<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:00
*/

use App\Http\Controllers\Controller;
use App\Models\ItemPedido;
use App\Models\MetricaSite;
use App\Models\MovimentacaoFinanceira;
use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $inicio = $request->input('data_inicio') ? Carbon::parse($request->input('data_inicio')) : now()->subDays(30);
        $fim    = $request->input('data_fim') ? Carbon::parse($request->input('data_fim')) : now();
        $fim->endOfDay();

        $user = auth()->user();
        $isGestor = in_array(strtolower($user->perfil), ['administrador', 'gerente']);
        $isGestorOuFinanceiro = in_array(strtolower($user->perfil), ['administrador', 'gerente', 'financeiro']);

        // 1. KPIs Comerciais
        $pedidosQuery = Pedido::whereBetween('created_at', [$inicio, $fim]);
        if (!$isGestor) {
            $pedidosQuery->where('responsavel_id', $user->id);
        }

        $totalPedidos = (clone $pedidosQuery)->count();
        $totalOrcamentos = (clone $pedidosQuery)->whereIn('status', ['rascunho', 'aguardando_aprovacao'])->count();
        
        // 2. Faturamento (Apenas Entradas Pagas)
        $faturamento = 0;
        if ($isGestorOuFinanceiro) {
            $faturamento = MovimentacaoFinanceira::entradas()
                ->pagos()
                ->whereBetween('data_movimentacao', [$inicio, $fim])
                ->sum('valor');
        }

        // 3. Produtos mais vendidos
        $topProdutos = ItemPedido::select('produto_id', DB::raw('SUM(quantidade) as total_qtd'), DB::raw('SUM(valor_total) as total_vendas'))
            ->whereHas('pedido', function($q) use ($inicio, $fim) {
                $q->whereNotIn('status', ['cancelado', 'rascunho'])
                  ->whereBetween('created_at', [$inicio, $fim]);
            })
            ->groupBy('produto_id')
            ->with('produto:id,nome')
            ->orderByDesc('total_qtd')
            ->take(5)
            ->get();

        // 4. Clientes Recorrentes
        $clientesQuery = Pedido::select('cliente_id', DB::raw('COUNT(*) as total_pedidos'))
            ->whereBetween('created_at', [$inicio, $fim]);
            
        if (!$isGestor) {
            $clientesQuery->where('responsavel_id', $user->id);
        }

        $clientesRecorrentes = $clientesQuery->groupBy('cliente_id')
            ->having('total_pedidos', '>', 1)
            ->with('cliente:id,nome')
            ->orderByDesc('total_pedidos')
            ->take(5)
            ->get();

        // 5. Métricas de Catálogo
        $acessosPorTipo = MetricaSite::select('entidade_tipo', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$inicio, $fim])
            ->groupBy('entidade_tipo')
            ->get();

        $origensTrafego = MetricaSite::select('origem', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$inicio, $fim])
            ->groupBy('origem')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $dispositivos = MetricaSite::select('dispositivo', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$inicio, $fim])
            ->groupBy('dispositivo')
            ->get();

        return view('painel.relatorios.index', compact(
            'inicio', 'fim', 'totalPedidos', 'totalOrcamentos', 'faturamento',
            'topProdutos', 'clientesRecorrentes', 'acessosPorTipo', 'origensTrafego', 'dispositivos'
        ));
    }

    /**
     * Exporta os pedidos do período para CSV.
     */
    public function exportarPedidos(Request $request): StreamedResponse
    {
        $inicio = $request->input('data_inicio') ? Carbon::parse($request->input('data_inicio')) : now()->subDays(30);
        $fim    = $request->input('data_fim') ? Carbon::parse($request->input('data_fim')) : now();
        $fim->endOfDay();

        $query = Pedido::with('cliente')
            ->whereBetween('created_at', [$inicio, $fim])
            ->orderBy('created_at', 'desc');

        if (!in_array(strtolower(auth()->user()->perfil), ['administrador', 'gerente'])) {
            $query->where('responsavel_id', auth()->id());
        }

        $pedidos = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="relatorio_pedidos_'.now()->format('Ymd').'.csv"',
        ];

        return new StreamedResponse(function() use ($pedidos) {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM para Excel
            
            fputcsv($handle, ['Número', 'Cliente', 'Data', 'Total', 'Status', 'Forma Pagamento']);

            foreach ($pedidos as $pedido) {
                fputcsv($handle, [
                    $pedido->numero,
                    $pedido->cliente?->nome,
                    $pedido->created_at->format('d/m/Y H:i'),
                    number_format((float)$pedido->total, 2, ',', '.'),
                    ucfirst(str_replace('_', ' ', $pedido->status)),
                    $pedido->forma_pagamento
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
