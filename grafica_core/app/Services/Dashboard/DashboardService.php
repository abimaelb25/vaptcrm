<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data de Criação: 14/04/2026
*/

use App\Models\Usuario;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\MovimentacaoFinanceira;
use App\Models\Tarefa;
use App\Services\SaaS\SaaSService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    protected SaaSService $saas;

    public function __construct(SaaSService $saas)
    {
        $this->saas = $saas;
    }

    /**
     * Resolve o slug interno do perfil baseado no texto original.
     */
    public function getProfileSlug(Usuario $user): string
    {
        $perfil = strtolower($user->perfil ?? '');

        if ($user->isSuperAdmin()) {
            return 'admin';
        }

        return match (true) {
            str_contains($perfil, 'administrador') => 'admin',
            str_contains($perfil, 'gerente')      => 'gerente',
            str_contains($perfil, 'atendente') || str_contains($perfil, 'comercial') => 'atendente',
            str_contains($perfil, 'produção') || str_contains($perfil, 'operação')  => 'producao',
            str_contains($perfil, 'financeiro') || str_contains($perfil, 'administrativo') => 'financeiro',
            default => 'fallback',
        };
    }

    /**
     * Coleta os dados necessários para o dashboard baseado no perfil e permissões.
     */
    public function getDataForProfile(Usuario $user): array
    {
        $slug = $this->getProfileSlug($user);
        $lojaId = $user->loja_id;

        $baseData = [
            'perfilSlug' => $slug,
            'assinatura' => $this->saas->getAssinatura(),
            'hoje'       => Carbon::today(),
        ];

        $profileData = match ($slug) {
            'admin'      => $this->getAdminData($lojaId),
            'gerente'    => $this->getGerenteData($lojaId),
            'atendente'  => $this->getAtendenteData($lojaId, $user->id),
            'producao'   => $this->getProducaoData($lojaId, $user->id),
            'financeiro' => $this->getFinanceiroData($lojaId),
            default      => [],
        };

        // Filtra widgets adicionais por permissão
        $profileData['widgets_adicionais'] = $this->getExtraWidgets($user);

        return array_merge($baseData, $profileData);
    }

    /**
     * Métricas para o Administrador (Decisão/Estratégia)
     */
    protected function getAdminData(?int $lojaId): array
    {
        if (!$lojaId) return [];

        $hoje = Carbon::today();
        $inicioMes = $hoje->copy()->startOfMonth();

        return [
            'faturamento_mes' => Pedido::where('loja_id', $lojaId)
                ->where('status', '!=', Pedido::STATUS_CANCELADO)
                ->whereDate('created_at', '>=', $inicioMes)
                ->sum('total'),
            'novos_clientes_mes' => Cliente::where('loja_id', $lojaId)
                ->whereDate('created_at', '>=', $inicioMes)
                ->count(),
            'ticket_medio' => Pedido::where('loja_id', $lojaId)
                ->where('status', '!=', Pedido::STATUS_CANCELADO)
                ->whereDate('created_at', '>=', $inicioMes)
                ->avg('total') ?? 0,
            'alertas_risco' => [
                'inadimplencia' => MovimentacaoFinanceira::where('loja_id', $lojaId)
                    ->where('status', MovimentacaoFinanceira::STATUS_PENDENTE)
                    ->whereDate('data_movimentacao', '<', $hoje)
                    ->count(),
            ],
            'pedidos_hoje' => Pedido::where('loja_id', $lojaId)->whereDate('created_at', $hoje)->count(),
        ];
    }

    /**
     * Métricas para o Gerente (Controle/Equipe)
     */
    protected function getGerenteData(?int $lojaId): array
    {
        if (!$lojaId) return [];

        $hoje = Carbon::today();

        return [
            'pedidos_dia' => Pedido::where('loja_id', $lojaId)->whereDate('created_at', $hoje)->count(),
            'faturamento_dia' => Pedido::where('loja_id', $lojaId)
                ->where('status', '!=', Pedido::STATUS_CANCELADO)
                ->whereDate('created_at', $hoje)
                ->sum('total'),
            'atrasos_criticos' => Pedido::where('loja_id', $lojaId)
                ->whereNotIn('status', [Pedido::STATUS_ENTREGUE, Pedido::STATUS_CANCELADO])
                ->whereDate('prazo_entrega', '<', $hoje)
                ->count(),
            'ranking_vendas' => Pedido::where('loja_id', $lojaId)
                ->where('status', '!=', Pedido::STATUS_CANCELADO)
                ->whereDate('created_at', '>=', $hoje->copy()->startOfMonth())
                ->select('atendente_id', DB::raw('SUM(total) as total_vendas'), DB::raw('COUNT(*) as qtd_pedidos'))
                ->groupBy('atendente_id')
                ->with('atendente')
                ->orderByDesc('total_vendas')
                ->take(5)
                ->get(),
            'pedidos_por_status' => Pedido::where('loja_id', $lojaId)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get()
                ->pluck('total', 'status'),
        ];
    }

    /**
     * Métricas para o Atendente (Execução/Venda)
     */
    protected function getAtendenteData(?int $lojaId, int $userId): array
    {
        if (!$lojaId) return [];

        $hoje = Carbon::today();

        return [
            'meus_pedidos_hoje' => Pedido::where('loja_id', $lojaId)
                ->where('atendente_id', $userId)
                ->whereDate('created_at', $hoje)
                ->count(),
            'minha_venda_dia' => Pedido::where('loja_id', $lojaId)
                ->where('atendente_id', $userId)
                ->where('status', '!=', Pedido::STATUS_CANCELADO)
                ->whereDate('created_at', $hoje)
                ->sum('total'),
            'fila_acao' => Pedido::with('cliente')
                ->where('loja_id', $lojaId)
                ->where('atendente_id', $userId)
                ->whereIn('status', [Pedido::STATUS_RASCUNHO, Pedido::STATUS_AGUARDANDO])
                ->latest()
                ->take(5)
                ->get(),
        ];
    }

    /**
     * Métricas para a Produção (Tarefas/Prazos)
     */
    protected function getProducaoData(?int $lojaId, int $userId): array
    {
        if (!$lojaId) return [];

        $hoje = Carbon::today();

        return [
            'fila_prioritaria' => Pedido::with(['cliente', 'itens'])
                ->where('loja_id', $lojaId)
                ->whereIn('status', [Pedido::STATUS_EM_PRODUCAO, Pedido::STATUS_APROVADO])
                ->orderByRaw('prazo_entrega IS NULL')
                ->orderBy('prazo_entrega', 'ASC')
                ->take(10)
                ->get(),
            'entregas_hoje' => Pedido::where('loja_id', $lojaId)
                ->whereIn('status', [Pedido::STATUS_PRONTO, Pedido::STATUS_EM_PRODUCAO])
                ->whereDate('prazo_entrega', $hoje)
                ->count(),
            'minhas_tarefas' => Tarefa::where('responsavel_id', $userId)
                ->where('status', '!=', 'concluida')
                ->get(),
        ];
    }

    /**
     * Métricas para o Financeiro (Fluxo/Dinheiro)
     */
    protected function getFinanceiroData(?int $lojaId): array
    {
        if (!$lojaId) return [];

        $hoje = Carbon::today();

        return [
            'receber_hoje' => MovimentacaoFinanceira::where('loja_id', $lojaId)
                ->where('tipo', MovimentacaoFinanceira::TIPO_ENTRADA)
                ->where('status', MovimentacaoFinanceira::STATUS_PENDENTE)
                ->whereDate('data_movimentacao', $hoje)
                ->sum('valor'),
            'atrasados_total' => MovimentacaoFinanceira::where('loja_id', $lojaId)
                ->where('status', MovimentacaoFinanceira::STATUS_PENDENTE)
                ->whereDate('data_movimentacao', '<', $hoje)
                ->sum('valor'),
            'ultimos_pagamentos' => MovimentacaoFinanceira::where('loja_id', $lojaId)
                ->where('status', MovimentacaoFinanceira::STATUS_PAGO)
                ->latest()
                ->take(5)
                ->get(),
        ];
    }

    /**
     * Verifica widgets extras baseados em permissões individuais.
     */
    protected function getExtraWidgets(Usuario $user): array
    {
        $widgets = [];
        
        // Exemplo: se o atendente tem permissão financeira, mostra resumo de caixa
        if ($user->temPermissao('ver_financeiro_resumo')) {
            $widgets['financeiro_mini'] = true;
        }

        return $widgets;
    }
}
