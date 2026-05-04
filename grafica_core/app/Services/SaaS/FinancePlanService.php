<?php

declare(strict_types=1);

namespace App\Services\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 15/04/2026 18:55
| Descrição: Controle de acesso a recursos financeiros baseados no plano SaaS.
*/

class FinancePlanService
{
    public function __construct(
        private readonly PlanService $planService,
    ) {}

    /**
     * Verifica o nível financeiro permitido.
     * Retornos: 'basico', 'pro', 'premium'
     */
    public function getFinanceLevel(): string
    {
        if ($this->planService->hasFeature('modulo_financeiro_premium')) {
            return 'premium';
        }

        if ($this->planService->hasFeature('modulo_financeiro')) {
            return 'pro';
        }

        return 'basico';
    }

    public function canUsePro(): bool
    {
        return in_array($this->getFinanceLevel(), ['pro', 'premium']);
    }

    public function canUsePremium(): bool
    {
        return $this->getFinanceLevel() === 'premium';
    }
}
