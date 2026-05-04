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
use App\Services\Domain\InventoryConversionService;
use App\Services\SaaS\FinancePlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EstoqueMovimentacaoController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected InventoryConversionService $conversionService,
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
        $insumos = Insumo::where('ativo', true)->get([
            'id',
            'nome',
            'unidade_medida',
            'unidade_compra',
            'quantidade_por_compra',
            'quantidade_subunidades_por_compra',
            'unidade_subunidade',
            'quantidade_consumo_por_subunidade',
            'estoque_atual',
        ]);

        return view('painel.estoque.movimentacoes.entrada', [
            'insumos'     => $insumos,
            'fornecedores' => Fornecedor::where('ativo', true)->get(),
            'insumo_id'   => $request->insumo_id,
            // Mapa id→dados de conversão para uso no JS da view
            'insumosJson' => $insumos->keyBy('id')->map(fn ($i) => [
                'nome' => $i->nome,
                'unidade_medida'       => $i->unidade_medida,
                'unidade_compra'       => $i->unidade_compra,
                'quantidade_por_compra' => (float) ($i->quantidade_por_compra ?? 1),
                'fator_conversao'      => (float) $i->getFatorTotalConversao(),
                'tem_conversao'        => $i->temConversaoUnidade(),
                'resumo_embalagem' => $this->conversionService->getPackagingSummary($i),
            ])->toJson(),
        ]);
    }

    public function processarEntrada(Request $request): RedirectResponse
    {
        $lojaId = (int) auth()->user()->loja_id;

        $data = $request->validate([
            'insumo_id'          => ['required', Rule::exists('insumos', 'id')->where(fn ($query) => $query->where('loja_id', $lojaId))],
            'quantidade'         => ['required', 'numeric', 'min:0.0001'],
            'custo_unitario'     => ['required', 'numeric', 'min:0'],
            'em_unidade_compra'  => ['nullable', 'boolean'],
            'fornecedor_id'      => ['nullable', Rule::exists('fornecedores', 'id')->where(fn ($query) => $query->where('loja_id', $lojaId))],
            'data_movimentacao'  => ['required', 'date'],
            'descricao'          => ['nullable', 'string', 'max:255'],
        ]);

        $insumo = Insumo::findOrFail($data['insumo_id']);
        $data['origem_tela'] = 'entrada';
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
            'insumo_id' => ['required', Rule::exists('insumos', 'id')->where(fn ($query) => $query->where('loja_id', (int) auth()->user()->loja_id))],
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

           $data['origem_tela'] = 'saida';
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
        $motivosRapidos = [
            'balanco_mensal',
            'correcao_lancamento',
            'perda_vazamento',
            'inventario_fisico',
            'ajuste_manual',
            'outro',
        ];

        $data = $request->validate([
            'modo_ajuste' => ['nullable', Rule::in(['direto', 'guiado'])],
            'quantidade' => ['nullable', 'numeric', 'min:0', 'required_if:modo_ajuste,direto'],
            'qtd_embalagens_contadas' => ['nullable', 'numeric', 'min:0', 'required_if:modo_ajuste,guiado'],
            'volume_por_embalagem' => ['nullable', 'numeric', 'min:0', 'required_if:modo_ajuste,guiado'],
            'motivo_rapido' => ['required', Rule::in($motivosRapidos)],
            'detalhe_motivo' => ['nullable', 'string', 'max:180', 'required_if:motivo_rapido,outro'],
        ]);

        $modoAjuste = (string) ($data['modo_ajuste'] ?? 'direto');

        if ($modoAjuste === 'guiado') {
            if (!$insumo->temConversaoUnidade()) {
                return back()
                    ->withErrors(['modo_ajuste' => 'Modo guiado requer insumo com conversao de unidade configurada.'])
                    ->withInput();
            }

            $qtdEmbalagens = (float) ($data['qtd_embalagens_contadas'] ?? 0);
            $volumePorEmbalagem = (float) ($data['volume_por_embalagem'] ?? 0);

            $data['quantidade'] = round($qtdEmbalagens * $volumePorEmbalagem, 4);

            if (empty($data['detalhe_motivo'])) {
                $data['detalhe_motivo'] = sprintf(
                    'Ajuste guiado: %s x %s %s',
                    rtrim(rtrim(number_format($qtdEmbalagens, 4, '.', ''), '0'), '.'),
                    rtrim(rtrim(number_format($volumePorEmbalagem, 4, '.', ''), '0'), '.'),
                    $insumo->unidade_medida
                );
            }
        }

        $data['origem_tela'] = 'ajuste';
        $this->inventoryService->registrarAjuste($insumo, $data);

        return redirect()->route('admin.inventory.insumos.index')->with('sucesso', "Ajuste de estoque concluído para {$insumo->nome}.");
    }
}
