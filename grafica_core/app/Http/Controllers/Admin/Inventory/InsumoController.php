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
use App\Services\Domain\InventoryConversionService;
use App\Services\Domain\InsumoConversaoService;
use App\Services\Domain\InsumoOperationalClassifierService;
use App\Services\SaaS\FinancePlanService;
use App\Services\SaaS\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InsumoController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected InventoryConversionService $conversionService,
        protected InsumoConversaoService $insumoConversaoService,
        protected InsumoOperationalClassifierService $insumoClassifierService,
        protected FinancePlanService $planService,
        protected TenantContext $tenantContext,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Insumo::class);
        $query = Insumo::query();

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('estoque_baixo')) {
            $query->where('controlar_estoque', true)
                ->whereColumn('estoque_atual', '<=', 'estoque_minimo');
        }

        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }

        return view('painel.estoque.insumos.index', [
            'insumos' => $query->latest()->paginate(30),
            'categorias' => Insumo::distinct()->pluck('categoria')->filter()->values(),
            'alertasCount' => Insumo::where('controlar_estoque', true)
                ->whereColumn('estoque_atual', '<=', 'estoque_minimo')
                ->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Insumo::class);
        return view('painel.estoque.insumos.form', [
            'insumo' => new Insumo(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Insumo::class);
        $data = $request->validate([
            'nome'                  => ['required', 'string', 'max:255'],
            'codigo_interno'        => ['nullable', 'string', 'max:50'],
            'categoria'             => ['nullable', 'string', 'max:100'],
            'tipo_item_operacional' => ['required', 'in:consumivel,embalagem,componente,apoio,ignorado'],
            'unidade_medida'        => ['required', 'string', 'max:50'],
            'unidade_compra'                       => ['nullable', 'string', 'max:50'],
            'quantidade_por_compra'                => ['nullable', 'required_with:unidade_compra', 'numeric', 'min:0.0001', 'max:999999'],
            'quantidade_subunidades_por_compra'    => ['nullable', 'numeric', 'min:0.0001', 'max:999999'],
            'unidade_subunidade'                   => ['nullable', 'string', 'max:50'],
            'quantidade_consumo_por_subunidade'    => ['nullable', 'numeric', 'min:0.0001', 'max:999999'],
            'controlar_estoque'                    => ['nullable', 'boolean'],
            'usar_na_precificacao'                 => ['nullable', 'boolean'],
            'estoque_minimo'                       => ['required', 'numeric', 'min:0'],
            'estoque_maximo'                       => ['nullable', 'numeric', 'min:0'],
            'observacao'                           => ['nullable', 'string'],
            'submit_action'                        => ['nullable', Rule::in(['save', 'save_and_entry'])],
        ]);

        $data['controlar_estoque'] = $request->boolean('controlar_estoque');
        $data['usar_na_precificacao'] = $request->boolean('usar_na_precificacao');

        $data = $this->insumoClassifierService->normalizarCamposInsumo($data);

        try {
            $data = $this->insumoConversaoService->validateAndNormalizeConversion($data);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['unidade_subunidade' => $e->getMessage()])->withInput();
        }

        $insumo = Insumo::create(array_merge($data, [
            'loja_id' => $this->tenantContext->getLojaId() ?? auth()->user()->loja_id,
        ]));

        if (($data['submit_action'] ?? 'save') === 'save_and_entry') {
            return redirect()
                ->route('admin.inventory.movimentacoes.entrada', ['insumo_id' => $insumo->id])
                ->with('sucesso', 'Item cadastrado. Agora registre a entrada inicial para definir saldo e custo.');
        }

        return redirect()->route('admin.inventory.insumos.index')->with('sucesso', 'Insumo cadastrado com sucesso.');
    }

    public function edit(Insumo $insumo): View
    {
        $this->authorize('update', $insumo);
        return view('painel.estoque.insumos.form', compact('insumo'));
    }

    public function update(Request $request, Insumo $insumo): RedirectResponse
    {
        $this->authorize('update', $insumo);
        $data = $request->validate([
            'nome'                  => ['required', 'string', 'max:255'],
            'codigo_interno'        => ['nullable', 'string', 'max:50'],
            'categoria'             => ['nullable', 'string', 'max:100'],
            'tipo_item_operacional' => ['required', 'in:consumivel,embalagem,componente,apoio,ignorado'],
            'unidade_medida'        => ['required', 'string', 'max:50'],
            'unidade_compra'                       => ['nullable', 'string', 'max:50'],
            'quantidade_por_compra'                => ['nullable', 'required_with:unidade_compra', 'numeric', 'min:0.0001', 'max:999999'],
            'quantidade_subunidades_por_compra'    => ['nullable', 'numeric', 'min:0.0001', 'max:999999'],
            'unidade_subunidade'                   => ['nullable', 'string', 'max:50'],
            'quantidade_consumo_por_subunidade'    => ['nullable', 'numeric', 'min:0.0001', 'max:999999'],
            'controlar_estoque'                    => ['nullable', 'boolean'],
            'usar_na_precificacao'                 => ['nullable', 'boolean'],
            'estoque_minimo'                       => ['required', 'numeric', 'min:0'],
            'estoque_maximo'                       => ['nullable', 'numeric', 'min:0'],
            'ativo'                                => ['required', 'boolean'],
            'observacao'                           => ['nullable', 'string'],
        ]);

        $data['controlar_estoque'] = $request->boolean('controlar_estoque');
        $data['usar_na_precificacao'] = $request->boolean('usar_na_precificacao');

        $data = $this->insumoClassifierService->normalizarCamposInsumo($data);

        try {
            $data = $this->insumoConversaoService->validateAndNormalizeConversion($data);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['unidade_subunidade' => $e->getMessage()])->withInput();
        }

        $temDoisNiveis = !empty($data['unidade_subunidade'])
            && ($data['quantidade_subunidades_por_compra'] ?? 0) > 0
            && ($data['quantidade_consumo_por_subunidade'] ?? 0) > 0;

        // -----------------------------------------------------------------------
        // NORMALIZAÇÃO DE CUSTO AO ATIVAR DOIS NÍVEIS PELA PRIMEIRA VEZ
        //
        // Cenário: insumo tinha conversão simples com fator = quantidade_por_compra (ex: 6).
        // O InventoryService gravou custo_unitario_consumo no nível da subunidade (ex: 8.33/frasco).
        // Ao ativar dois níveis (fator = sub × consumo = 600), os métodos de display
        // getCustoPorSubunidadeEfetivo() e getCustoPorUnidadeCompraEfetivo() multiplicam
        // getCustoEfetivo() pelo novo fator, produzindo valores 100× maiores.
        //
        // Correção: re-escala custo_unitario_consumo do nível antigo para o nível novo.
        //   custo_por_ml = custo_por_frasco × (fatorAntigo / novoFator)
        //   ex: 8.33 × (6 / 600) = 0.0833
        // -----------------------------------------------------------------------
        if ($temDoisNiveis && !$insumo->temDoisNiveisConversao()) {
            $fatorAntigo   = max(0.0001, (float) $insumo->quantidade_por_compra);
            $novoSubQtd    = max(0.0001, (float) ($data['quantidade_subunidades_por_compra'] ?? 1));
            $novoConsumSub = max(0.0001, (float) ($data['quantidade_consumo_por_subunidade'] ?? 1));
            $novoFator     = $novoSubQtd * $novoConsumSub;

            // Só normaliza quando há mudança real de nível (novo fator > fator antigo).
            // Se o usuário já apontava para o total correto (ex: quantidade_por_compra = 600),
            // o custo já está no nível final e não precisa ser dividido.
            if ($novoFator > $fatorAntigo + 0.0001) {
                $ratio = $novoFator / $fatorAntigo;  // ex: 600 / 6 = 100

                if ($insumo->custo_unitario_consumo > 0) {
                    $data['custo_unitario_consumo'] = round((float) $insumo->custo_unitario_consumo / $ratio, 6);
                }
                if ($insumo->custo_medio > 0) {
                    $data['custo_medio'] = round((float) $insumo->custo_medio / $ratio, 6);
                }
                if ($insumo->ultimo_custo > 0) {
                    $data['ultimo_custo'] = round((float) $insumo->ultimo_custo / $ratio, 6);
                }
            }
        }

        $insumo->update($data);

        return redirect()->route('admin.inventory.insumos.index')->with('sucesso', 'Insumo atualizado.');
    }

    /**
     * Tela de Alertas (Estoque Baixo).
     */
    public function destroy(Insumo $insumo): RedirectResponse
    {
        $this->authorize('delete', $insumo);

        $insumo->forceDelete();

        return redirect()
            ->route('admin.inventory.insumos.index')
            ->with('sucesso', 'Insumo excluido permanentemente.');
    }

    public function inativar(Insumo $insumo): RedirectResponse
    {
        $this->authorize('deactivate', $insumo);

        $insumo->inativar(motivo: 'Inativado pelo administrador via painel.');

        return redirect()
            ->route('admin.inventory.insumos.index')
            ->with('sucesso', "Insumo \"{$insumo->nome}\" inativado. O histórico foi preservado.");
    }

    public function alertas(): View
    {
        $this->authorize('viewAny', Insumo::class);
        if (!$this->planService->canUsePro()) {
            return view('painel.billing.upgrade_needed', ['feature' => 'Alertas de Estoque Crítico']);
        }

        return view('painel.estoque.insumos.alertas', [
            'insumos' => Insumo::where('controlar_estoque', true)
                ->whereColumn('estoque_atual', '<=', 'estoque_minimo')
                ->latest()
                ->get(),
        ]);
    }
}
