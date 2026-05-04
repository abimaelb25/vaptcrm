<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Models\Loja;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\Plano;
use App\Models\SaaS\PagamentoSaaS;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class CommercialSubscriptionService
{
    public function __construct(
        private readonly PlanService $planService,
        private readonly SubscriptionSyncService $subscriptionSyncService,
    ) {}

    /**
     * @return array{allowed: bool, violations: list<array{key:string,current:int,limit:int}>}
     */
    public function validateDowngrade(Loja $loja, Plano $targetPlan): array
    {
        $limitsToCheck = [
            'max_produtos',
            'max_usuarios',
            'max_pedidos_mes',
            'max_ops_simultaneas',
            'max_producao_ativa',
            'max_storage_mb',
        ];

        $violations = [];

        foreach ($limitsToCheck as $limitKey) {
            $newLimit = $targetPlan->resolveLimit($limitKey);
            if ($newLimit === null) {
                continue;
            }

            $currentUsage = $this->planService->currentUsage($limitKey, $loja->id);
            if ($currentUsage > $newLimit) {
                $violations[] = [
                    'key' => $limitKey,
                    'current' => $currentUsage,
                    'limit' => $newLimit,
                ];
            }
        }

        return [
            'allowed' => $violations === [],
            'violations' => $violations,
        ];
    }

    /**
     * @return array{assinatura: Assinatura, prorata: float, violations: list<array{key:string,current:int,limit:int}>}
     */
    public function changePlan(
        Loja $loja,
        Plano $targetPlan,
        string $billingCycle = Assinatura::BILLING_MONTHLY,
        bool $strictDowngrade = false
    ): array {
        if (! in_array($billingCycle, [Assinatura::BILLING_MONTHLY, Assinatura::BILLING_YEARLY], true)) {
            throw new InvalidArgumentException('Ciclo de cobrança inválido.');
        }

        if (! $targetPlan->ativo) {
            throw new RuntimeException('Plano de destino está inativo.');
        }

        return DB::transaction(function () use ($loja, $targetPlan, $billingCycle, $strictDowngrade): array {
            $assinatura = Assinatura::query()->where('loja_id', $loja->id)->latest('id')->first();
            if (! $assinatura) {
                $assinatura = $this->subscriptionSyncService->syncSubscriptionForStore($loja);
                if (! $assinatura) {
                    throw new RuntimeException('Falha ao criar assinatura inicial para a loja.');
                }
            }

            $isDowngrade = $this->priceForCycle($targetPlan, $billingCycle) < $this->currentPlanPrice($assinatura, $billingCycle);
            $downgradeValidation = $this->validateDowngrade($loja, $targetPlan);

            if ($isDowngrade && $strictDowngrade && ! $downgradeValidation['allowed']) {
                throw new RuntimeException('Downgrade bloqueado: ajuste o uso para caber no novo plano.');
            }

            $prorata = $this->calculateProration($assinatura, $targetPlan, $billingCycle);

            $assinatura->update([
                'plano_id' => $targetPlan->id,
                'plan_version' => $targetPlan->version ?? 1,
                'plan_snapshot' => [
                    'plano_id' => $targetPlan->id,
                    'nome' => $targetPlan->nome,
                    'slug' => $targetPlan->slug,
                    'version' => $targetPlan->version ?? 1,
                    'price_monthly' => $targetPlan->commercialMonthlyPrice(),
                    'price_yearly' => $targetPlan->commercialYearlyPrice(),
                ],
                'billing_cycle' => $billingCycle,
                'status' => Assinatura::STATUS_ACTIVE,
                'next_billing_at' => $this->resolveNextBillingDate($assinatura, $billingCycle),
            ]);

            $loja->update([
                'plano_id' => $targetPlan->id,
            ]);

            if ($prorata > 0) {
                PagamentoSaaS::create([
                    'loja_id' => $loja->id,
                    'assinatura_id' => $assinatura->id,
                    'valor' => $prorata,
                    'moeda' => 'BRL',
                    'status' => 'pendente',
                    'periodo_inicio' => now()->toDateString(),
                    'periodo_fim' => $assinatura->next_billing_at?->toDateString() ?? now()->addMonth()->toDateString(),
                    'vencimento_em' => now()->addDay(),
                    'tentativas' => 0,
                ]);
            }

            return [
                'assinatura' => $assinatura->fresh(['plano']),
                'prorata' => $prorata,
                'violations' => $downgradeValidation['violations'],
            ];
        });
    }

    public function ensureTrialForNewStore(Loja $loja): Assinatura
    {
        $assinatura = Assinatura::query()->where('loja_id', $loja->id)->latest('id')->first();
        if (! $assinatura) {
            $assinatura = $this->subscriptionSyncService->syncSubscriptionForStore($loja);
        }

        if (! $assinatura) {
            throw new RuntimeException('Não foi possível garantir trial para a loja.');
        }

        if (! $assinatura->trial_ends_at) {
            $days = max(1, (int) ($assinatura->plano?->trial_days ?? config('saas.trial_default_days', 14)));
            $assinatura->update([
                'status' => Assinatura::STATUS_TRIAL,
                'trial_ends_at' => now()->addDays($days),
                'next_billing_at' => now()->addDays($days),
            ]);
        }

        return $assinatura->fresh();
    }

    private function calculateProration(Assinatura $current, Plano $target, string $billingCycle): float
    {
        $currentPrice = $this->currentPlanPrice($current, $billingCycle);
        $targetPrice = $this->priceForCycle($target, $billingCycle);

        $periodEnd = $current->next_billing_at ?? $current->renews_at ?? now()->addMonth();
        if ($periodEnd->isPast()) {
            return 0.0;
        }

        $remainingDays = max(1, now()->diffInDays($periodEnd));
        $periodDays = $billingCycle === Assinatura::BILLING_YEARLY ? 365 : 30;
        $remainingFactor = min(1, $remainingDays / $periodDays);

        $difference = $targetPrice - $currentPrice;
        if ($difference <= 0) {
            return 0.0;
        }

        return round($difference * $remainingFactor, 2);
    }

    private function resolveNextBillingDate(Assinatura $assinatura, string $billingCycle)
    {
        if ($assinatura->trial_ends_at && $assinatura->status === Assinatura::STATUS_TRIAL && $assinatura->trial_ends_at->isFuture()) {
            return $assinatura->trial_ends_at;
        }

        return $billingCycle === Assinatura::BILLING_YEARLY
            ? now()->addYear()
            : now()->addMonth();
    }

    private function currentPlanPrice(Assinatura $assinatura, string $billingCycle): float
    {
        $plano = $assinatura->plano;
        if (! $plano) {
            return 0.0;
        }

        return $this->priceForCycle($plano, $billingCycle);
    }

    private function priceForCycle(Plano $plan, string $billingCycle): float
    {
        if ($billingCycle === Assinatura::BILLING_YEARLY) {
            return (float) ($plan->commercialYearlyPrice() ?? ($plan->commercialMonthlyPrice() * 12));
        }

        return $plan->commercialMonthlyPrice();
    }
}
