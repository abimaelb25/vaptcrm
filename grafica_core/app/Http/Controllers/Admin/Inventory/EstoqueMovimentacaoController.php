<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Inventory;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:45
*/

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\Fornecedor;
use App\Models\EstoqueMovimentacao;
use App\Services\Domain\InventoryService;
use App\Services\SaaS\FinancePlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstoqueMovimentacaoController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected FinancePlanService $planService
    ) {}

    /**
     * Histórico de Movimentações (Timeline).
     */
    public function index(Request $request): View
    {
        if (!$this->planService->canUsePro()) {
             // Fallback para histórico limitado no plano básico
             $limit = 50;
        } else {
             $limit = 100;
        }

        $query = EstoqueMovimentacao::with(['insumo', 'fornecedor', 'usuario']);

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('insumo_id')) {
            $query->where('insumo_id', $request->insumo_id);
        }

        if ($request->filled('inicio')) {
            $query->where('data_movimentacao', '>=', $request->inicio);
        }

        if ($request->filled('fim')) {
            $query->where('data_movimentacao', '<=', $request->fim);
        }

        return view('painel.estoque.movimentacoes.index', [
            'movimentacoes' => $query->latest('data_movimentacao')->paginate($limit),
            'insumos' => Insumo::where('ativo', true)->get(),
        ]);
    }

    /**
     * Tela de Entrada (Compra).
     */
    public function entrada(Request $request): View
    {
        return view('painel.estoque.movimentacoes.entrada', [
            'insumos' => Insumo::where('ativo', true)->get(),
            'fornecedores' => Fornecedor::where('ativo', true)->get(),
            'insumo_id' => $request->insumo_id,
        ]);
    }

    public function processarEntrada(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'insumo_id' => ['required', 'exists:insumos,id'],
            'quantidade' => ['required', 'numeric', 'min:0.0001'],
            'custo_unitario' => ['required', 'numeric', 'min:0'],
            'fornecedor_id' => ['nullable', 'exists:fornecedores,id'],
            'data_movimentacao' => ['required', 'date'],
            'descricao' => ['nullable', 'string', 'max:255'],
        ]);

        $insumo = Insumo::findOrFail($data['insumo_id']);
        $this->inventoryService->registrarEntrada($insumo, $data);

        return redirect()->route('admin.inventory.movimentacoes.index')->with('sucesso', 'Entrada de estoque registrada.');
    }

    /**
     * Tela de Saída (Consumo/Perda).
     */
    public function saida(Request $request): View
    {
        return view('painel.estoque.movimentacoes.saida', [
            'insumos' => Insumo::where('ativo', true)->get(),
            'insumo_id' => $request->insumo_id,
        ]);
    }

    public function processarSaida(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'insumo_id' => ['required', 'exists:insumos,id'],
            'quantidade' => ['required', 'numeric', 'min:0.0001'],
            'origem' => ['required', 'in:manual,perda,producao'],
            'data_movimentacao' => ['required', 'date'],
            'descricao' => ['nullable', 'string', 'max:255'],
        ]);

        $insumo = Insumo::findOrFail($data['insumo_id']);
        
        // Bloqueio de saída maior que estoque
        if ($data['quantidade'] > $insumo->estoque_atual) {
             return back()->with('erro', 'Estoque insuficiente para esta saída.')->withInput();
        }

        $this->inventoryService->registrarSaida($insumo, $data);

        return redirect()->route('admin.inventory.movimentacoes.index')->with('sucesso', 'Saída de estoque registrada.');
    }

    /**
     * Tela de Ajuste (Balanço).
     */
    public function ajuste(Insumo $insumo): View
    {
        return view('painel.estoque.movimentacoes.ajuste', compact('insumo'));
    }

    public function processarAjuste(Request $request, Insumo $insumo): RedirectResponse
    {
        $data = $request->validate([
            'quantidade' => ['required', 'numeric', 'min:0'],
            'descricao' => ['required', 'string', 'max:255'],
        ]);

        $this->inventoryService->registrarAjuste($insumo, $data);

        return redirect()->route('admin.inventory.insumos.index')->with('sucesso', "Ajuste de estoque concluído para {$insumo->nome}.");
    }
}
