<?php

declare(strict_types=1);

namespace App\Services\Domain;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use App\Models\Pedido;
use App\Models\Loja;
use App\Models\HistoricoPedido;
use App\Models\MovimentacaoFinanceira;
use App\Models\Cupom;
use App\Services\AuditLogService;
use App\Services\SaaS\PlanService;
use App\Services\WhatsApp\WhatsAppOrderAutomationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected AuditLogService $auditLog,
        protected FinanceService $financeService,
        protected ProductionService $productionService,
        protected \App\Services\System\NotificationService $notificationService,
        protected PlanService $planService,
        protected WhatsAppOrderAutomationService $whatsAppOrderAutomationService,
    ) {}

    /**
     * Número máximo de tentativas em caso de conflito de concorrência.
     */
    private const MAX_RETRIES = 3;

    /**
     * Cria um novo pedido consolidando lógica de cálculos e cupons.
     *
     * Usa transação com lock na loja para garantir unicidade do numero_sequencial.
     * Em caso de conflito de concorrência (Duplicate entry), faz retry automático.
     */
    public function create(array $data, int $userId): Pedido
    {
        $this->planService->ensureLimit('max_pedidos_mes', 1);

        $tentativa = 0;
        $lastException = null;

        while ($tentativa < self::MAX_RETRIES) {
            try {
                $order = DB::transaction(function () use ($data, $userId) {
                    $subtotal = 0;
                    foreach ($data['items'] as $item) {
                        $subtotal += ($item['unitary_value'] * $item['quantity']);
                    }

                    $shipping = (float) ($data['shipping_value'] ?? 0);
                    $fees = (float) ($data['additional_fees'] ?? 0);
                    $manualDiscount = (float) ($data['discount'] ?? 0);

                    // Regra de Cupom
                    $couponId = null;
                    $couponDiscount = 0;
                    if (!empty($data['coupon_code'])) {
                        $coupon = Cupom::where('codigo', $data['coupon_code'])->first();
                        if ($coupon && $coupon->isValid()) {
                            $couponId = $coupon->id;
                            $couponDiscount = $coupon->calcularDesconto($subtotal);
                            $coupon->increment('usos_atuais');
                        }
                    }

                    $total = max(0, ($subtotal - $couponDiscount + $shipping + $fees) - $manualDiscount);

                    // Geração dos identificadores do pedido
                    $number = 'PED-' . now()->format('ymd') . '-' . Str::upper(Str::random(4));

                    // Validação crítica: garantir contexto de loja
                    $lojaId = auth()->user()?->loja_id ?? tenant('id');
                    if (empty($lojaId)) {
                        throw new \RuntimeException('Contexto de loja não identificado. Impossível criar pedido.');
                    }

                    // Geração segura do sequencial com lock na loja
                    // O lock é mantido até o fim da transação, garantindo atomicidade
                    $sequencial = Pedido::gerarSequencialSeguro($lojaId);

                    // Busca loja (já foi travada pelo gerarSequencialSeguro)
                    $loja = Loja::find($lojaId);
                    $codigoPedido = Pedido::gerarCodigoPedido($loja->codigo_loja ?? 'L' . $lojaId, $sequencial);

                    $order = Pedido::create([
                'numero' => $number,
                'numero_sequencial' => $sequencial,
                'codigo_pedido' => $codigoPedido,
                'cliente_id' => $data['cliente_id'],
                'responsavel_id' => $userId,
                'status' => $data['status'] ?? Pedido::STATUS_RASCUNHO,
                'origem' => $data['origin'] ?? 'interno',
                'subtotal' => $subtotal,
                'valor_frete' => $shipping,
                'taxas_adicionais' => $fees,
                'desconto' => $manualDiscount,
                'cupom_id' => $couponId,
                'valor_desconto_cupom' => $couponDiscount,
                'total' => $total,
                'tipo_entrega' => $data['delivery_type'] ?? 'retirada',
                'prazo_entrega' => $data['delivery_deadline'] ?? null,
                'observacoes' => $data['observations'] ?? '',
            ]);

            foreach ($data['items'] as $item) {
                $order->itens()->create([
                    'produto_id' => $item['produto_id'],
                    'descricao_item' => $item['description'] ?? 'Item de Pedido',
                    'quantidade' => $item['quantity'],
                    'valor_unitario' => $item['unitary_value'],
                    'valor_total' => $item['unitary_value'] * $item['quantity'],
                    'caminho_arte' => $item['art_path'] ?? null,
                ]);
            }

            $this->logHistory($order->id, null, $order->status, 'Pedido inicializado no sistema.', $userId);
            $this->auditLog->log('pedidos', 'criacao', $order->id, null, $order->toArray());

            // Novo: Notificação Interna (Bell Icon)
            $this->notificationService->notifyNewOrderInternally($order);

            // Novo: Gerar Título Financeiro automático
            if ($order->status !== Pedido::STATUS_RASCUNHO) {
                $this->financeService->createReceivableFromOrder($order);
            }

            // Novo: Gerar Ordem de Produção se já for criado em produção/aprovado
                    if ($order->status === Pedido::STATUS_EM_PRODUCAO) {
                        $this->productionService->createFromOrder($order);
                    }

                    return $order;
                }); // Fim da transação

                if ($order->status !== Pedido::STATUS_RASCUNHO) {
                    if (in_array($order->status, [Pedido::STATUS_AGUARDANDO, Pedido::STATUS_AGUARDANDO_PAGAMENTO], true)) {
                        $this->whatsAppOrderAutomationService->onQuoteSent($order->fresh(['cliente']));
                    } else {
                        $this->whatsAppOrderAutomationService->onOrderCreated($order->fresh(['cliente']));
                    }
                }

                return $order;

            } catch (\Illuminate\Database\QueryException $e) {
                $tentativa++;
                $lastException = $e;

                // Verificar se é erro de duplicata (código MySQL 1062 / SQLSTATE 23000)
                $isDuplicate = str_contains($e->getMessage(), 'Duplicate entry')
                    || str_contains($e->getMessage(), '1062')
                    || $e->getCode() === '23000';

                if ($isDuplicate && $tentativa < self::MAX_RETRIES) {
                    // Delay exponencial antes de retry: 50ms, 100ms, 150ms
                    usleep(50000 * $tentativa);
                    continue;
                }

                // Se não for duplicata ou esgotou tentativas, propaga o erro
                throw $e;
            }
        }

        // Não deveria chegar aqui, mas por segurança:
        throw new \RuntimeException(
            'Falha ao criar pedido após ' . self::MAX_RETRIES . ' tentativas. ' .
            ($lastException ? $lastException->getMessage() : '')
        );
    }

    /* ... */

    public function updateStatus(Pedido $order, string $newStatus, ?string $description, int $userId): void
    {
        DB::transaction(function () use ($order, $newStatus, $description, $userId) {
            $oldStatus = $order->status;
            $oldData = $order->toArray();

            $order->update(['status' => $newStatus]);
            
            $this->logHistory($order->id, $oldStatus, $newStatus, $description ?? "Alteração para {$newStatus}", $userId);
            $this->auditLog->log('pedidos', 'atualizacao_status', $order->id, $oldData, $order->fresh()->toArray());

            // Novo: Notificar o cliente via NotificationService
            $this->notificationService->notifyStatusUpdate($order);

            // Garantir existência de título se saiu do rascunho
            if ($newStatus !== Pedido::STATUS_RASCUNHO) {
                $this->financeService->createReceivableFromOrder($order);
            }

            // Lógica automática de faturamento se marcado como pago
            if ($newStatus === Pedido::STATUS_EM_PRODUCAO && $oldStatus !== Pedido::STATUS_EM_PRODUCAO) {
                // Criar Ordem de Produção (Chão de Fábrica)
                $this->productionService->createFromOrder($order);

                // Faturamento
                $title = $this->financeService->createReceivableFromOrder($order);
                if ($title->status === 'aberto') {
                    $this->registerPayment($order, $order->forma_pagamento ?? 'Dinheiro', $userId);
                }
            }
        });

        $freshOrder = $order->fresh(['cliente']);

        match ($newStatus) {
            Pedido::STATUS_EM_PRODUCAO => $this->whatsAppOrderAutomationService->onOrderInProduction($freshOrder),
            Pedido::STATUS_PRONTO => $this->whatsAppOrderAutomationService->onOrderReady($freshOrder),
            Pedido::STATUS_ENTREGUE => $this->whatsAppOrderAutomationService->onOrderDelivered($freshOrder),
            default => null,
        };
    }

    /**
     * Registra o pagamento e gera movimentação financeira profissional.
     */
    public function registerPayment(Pedido $order, string $method, int $userId): void
    {
        $title = $this->financeService->createReceivableFromOrder($order);
        
        $this->financeService->addPayment($title, [
            'valor' => $title->saldo_restante,
            'forma_pagamento' => $method,
            'data_pagamento' => now(),
        ]);

        $this->whatsAppOrderAutomationService->onPaymentConfirmed($order->fresh(['cliente']));
    }

    private function logHistory(int $orderId, ?string $oldStatus, string $newStatus, string $description, int $userId): void
    {
        HistoricoPedido::create([
            'pedido_id' => $orderId,
            'status_anterior' => $oldStatus,
            'status_novo' => $newStatus,
            'descricao' => $description,
            'usuario_id' => $userId,
        ]);
    }
}
