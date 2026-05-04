<?php

declare(strict_types=1);

namespace App\Observers\SaaS;

use App\Models\Pedido;
use App\Services\SaaS\LimitEnforcementService;
use App\Services\SaaS\TenantContext;
use App\Services\SaaS\UsageTrackerService;

class PedidoPlanObserver
{
    public function __construct(
        private readonly LimitEnforcementService $limitEnforcementService,
        private readonly TenantContext $tenantContext,
        private readonly UsageTrackerService $usageTrackerService,
    ) {}

    public function creating(Pedido $pedido): void
    {
        $this->limitEnforcementService->checkLimit($this->resolveLojaId($pedido->loja_id), 'pedido');
    }

    public function created(Pedido $pedido): void
    {
        $lojaId = $this->resolveLojaId($pedido->loja_id);

        $this->usageTrackerService->trackOrderCreated($lojaId, [
            'pedido_id' => $pedido->id,
            'origem' => $pedido->origem,
            'status' => $pedido->status,
        ]);
    }

    private function resolveLojaId(?int $lojaId): int
    {
        if ($lojaId) {
            return $lojaId;
        }

        $resolved = auth()->user()?->loja_id ?? $this->tenantContext->getLojaId();

        if (! $resolved) {
            throw new \RuntimeException('Tenant nao identificado para criacao de pedido.');
        }

        return (int) $resolved;
    }
}
