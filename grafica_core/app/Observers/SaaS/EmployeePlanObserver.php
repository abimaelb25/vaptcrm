<?php

declare(strict_types=1);

namespace App\Observers\SaaS;

use App\Models\Employee;
use App\Services\SaaS\LimitEnforcementService;
use App\Services\SaaS\TenantContext;
use App\Services\SaaS\UsageTrackerService;

class EmployeePlanObserver
{
    public function __construct(
        private readonly LimitEnforcementService $limitEnforcementService,
        private readonly TenantContext $tenantContext,
        private readonly UsageTrackerService $usageTrackerService,
    ) {}

    public function creating(Employee $employee): void
    {
        // Licença SaaS (max_usuarios) só deve ser consumida quando o
        // colaborador recebe acesso sistêmico real (user_id preenchido).
        if ($employee->user_id) {
            $this->limitEnforcementService->checkLimit($this->resolveLojaId($employee->loja_id), 'usuario');
        }
    }

    public function created(Employee $employee): void
    {
        $lojaId = $this->resolveLojaId($employee->loja_id);

        if ($employee->user_id) {
            $this->usageTrackerService->trackUserCreated($lojaId, [
                'employee_id' => $employee->id,
                'user_id' => $employee->user_id,
            ]);
        }
    }

    private function resolveLojaId(?int $lojaId): int
    {
        if ($lojaId) {
            return $lojaId;
        }

        $resolved = auth()->user()?->loja_id ?? $this->tenantContext->getLojaId();

        if (! $resolved) {
            throw new \RuntimeException('Tenant nao identificado para criacao de funcionario.');
        }

        return (int) $resolved;
    }
}
