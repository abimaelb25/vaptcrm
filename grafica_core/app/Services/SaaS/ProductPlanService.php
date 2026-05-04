<?php

declare(strict_types=1);

namespace App\Services\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 15/04/2026 14:45
*/

class ProductPlanService
{
    public function __construct(
        private readonly PlanService $planService,
    ) {}

    /**
     * Verifica se a loja possui permissão para usar recursos Premium (Ex: Faixas, Materiais, Acabamentos).
     * No plano Básico (Gratuito/Entry), retorna falso para forçar Upgrade.
     */
    public function canUseAdvancedFeatures(): bool
    {
        return $this->planService->hasFeature('modulo_produtos_avancado')
            || $this->planService->hasFeature('modulo_producao');
    }

    /**
     * Verifica se pode cadastrar produtos avançados (Nível 3).
     */
    public function canUseTechnicalModule(): bool
    {
        return $this->planService->hasFeature('modulo_producao');
    }
}
