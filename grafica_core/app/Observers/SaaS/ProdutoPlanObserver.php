<?php

declare(strict_types=1);

namespace App\Observers\SaaS;

use App\Models\Produto;
use App\Services\SaaS\LimitEnforcementService;
use App\Services\SaaS\TenantContext;
use App\Services\SaaS\UsageTrackerService;

class ProdutoPlanObserver
{
    public function __construct(
        private readonly LimitEnforcementService $limitEnforcementService,
        private readonly TenantContext $tenantContext,
        private readonly UsageTrackerService $usageTrackerService,
    ) {}

    public function creating(Produto $produto): void
    {
        if (! ($produto->ativo ?? true)) {
            return;
        }

        $this->limitEnforcementService->checkLimit($this->resolveLojaId($produto->loja_id), 'produto');
    }

    public function created(Produto $produto): void
    {
        if (! ($produto->ativo ?? true)) {
            return;
        }

        $lojaId = $this->resolveLojaId($produto->loja_id);

        $this->usageTrackerService->trackProductCreated($lojaId, [
            'produto_id' => $produto->id,
        ]);
    }

    private function resolveLojaId(?int $lojaId): int
    {
        if ($lojaId) {
            return $lojaId;
        }

        $resolved = auth()->user()?->loja_id ?? $this->tenantContext->getLojaId();

        if (! $resolved) {
            throw new \RuntimeException('Tenant nao identificado para criacao de produto.');
        }

        return (int) $resolved;
    }
}
