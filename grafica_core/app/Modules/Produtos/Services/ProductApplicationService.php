<?php

declare(strict_types=1);

namespace App\Modules\Produtos\Services;

use App\Models\Produto;
use App\Modules\Produtos\Repositories\ProductRepository;
use App\Services\Domain\CatalogService;
use App\Services\SaaS\ProductPlanService;
use App\Services\SaaS\SaaSService;

class ProductApplicationService
{
    public function __construct(
        private readonly ProductRepository $repository,
        private readonly CatalogService $catalogService,
        private readonly SaaSService $saasService,
        private readonly ProductPlanService $planService,
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
        return [
            'categorias' => $this->repository->activeCategories(),
            'canAdvanced' => $this->planService->canUseAdvancedFeatures(),
            'canTechnical' => $this->planService->canUseTechnicalModule(),
        ];
    }

    public function editPayload(Produto $produto): array
    {
        return [
            'produto' => $this->repository->loadForEdit($produto),
            'categorias' => $this->repository->activeCategories(),
            'canAdvanced' => $this->planService->canUseAdvancedFeatures(),
            'canTechnical' => $this->planService->canUseTechnicalModule(),
        ];
    }

    public function save(array $dados, ?Produto $produto = null): void
    {
        $this->catalogService->saveProduct($dados, $produto);
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
