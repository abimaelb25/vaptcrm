<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Inventory;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:00
*/

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Usuario;
use App\Services\SaaS\FinancePlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function __construct(
        protected FinancePlanService $planService
    ) {}

    public function index(): View
    {
        if (!$this->planService->canUsePremium()) {
             return view('painel.billing.upgrade_needed', ['feature' => 'Gestão de Ativos e Equipamentos']);
        }

        return view('painel.estoque.ativos.index', [
            'assets' => Asset::latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('painel.estoque.ativos.form', [
            'asset' => new Asset(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string',
            'marca' => 'nullable|string',
            'modelo' => 'nullable|string',
            'data_aquisicao' => 'required|date',
            'valor_aquisicao' => 'required|numeric',
            'vida_util_meses' => 'required|integer|min:1',
            'valor_residual' => 'nullable|numeric',
            'status' => 'required|in:ativo,manutencao,inativo',
            'setor' => 'nullable|string',
        ]);

        Asset::create($data);

        return redirect()->route('admin.inventory.assets.index')->with('sucesso', 'Equipamento cadastrado.');
    }

    public function show(Asset $asset): View
    {
        $asset->load('maintenances.responsavel');
        return view('painel.estoque.ativos.show', [
            'asset' => $asset,
            'usuarios' => Usuario::where('ativo', true)->get(),
        ]);
    }

    public function storeMaintenance(Request $request, Asset $asset): RedirectResponse
    {
        $data = $request->validate([
            'tipo' => 'required|in:preventiva,corretiva',
            'data' => 'required|date',
            'custo' => 'required|numeric',
            'descricao' => 'required|string',
            'responsavel_id' => 'nullable|exists:usuarios,id',
        ]);

        $asset->maintenances()->create($data);

        // Se for corretiva, opcionalmente coloca a máquina em manutenção
        if ($data['tipo'] === 'corretiva') {
            $asset->update(['status' => 'manutencao']);
        }

        return back()->with('sucesso', 'Manutenção registrada.');
    }
}
