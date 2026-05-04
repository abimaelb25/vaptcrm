<?php

declare(strict_types=1);

namespace App\Modules\Produtos\Services;

use App\Models\Produto;
use App\Models\ProductionPhase;
use App\Modules\Produtos\Repositories\ProductRepository;
use App\Services\Domain\CatalogService;
use App\Services\SaaS\ProductPlanService;
use App\Services\SaaS\SaaSService;
use App\Services\Domain\PricingEngineService;

class ProductApplicationService
{
    public function __construct(
        private readonly ProductRepository $repository,
        private readonly CatalogService $catalogService,
        private readonly SaaSService $saasService,
        private readonly ProductPlanService $planService,
        private readonly PricingEngineService $pricingEngine,
    ) {
    }

    public function indexPayload(): array
    {
        return [
            'produtos' => $this->repository->paginateIndex(),
            'categorias' => $this->repository->activeCategories(),
            'canAdvanced' => $this->planService->canUseAdvancedFeatures(),
        ];
    }

    public function createPayload(): array
    {
        $fasesComEtapas = ProductionPhase::with(['steps' => function ($query) {
            $query->where('ativo', true)->orderBy('ordem');
        }])->orderBy('ordem')->get();

        return [
            'categorias' => $this->repository->activeCategories(),
            'canAdvanced' => $this->planService->canUseAdvancedFeatures(),
            'canTechnical' => $this->planService->canUseTechnicalModule(),
            'fasesProducao' => $fasesComEtapas,
        ];
    }

    public function editPayload(Produto $produto): array
    {
        $fasesComEtapas = ProductionPhase::with(['steps' => function ($query) {
            $query->where('ativo', true)->orderBy('ordem');
        }])->orderBy('ordem')->get();

        return [
            'produto' => $this->repository->loadForEdit($produto),
            'categorias' => $this->repository->activeCategories(),
            'canAdvanced' => $this->planService->canUseAdvancedFeatures(),
            'canTechnical' => $this->planService->canUseTechnicalModule(),
            'fasesProducao' => $fasesComEtapas,
            'insumos_loja' => \App\Models\Insumo::where('ativo', true)->orderBy('nome')->get(),
            'servicos_loja' => \App\Models\ServicoProducao::where('ativo', true)->orderBy('nome')->get(),
            'config_precificacao' => \App\Models\LojaPrecificacaoConfig::first(),
        ];
    }

    public function save(array $dados, ?Produto $produto = null): void
    {
        // Extraímos a ficha técnica para não poluir o saveProduct base
        $dadosFicha = $dados['ficha_tecnica'] ?? null;
        unset($dados['ficha_tecnica']);

        \Illuminate\Support\Facades\DB::transaction(function () use ($dados, $dadosFicha, &$produto) {
            $produto = $this->catalogService->saveProduct($dados, $produto);

            $lojaId = $produto->loja_id ?? auth()->user()->loja_id;
            $config = \App\Models\LojaPrecificacaoConfig::where('loja_id', $lojaId)->first();

            // Só persiste a ficha e recalcula se vier no payload e a loja usar precificação dinâmica
            if ($config?->precificacao_dinamica_ativa && $dadosFicha !== null) {
                $this->persistirFichaTecnica($produto, $dadosFicha);
                
                // Chama a Engine para gerar o histórico e atualizar o custo_base e preço sugerido do produto
                $resultado = $this->pricingEngine->recalcularProdutoExistente($produto);
                $this->pricingEngine->efetivarRecalculo($produto, $resultado, 'atualizacao_ficha_tecnica', auth()->id());
            }
        });
    }

    private function persistirFichaTecnica(Produto $produto, array $dadosFicha): void
    {
        $ficha = $produto->fichaTecnica()->updateOrCreate(
            ['produto_id' => $produto->id],
            [
                'loja_id' => $produto->loja_id,
                'tempo_producao_min' => $dadosFicha['tempo_producao_min'] ?? 0,
                'quantidade_base' => $dadosFicha['quantidade_base'] ?? 1,
                'perda_percentual' => $dadosFicha['perda_percentual'] ?? 0,
            ]
        );

        $ficha->insumos()->delete();
        if (!empty($dadosFicha['insumos'])) {
            foreach ($dadosFicha['insumos'] as $insumo) {
                $ficha->insumos()->create([
                    'loja_id' => $produto->loja_id,
                    'insumo_id' => $insumo['insumo_id'],
                    'quantidade' => $insumo['quantidade'],
                    'fator_perda' => $insumo['fator_perda'] ?? 0,
                ]);
            }
        }

        $ficha->servicos()->delete();
        if (!empty($dadosFicha['servicos'])) {
            foreach ($dadosFicha['servicos'] as $servico) {
                $ficha->servicos()->create([
                    'loja_id' => $produto->loja_id,
                    'servico_producao_id' => $servico['servico_producao_id'],
                    'quantidade' => $servico['quantidade'],
                    'fator_aplicacao' => 1.0,
                ]);
            }
        }
    }

    public function duplicate(Produto $produto): Produto
    {
        return $this->repository->duplicate($produto);
    }

    public function toggle(Produto $produto): void
    {
        $this->repository->toggleActive($produto);
    }

    public function remove(Produto $produto): void
    {
        $this->repository->delete($produto);
    }

    public function canCreateProduct(): bool
    {
        return $this->saasService->podeAdicionar('produto');
    }

    public function canUseAdvancedFor(string $modeloCadastro): bool
    {
        if ($modeloCadastro === 'simples') {
            return true;
        }

        return $this->planService->canUseAdvancedFeatures();
    }
}