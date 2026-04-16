<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Relatorios;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-13 13:59 -03:00
*/

use App\Http\Controllers\Controller;
use App\Models\Caixa;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaixaController extends Controller
{
    /**
     * Lista o histórico de abertura e fechamento de caixas.
     */
    public function index(Request $request): View
    {
        $query = Caixa::with('usuario')->orderByDesc('data_abertura');

        // Filtros
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_abertura', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_abertura', '<=', $request->data_fim);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $caixas = $query->paginate(20);
        $usuarios = Usuario::where('ativo', true)->orderBy('nome')->get(['id', 'nome']);

        return view('painel.relatorios.caixas.index', [
            'caixas'   => $caixas,
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Detalhes de um caixa específico (movimentações).
     */
    public function show(Caixa $caixa): View
    {
        $caixa->load(['usuario', 'movimentacoes.pedido.cliente']);
        
        return view('painel.relatorios.caixas.show', [
            'caixa' => $caixa
        ]);
    }

    /**
     * Realiza o fechamento administrativo do caixa.
     */
    public function fechar(Request $request, Caixa $caixa)
    {
        try {
            if ($caixa->status !== 'aberto') {
                return redirect()->back()->with('erro', 'Este caixa já se encontra fechado.');
            }

            // Calcula totais
            $totalVendas = $caixa->movimentacoes()->sum('valor');
            $valorInformado = (float) $request->input('valor_fechamento', 0);
            
            // Valor Esperado = Inicial + Entradas
            $esperado = $caixa->valor_inicial + $totalVendas;
            $diferenca = $valorInformado - $esperado;

            $caixa->update([
                'data_fechamento'  => now(),
                'valor_vendas'     => $totalVendas,
                'valor_fechamento' => $valorInformado,
                'diferenca'        => $diferenca,
                'status'           => 'fechado',
                'observacoes'      => $request->input('observacoes') . ' (Fechamento Administrativo)',
            ]);

            return redirect()->route('admin.bi.caixas.show', $caixa->id)
                ->with('sucesso', 'Caixa encerrado administrativamente com sucesso.');
        } catch (\Exception $e) {
            return redirect()->back()->with('erro', 'Erro ao fechar caixa: ' . $e->getMessage());
        }
    }
}
