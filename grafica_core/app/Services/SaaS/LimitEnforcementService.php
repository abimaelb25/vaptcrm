<?php

declare(strict_types=1);

namespace App\Services\SaaS;

class LimitEnforcementService
{
    public function __construct(
        private readonly PlanService $planService,
    ) {}

    public function checkLimit(int $lojaId, string $recurso): bool
    {
        foreach ($this->resolveLimitKeys($recurso) as $limitKey) {
            $this->planService->ensureLimit($limitKey, 1, $lojaId);
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function resolveLimitKeys(string $recurso): array
    {
        return match ($recurso) {
            'produto', 'produtos' => ['max_produtos'],
            'usuario', 'usuarios', 'funcionario', 'funcionarios' => ['max_usuarios'],
            'pedido', 'pedidos' => ['max_pedidos_mes'],
            'op', 'ops', 'ordem_producao', 'ordens_producao' => ['max_ops_simultaneas', 'max_producao_ativa'],
            default => [],
        };
    }
}
