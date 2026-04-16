<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Inventory;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:40
*/

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\Fornecedor;
use App\Services\Domain\InventoryService;
use App\Services\SaaS\FinancePlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsumoController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected FinancePlanService $planService
    ) {}

    public function index(Request $request): View
    {
        $query = Insumo::query();

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('estoque_baixo')) {
            $query->whereColumn('estoque_atual', '<=', 'estoque_minimo');
        }

        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        return view('painel.estoque.insumos.index', [
            'insumos' => $query->latest()->paginate(30),
            'categorias' => Insumo::distinct()->pluck('categoria')->filter()->values(),
            'alertasCount' => Insumo::whereColumn('estoque_atual', '<=', 'estoque_minimo')->count(),
        ]);
    }

    public function create(): View
    {
        return view('painel.estoque.insumos.form', [
            'insumo' => new Insumo(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'codigo_interno' => ['nullable', 'string', 'max:50'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'unidade_medida' => ['required', 'string'],
            'estoque_minimo' => ['required', 'numeric', 'min:0'],
            'estoque_maximo' => ['nullable', 'numeric', 'min:0'],
            'observacao' => ['nullable', 'string'],
        ]);

        Insumo::create($data);

        return redirect()->route('admin.inventory.insumos.index')->with('sucesso', 'Insumo cadastrado com sucesso.');
    }

    public function edit(Insumo $insumo): View
    {
        return view('painel.estoque.insumos.form', compact('insumo'));
    }

    public function update(Request $request, Insumo $insumo): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'codigo_interno' => ['nullable', 'string', 'max:50'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'unidade_medida' => ['required', 'string'],
            'estoque_minimo' => ['required', 'numeric', 'min:0'],
            'estoque_maximo' => ['nullable', 'numeric', 'min:0'],
            'ativo' => ['required', 'boolean'],
            'observacao' => ['nullable', 'string'],
        ]);

        $insumo->update($data);

        return redirect()->route('admin.inventory.insumos.index')->with('sucesso', 'Insumo atualizado.');
    }

    /**
     * Tela de Alertas (Estoque Baixo).
     */
    public function alertas(): View
    {
        if (!$this->planService->canUsePro()) {
            return view('painel.billing.upgrade_needed', ['feature' => 'Alertas de Estoque Crítico']);
        }

        return view('painel.estoque.insumos.alertas', [
            'insumos' => Insumo::whereColumn('estoque_atual', '<=', 'estoque_minimo')->latest()->get(),
        ]);
    }
}
