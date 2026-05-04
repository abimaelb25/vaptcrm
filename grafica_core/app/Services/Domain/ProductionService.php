<?php

declare(strict_types=1);

namespace App\Services\Domain;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-20 03:30 (Modificado)
| Descrição: Motor de produção com integração ERP, snapshots por produto e gestão de insumos.
*/

use App\Models\ProductionOrder;
use App\Models\ProductionOrderHistory;
use App\Models\ProductionStep;
use App\Models\ProductionPhase;
use App\Models\ProductionOrderStep;
use App\Models\ProductionOrderStepInsumo;
use App\Models\Pedido;
use App\Services\SaaS\LimitEnforcementService;
use App\Services\SaaS\UsageTrackerService;
use App\Events\OrderCreatedEvent;
use App\Events\OrderMovedEvent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class ProductionService
{
    private const CACHE_TTL_SECONDS = 10;

    public function __construct(
        private readonly LimitEnforcementService $limitEnforcementService,
        private readonly UsageTrackerService $usageTrackerService,
    ) {}

    /**
     * Cria OP e já posiciona na primeira etapa válida da loja.
     */
    public function criarOrdem(array $data): ProductionOrder
    {
        $lojaId = (int) ($data['loja_id'] ?? 0);

        if ($lojaId <= 0) {
            throw new InvalidArgumentException('Loja inválida para criação da OP.');
        }

        $this->limitEnforcementService->checkLimit($lojaId, 'op');

        $produtoId = (int) ($data['produto_id'] ?? 0);
        $firstStep = $this->getFirstStepForProductOrStore($lojaId, $produtoId);

        if (!$firstStep) {
            throw new InvalidArgumentException('Não há etapas ativas configuradas para esta loja.');
        }

        return DB::transaction(function () use ($data, $lojaId, $firstStep): ProductionOrder {
            $prioridade = (string) ($data['prioridade'] ?? 'normal');

            if (!in_array($prioridade, ['baixa', 'normal', 'alta', 'urgente'], true)) {
                $prioridade = 'normal';
            }

            $order = ProductionOrder::create([
                'loja_id' => $lojaId,
                'pedido_id' => (int) $data['pedido_id'],
                'item_pedido_id' => (int) ($data['item_pedido_id'] ?? 0) ?: null,
                'produto_id' => $produtoId ?: null,
                'cliente_nome' => (string) ($data['cliente_nome'] ?? 'Cliente não informado'),
                'produto_nome' => (string) ($data['produto_nome'] ?? 'Produto não informado'),
                'quantidade' => max((int) ($data['quantidade'] ?? 1), 1),
                'valor_total' => (float) ($data['valor_total'] ?? 0),
                'status' => 'em_producao',
                'status_atual' => $firstStep->nome,
                'production_step_id' => $firstStep->id,
                'data_inicio' => $data['data_inicio'] ?? now(),
                'data_previsao' => $data['data_previsao'] ?? null,
                'prioridade' => $prioridade,
                'observacoes' => $data['observacoes'] ?? null,
                'observacao' => $data['observacoes'] ?? null,
            ]);

            ProductionOrderHistory::create([
                'production_order_id' => $order->id,
                'etapa_origem_id' => null,
                'etapa_destino_id' => $firstStep->id,
                'usuario_id' => $data['usuario_id'] ?? null,
                'data_movimentacao' => now(),
                'observacao' => $data['observacoes'] ?? 'OP criada na primeira etapa.',
            ]);

            Log::info('OP criada no chão de fábrica', [
                'loja_id' => $lojaId,
                'production_order_id' => $order->id,
                'pedido_id' => $order->pedido_id,
                'production_step_id' => $firstStep->id,
            ]);

            $this->snapshotStepsForOrder($order, $lojaId);

            $this->invalidateProductionCaches($lojaId);
            event(new OrderCreatedEvent($order, $lojaId, $data['usuario_id'] ?? null));

            $this->usageTrackerService->trackProductionOrderCreated($lojaId, [
                'production_order_id' => $order->id,
                'pedido_id' => $order->pedido_id,
            ]);

            return $order->load('currentStep');
        });
    }

    /**
     * Move OP para a próxima etapa válida, com rastreabilidade completa.
     */
    public function moverOrdem(int $lojaId, int $orderId, int $nextStepId, ?int $usuarioId = null, ?string $observacao = null): ProductionOrder
    {
        return DB::transaction(function () use ($lojaId, $orderId, $nextStepId, $usuarioId, $observacao): ProductionOrder {
            $order = ProductionOrder::where('loja_id', $lojaId)
                ->lockForUpdate()
                ->find($orderId);

            if (!$order) {
                throw new ModelNotFoundException('OP não encontrada para esta loja.');
            }

            if ($order->status === 'finalizado' || $order->data_finalizacao !== null) {
                Log::warning('Tentativa de mover OP finalizada', [
                    'loja_id' => $lojaId,
                    'production_order_id' => $orderId,
                    'usuario_id' => $usuarioId,
                    'next_step_id' => $nextStepId,
                ]);

                throw new InvalidArgumentException('Não é permitido mover uma OP já finalizada.');
            }

            $nextStep = ProductionStep::where('loja_id', $lojaId)
                ->where('ativo', true)
                ->find($nextStepId);

            if (!$nextStep) {
                Log::warning('Tentativa de mover OP para etapa inválida', [
                    'loja_id' => $lojaId,
                    'production_order_id' => $orderId,
                    'usuario_id' => $usuarioId,
                    'next_step_id' => $nextStepId,
                ]);

                throw new ModelNotFoundException('Etapa de destino inválida para esta loja.');
            }

            $sequence = $this->getOrderedSteps($lojaId);

            if ($sequence->isEmpty()) {
                throw new InvalidArgumentException('Fluxo de etapas inexistente para esta loja.');
            }

            $currentStepId = $order->production_step_id;
            $toIndex = $sequence->search(fn (ProductionStep $step) => $step->id === $nextStep->id);

            if ($toIndex === false) {
                throw new InvalidArgumentException('A etapa de destino não pertence ao fluxo configurado.');
            }

            if ($currentStepId === $nextStep->id) {
                throw new InvalidArgumentException('A OP já está nesta etapa.');
            }

            if ($currentStepId === null && $toIndex !== 0) {
                $this->logInvalidMoveAttempt($lojaId, $orderId, $usuarioId, $currentStepId, $nextStepId, 'iniciar-fora-da-primeira-etapa');
                throw new InvalidArgumentException('A OP sem etapa só pode iniciar na primeira etapa do fluxo.');
            }

            if ($currentStepId !== null) {
                $fromIndex = $sequence->search(fn (ProductionStep $step) => $step->id === (int) $currentStepId);

                if ($fromIndex === false) {
                    throw new InvalidArgumentException('Etapa atual da OP não existe no fluxo configurado.');
                }

                if ($toIndex !== ($fromIndex + 1)) {
                    $this->logInvalidMoveAttempt($lojaId, $orderId, $usuarioId, $currentStepId, $nextStepId, 'salto-de-etapa');
                    throw new InvalidArgumentException('Movimentação inválida. A OP só pode avançar para a próxima etapa.');
                }
            }

            $lastIndex = $sequence->count() - 1;
            $isLastStep = $toIndex === $lastIndex;
            $isFirstStep = $toIndex === 0;
            $now = now();
            $originStepId = $order->production_step_id;

            $payload = [
                'production_step_id' => $nextStep->id,
                'status_atual' => $nextStep->nome,
                'status' => $isLastStep ? 'finalizado' : 'em_producao',
            ];

            if ($order->data_inicio === null && $isFirstStep) {
                $payload['data_inicio'] = $now;
            }

            if ($isLastStep) {
                $payload['data_finalizacao'] = $now;
                $payload['data_conclusao'] = $now;
            }

            $order->update($payload);

            ProductionOrderHistory::create([
                'production_order_id' => $order->id,
                'etapa_origem_id' => $originStepId,
                'etapa_destino_id' => $nextStep->id,
                'usuario_id' => $usuarioId,
                'data_movimentacao' => $now,
                'observacao' => $observacao,
            ]);

            Log::info('OP movida no chão de fábrica', [
                'loja_id' => $order->loja_id,
                'production_order_id' => $order->id,
                'etapa_origem_id' => $originStepId,
                'etapa_destino_id' => $nextStep->id,
                'usuario_id' => $usuarioId,
            ]);

            $freshOrder = $order->fresh(['currentStep']);
            $history = ProductionOrderHistory::where('production_order_id', $order->id)
                ->latest('id')
                ->first();

            $this->invalidateProductionCaches($lojaId);

            if ($history) {
                event(new OrderMovedEvent($freshOrder, $history, $lojaId, $usuarioId));
            }

            return $freshOrder;
        });
    }

    /**
     * Retorna estrutura Kanban com fases -> etapas -> ordens.
     */
    public function getKanban(int $lojaId): Collection
    {
        return Cache::remember(
            $this->kanbanCacheKey($lojaId),
            now()->addSeconds(self::CACHE_TTL_SECONDS),
            function () use ($lojaId): Collection {
                $phases = ProductionPhase::where('loja_id', $lojaId)
                    ->where('ativo', true)
                    ->with(['steps' => function ($query) use ($lojaId) {
                        $query->where('ativo', true)
                            ->orderBy('ordem')
                            ->with(['productionOrders' => function ($ordersQuery) use ($lojaId) {
                                $ordersQuery->where('loja_id', $lojaId)
                                    ->whereNull('data_finalizacao')
                                    ->orderByRaw("FIELD(prioridade, 'urgente', 'alta', 'normal', 'baixa')")
                                    ->orderBy('created_at');
                            }]);
                    }])
                    ->orderBy('ordem')
                    ->get();

                $kanban = $phases->map(function (ProductionPhase $phase): array {
                    return [
                        'fase' => $phase->nome,
                        'fase_id' => $phase->id,
                        'ordem' => $phase->ordem,
                        'etapas' => $phase->steps->map(fn (ProductionStep $step): array => [
                            'etapa_id' => $step->id,
                            'etapa_nome' => $step->nome,
                            'ordem' => $step->ordem,
                            'ordens' => $step->productionOrders->map(fn (ProductionOrder $order): array => $this->serializeOrderForKanban($order))->values()->toArray(),
                            'total_ops' => $step->productionOrders->count(),
                        ])->values()->toArray(),
                    ];
                })->values();

                return $kanban->sortBy('ordem')->values();
            }
        );
    }

    /**
     * Métricas principais do chão de fábrica.
     */
    public function getProductionMetrics(int $lojaId): array
    {
        return Cache::remember(
            $this->metricsCacheKey($lojaId),
            now()->addSeconds(self::CACHE_TTL_SECONDS),
            function () use ($lojaId): array {
                $baseQuery = ProductionOrder::where('loja_id', $lojaId);

                $totalEmProducao = (clone $baseQuery)
                    ->whereNull('data_finalizacao')
                    ->count();

                $totalConcluidasHoje = (clone $baseQuery)
                    ->whereDate('data_finalizacao', now()->toDateString())
                    ->count();

                if (DB::getDriverName() === 'mysql') {
                    $tempoMedio = (int) round((float) ((clone $baseQuery)
                        ->whereNotNull('data_inicio')
                        ->whereNotNull('data_finalizacao')
                        ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, data_inicio, data_finalizacao)) as media')
                        ->value('media') ?? 0));
                } else {
                    $duracoes = (clone $baseQuery)
                        ->whereNotNull('data_inicio')
                        ->whereNotNull('data_finalizacao')
                        ->get(['data_inicio', 'data_finalizacao'])
                        ->map(fn (ProductionOrder $order): int => $order->data_inicio->diffInMinutes($order->data_finalizacao));

                    $tempoMedio = $duracoes->isNotEmpty() ? (int) round($duracoes->avg()) : 0;
                }

                $gargalo = (clone $baseQuery)
                    ->whereNull('data_finalizacao')
                    ->whereNotNull('production_step_id')
                    ->select('production_step_id', DB::raw('COUNT(*) as total'))
                    ->groupBy('production_step_id')
                    ->orderByDesc('total')
                    ->with('currentStep:id,nome')
                    ->first();

                $throughput = (clone $baseQuery)
                    ->whereNotNull('data_finalizacao')
                    ->whereDate('data_finalizacao', '>=', now()->subDays(6)->toDateString())
                    ->selectRaw('DATE(data_finalizacao) as dia, COUNT(*) as total')
                    ->groupBy('dia')
                    ->orderBy('dia')
                    ->get()
                    ->map(fn ($row): array => [
                        'dia' => (string) $row->dia,
                        'total' => (int) $row->total,
                    ])
                    ->values()
                    ->toArray();

                $opsPorPrioridade = (clone $baseQuery)
                    ->whereNull('data_finalizacao')
                    ->selectRaw('prioridade, COUNT(*) as total')
                    ->groupBy('prioridade')
                    ->pluck('total', 'prioridade')
                    ->map(fn ($total): int => (int) $total)
                    ->toArray();

                $totalFinalizadasComPrevisao = (clone $baseQuery)
                    ->whereNotNull('data_finalizacao')
                    ->whereNotNull('data_previsao')
                    ->count();

                $totalAtrasadas = (clone $baseQuery)
                    ->whereNotNull('data_finalizacao')
                    ->whereNotNull('data_previsao')
                    ->whereColumn('data_finalizacao', '>', 'data_previsao')
                    ->count();

                $taxaAtraso = $totalFinalizadasComPrevisao > 0
                    ? round(($totalAtrasadas / $totalFinalizadasComPrevisao) * 100, 2)
                    : 0.0;

                $tempoMedioPorEtapa = $this->getAverageTimeByStep($lojaId);
                $etapaMaisLenta = collect($tempoMedioPorEtapa)
                    ->sortByDesc('tempo_medio_minutos')
                    ->first();

                return [
                    'total_em_producao' => $totalEmProducao,
                    'total_concluidas_hoje' => $totalConcluidasHoje,
                    'tempo_medio_producao' => $tempoMedio,
                    'gargalo_atual' => $gargalo ? [
                        'etapa_id' => $gargalo->production_step_id,
                        'etapa_nome' => $gargalo->currentStep?->nome ?? 'Sem etapa',
                        'total_ops' => (int) $gargalo->total,
                    ] : null,
                    'throughput_por_dia' => $throughput,
                    'tempo_medio_por_etapa' => $tempoMedioPorEtapa,
                    'etapa_mais_lenta' => $etapaMaisLenta,
                    'taxa_atraso' => $taxaAtraso,
                    'ops_por_prioridade' => $opsPorPrioridade,
                ];
            }
        );
    }

    /**
     * Histórico completo de movimentação da OP.
     */
    public function getOrderHistory(int $lojaId, int $orderId): Collection
    {
        $order = ProductionOrder::where('loja_id', $lojaId)->find($orderId);

        if (!$order) {
            throw new ModelNotFoundException('OP não encontrada para esta loja.');
        }

        return $order->histories()
            ->with(['etapaOrigem:id,nome', 'etapaDestino:id,nome', 'usuario:id,nome'])
            ->orderByDesc('data_movimentacao')
            ->get();
    }

    /**
     * Cria Ordens de Produção para cada item individual de um Pedido.
     * Desta forma, diferentes produtos do mesmo pedido seguem seus próprios fluxos.
     */
    public function createFromOrder(Pedido $order): Collection
    {
        return DB::transaction(function () use ($order) {
            $ops = collect();

            foreach ($order->itens as $item) {
                // Evita duplicidade para o mesmo item de pedido
                $exists = ProductionOrder::where('item_pedido_id', $item->id)->exists();
                
                if (!$exists) {
                    $op = $this->criarOrdem([
                        'loja_id' => $order->loja_id,
                        'pedido_id' => $order->id,
                        'item_pedido_id' => $item->id,
                        'produto_id' => $item->produto_id,
                        'cliente_nome' => (string) ($order->cliente?->nome ?? 'Cliente não informado'),
                        'produto_nome' => (string) ($item->descricao_item ?? 'Produto não informado'),
                        'quantidade' => (int) ($item->quantidade ?: 1),
                        'valor_total' => (float) $item->valor_total,
                        'prioridade' => 'normal',
                        'data_previsao' => $order->prazo_entrega,
                        'usuario_id' => auth()->id(),
                    ]);
                    $ops->push($op);
                }
            }

            return $ops;
        });
    }

    /**
     * Atualiza o status de uma etapa de produção.
     */
    public function updateStepStatus(ProductionOrderStep $orderStep, string $status, ?int $userId): void
    {
        DB::transaction(function () use ($orderStep, $status, $userId) {
            $data = ['status' => $status];
            
            if ($status === 'em_andamento') {
                $data['data_inicio'] = now();
                $data['responsavel_id'] = $userId ?? $orderStep->responsavel_id;
                
                // Atualiza a OP pai para 'em_producao' se ainda não estiver
                if ($orderStep->order->status === 'aguardando') {
                    $orderStep->order->update(['status' => 'em_producao', 'data_inicio' => now()]);
                }
            }

            if ($status === 'concluido') {
                $data['data_fim'] = now();
                
                // Calcula tempo real se houver data_inicio
                if ($orderStep->data_inicio) {
                    $data['tempo_real'] = (int) $orderStep->data_inicio->diffInMinutes(now());
                }
            }

            $orderStep->update($data);

            // Verifica se todas as etapas da OP foram concluídas
            $this->checkProductionCompletion($orderStep->order);
        });
    }

    /**
     * Verifica conclusão total da OP e atualiza o pedido.
     */
    protected function checkProductionCompletion(ProductionOrder $po): void
    {
        $total = $po->stages()->count();
        $concluidas = $po->stages()->where('status', 'concluido')->count();

        if ($total > 0 && $total === $concluidas) {
            $po->update([
                'status' => 'finalizado',
                'data_conclusao' => now()
            ]);

            // Opcional: Atualizar status do pedido para "Pronto para Retirada" ou similar
            $po->pedido->update(['status' => 'finalizado']);
        }
    }

    /**
     * Retorna o fluxo de produção organizado por fases.
     * 
     * Estrutura de retorno:
     * - Fases com suas etapas ordenadas
     * 
     * Usa eager loading para evitar N+1.
     *
     * @param int $lojaId
     * @return Collection
     */
    public function getProductionFlow(int $lojaId): Collection
    {
        // Carrega fases com suas etapas (eager loading)
        $phases = ProductionPhase::where('loja_id', $lojaId)
            ->where('ativo', true)
            ->with(['steps' => function ($query) {
                $query->where('ativo', true)->orderBy('ordem');
            }])
            ->orderBy('ordem')
            ->get();

        // Monta coleção de resultado
        $result = collect();

        // Adiciona fases com etapas
        foreach ($phases as $phase) {
            $result->push([
                'fase' => $phase->nome,
                'fase_id' => $phase->id,
                'ordem' => $phase->ordem,
                'etapas' => $phase->steps->map(fn ($step) => [
                    'id' => $step->id,
                    'nome' => $step->nome,
                    'ordem' => $step->ordem,
                ])->values()->toArray(),
            ]);
        }

        return $result->sortBy('ordem')->values();
    }

    /**
     * Sequência global de etapas para validação de avanço.
     */
    protected function getOrderedSteps(int $lojaId): Collection
    {
        return ProductionStep::query()
            ->join('production_phases as phase', 'phase.id', '=', 'production_steps.production_phase_id')
            ->where('production_steps.loja_id', $lojaId)
            ->where('production_steps.ativo', true)
            ->select('production_steps.*')
            ->orderBy('phase.ordem')
            ->orderBy('production_steps.ordem')
            ->get();
    }

    protected function getFirstStepForProductOrStore(int $lojaId, int $produtoId = 0): ProductionStep
    {
        if ($produtoId > 0) {
            $firstProductStep = \App\Models\ProdutoEtapaProducao::where('produto_id', $produtoId)
                ->orderBy('ordem')
                ->first();
            
            if ($firstProductStep && $firstProductStep->etapa) {
                return $firstProductStep->etapa;
            }
        }

        $step = $this->getOrderedSteps($lojaId)->first();
        
        if (!$step) {
            throw new InvalidArgumentException('Configuração de fluxo de produção ausente para a loja.');
        }

        return $step;
    }

    protected function invalidateProductionCaches(int $lojaId): void
    {
        Cache::forget($this->kanbanCacheKey($lojaId));
        Cache::forget($this->metricsCacheKey($lojaId));
    }

    protected function kanbanCacheKey(int $lojaId): string
    {
        return "production_kanban_loja_{$lojaId}";
    }

    protected function metricsCacheKey(int $lojaId): string
    {
        return "production_metrics_loja_{$lojaId}";
    }

    protected function serializeOrderForKanban(ProductionOrder $order): array
    {
        $isAtrasada = $order->data_previsao !== null
            && $order->data_finalizacao === null
            && now()->greaterThan($order->data_previsao);

        $minutosAtraso = $isAtrasada
            ? $order->data_previsao?->diffInMinutes(now())
            : 0;

        return [
            'id' => $order->id,
            'pedido_id' => $order->pedido_id,
            'cliente_nome' => $order->cliente_nome,
            'produto_nome' => $order->produto_nome,
            'quantidade' => $order->quantidade,
            'prioridade' => $order->prioridade,
            'status_atual' => $order->status_atual,
            'data_inicio' => optional($order->data_inicio)?->toDateTimeString(),
            'data_previsao' => optional($order->data_previsao)?->toDateTimeString(),
            'tempo_em_producao_minutos' => $order->data_inicio ? $order->data_inicio->diffInMinutes(now()) : 0,
            'tempo_em_producao_humano' => $order->data_inicio ? 'há ' . $order->data_inicio->diffForHumans(null, true) : 'não iniciado',
            'atrasada' => $isAtrasada,
            'minutos_atraso' => $minutosAtraso,
        ];
    }

    protected function getAverageTimeByStep(int $lojaId): array
    {
        $histories = ProductionOrderHistory::query()
            ->whereHas('order', fn ($query) => $query->where('loja_id', $lojaId))
            ->with(['order:id,loja_id,data_finalizacao'])
            ->orderBy('production_order_id')
            ->orderBy('data_movimentacao')
            ->get();

        if ($histories->isEmpty()) {
            return [];
        }

        $durations = [];

        foreach ($histories->groupBy('production_order_id') as $orderHistories) {
            $ordered = $orderHistories->values();
            $order = $ordered->first()?->order;

            for ($index = 0; $index < $ordered->count(); $index++) {
                $current = $ordered[$index];
                $next = $ordered[$index + 1] ?? null;
                $startAt = $this->asCarbon($current->data_movimentacao);

                if (!$startAt || !$current->etapa_destino_id) {
                    continue;
                }

                $endAt = $next
                    ? $this->asCarbon($next->data_movimentacao)
                    : $this->asCarbon($order?->data_finalizacao);

                if (!$endAt || $endAt->lessThanOrEqualTo($startAt)) {
                    continue;
                }

                $minutes = $startAt->diffInMinutes($endAt);
                $stepId = (int) $current->etapa_destino_id;

                if (!isset($durations[$stepId])) {
                    $durations[$stepId] = ['sum' => 0, 'count' => 0];
                }

                $durations[$stepId]['sum'] += $minutes;
                $durations[$stepId]['count']++;
            }
        }

        if (empty($durations)) {
            return [];
        }

        $stepNames = ProductionStep::whereIn('id', array_keys($durations))
            ->pluck('nome', 'id');

        return collect($durations)
            ->map(function (array $item, int $stepId) use ($stepNames): array {
                $average = $item['count'] > 0 ? (int) round($item['sum'] / $item['count']) : 0;

                return [
                    'etapa_id' => (int) $stepId,
                    'etapa_nome' => $stepNames->get($stepId, 'Etapa removida'),
                    'tempo_medio_minutos' => $average,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function asCarbon(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if (!$value) {
            return null;
        }

        return Carbon::parse((string) $value);
    }

    protected function logInvalidMoveAttempt(
        int $lojaId,
        int $orderId,
        ?int $usuarioId,
        ?int $currentStepId,
        int $nextStepId,
        string $reason
    ): void {
        Log::warning('Tentativa inválida de movimentação de OP', [
            'loja_id' => $lojaId,
            'production_order_id' => $orderId,
            'usuario_id' => $usuarioId,
            'etapa_atual_id' => $currentStepId,
            'etapa_destino_id' => $nextStepId,
            'motivo' => $reason,
        ]);
    }

    /**
     * Gera snapshot das etapas ativas no momento da criação da OP.
     * Lógica inteligente: tenta carregar etapas específicas do produto, 
     * caso contrário usa o fluxo padrão da loja.
     */
    protected function snapshotStepsForOrder(ProductionOrder $order, int $lojaId): void
    {
        $productSteps = collect();
        
        if ($order->produto_id) {
            $productSteps = \App\Models\ProdutoEtapaProducao::where('produto_id', $order->produto_id)
                ->orderBy('ordem')
                ->with(['etapa.insumos', 'etapa.phase'])
                ->get();
        }

        if ($productSteps->isNotEmpty()) {
            foreach ($productSteps as $ps) {
                $step = $ps->etapa;
                $this->createStepInstance($order, $step, $lojaId, $ps->ordem, $ps->tempo_estimado_minutos);
            }
        } else {
            // Fallback para fluxo padrão da loja
            $steps = ProductionStep::where('production_steps.loja_id', $lojaId)
                ->where('production_steps.ativo', true)
                ->with(['phase', 'insumos'])
                ->join('production_phases as phase', 'phase.id', '=', 'production_steps.production_phase_id')
                ->select('production_steps.*')
                ->orderBy('phase.ordem')
                ->orderBy('production_steps.ordem')
                ->get();

            foreach ($steps as $index => $step) {
                $this->createStepInstance($order, $step, $lojaId, $step->ordem ?: ($index + 1));
            }
        }

        Log::info('Sistema de snapshot concluído para OP', [
            'production_order_id' => $order->id,
            'origem' => $productSteps->isNotEmpty() ? 'produto' : 'loja',
        ]);
    }

    protected function createStepInstance(ProductionOrder $order, ProductionStep $step, int $lojaId, int $ordem, ?int $tempoEstimado = null): ProductionOrderStep
    {
        $orderStep = ProductionOrderStep::create([
            'loja_id' => $lojaId,
            'production_order_id' => $order->id,
            'production_step_id' => $step->id,
            'nome_snapshot' => $step->nome,
            'ordem_snapshot' => $ordem,
            'fase_snapshot' => $step->phase?->nome,
            'status' => 'pendente',
            'tempo_estimado' => $tempoEstimado ?? $step->tempo_estimado_minutos,
            'asset_id' => $step->asset_id,
        ]);

        // Calcula consumo previsto de insumos (BOM)
        foreach ($step->insumos as $stepInsumo) {
            ProductionOrderStepInsumo::create([
                'loja_id' => $lojaId,
                'production_order_step_id' => $orderStep->id,
                'insumo_id' => $stepInsumo->insumo_id,
                'quantidade_prevista' => round($stepInsumo->quantidade_por_unidade * $order->quantidade, 4),
            ]);
        }

        return $orderStep;
    }
}
