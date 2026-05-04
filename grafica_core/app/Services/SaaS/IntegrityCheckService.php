<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Exceptions\SaaS\OutdatedDatabaseException;
use App\Models\Loja;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\Plano;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IntegrityCheckService
{
    /**
     * @return list<string>
     */
    public function criticalTables(): array
    {
        return [
            'saas_plano_features',
            'saas_plano_limits',
            'saas_usage_logs',
        ];
    }

    public function ensureRuntimeIntegrity(): void
    {
        if ($this->shouldSkipRuntimeIntegrityCheck()) {
            return;
        }

        $missingTables = $this->missingCriticalTables();

        if ($missingTables !== []) {
            throw new OutdatedDatabaseException($missingTables);
        }
    }

    /**
     * @return array{
     *     ok: bool,
     *     missing_tables: list<string>,
     *     plans_total: int,
     *     plans_without_features: list<string>,
     *     plans_without_limits: list<string>,
     *     orphan_feature_rows: int,
    *     orphan_limit_rows: int,
    *     stores_without_subscription: int,
    *     stores_without_plan: int,
    *     subscriptions_with_missing_plan: int,
    *     subscriptions_with_inactive_plan: int,
    *     invalid_usage_rows: int,
    *     subscriptions_with_invalid_billing_cycle: int,
    *     subscriptions_with_invalid_next_billing_at: int,
    *     subscriptions_with_expired_status_inconsistency: int
     * }
     */
    public function fullReport(): array
    {
        $missingTables = $this->missingCriticalTables();

        if ($missingTables !== []) {
            return [
                'ok' => false,
                'missing_tables' => $missingTables,
                'plans_total' => 0,
                'plans_without_features' => [],
                'plans_without_limits' => [],
                'orphan_feature_rows' => 0,
                'orphan_limit_rows' => 0,
                'stores_without_subscription' => 0,
                'stores_without_plan' => 0,
                'subscriptions_with_missing_plan' => 0,
                'subscriptions_with_inactive_plan' => 0,
                'invalid_usage_rows' => 0,
                'subscriptions_with_invalid_billing_cycle' => 0,
                'subscriptions_with_invalid_next_billing_at' => 0,
                'subscriptions_with_expired_status_inconsistency' => 0,
            ];
        }

        $plans = Plano::query()
            ->withCount(['features', 'limits'])
            ->orderBy('id')
            ->get();

        $plansWithoutFeatures = [];
        $plansWithoutLimits = [];

        foreach ($plans as $plan) {
            if ((int) $plan->features_count === 0) {
                $plansWithoutFeatures[] = $this->formatPlanLabel($plan->id, $plan->nome, $plan->slug);
            }

            if ((int) $plan->limits_count === 0) {
                $plansWithoutLimits[] = $this->formatPlanLabel($plan->id, $plan->nome, $plan->slug);
            }
        }

        $orphanFeatureRows = (int) DB::table('saas_plano_features')
            ->leftJoin('saas_planos', 'saas_planos.id', '=', 'saas_plano_features.plano_id')
            ->whereNull('saas_planos.id')
            ->count();

        $orphanLimitRows = (int) DB::table('saas_plano_limits')
            ->leftJoin('saas_planos', 'saas_planos.id', '=', 'saas_plano_limits.plano_id')
            ->whereNull('saas_planos.id')
            ->count();

        $latestAssinaturasSub = DB::table('saas_assinaturas as inner_sa')
            ->selectRaw('MAX(inner_sa.id) as latest_id')
            ->groupBy('inner_sa.loja_id');

        $latestAssinaturas = Assinatura::query()
            ->whereIn('id', $latestAssinaturasSub)
            ->get()
            ->keyBy('loja_id');

        $storesWithoutSubscription = 0;
        $storesWithoutPlan = 0;
        $subscriptionsWithMissingPlan = 0;
        $subscriptionsWithInactivePlan = 0;

        Loja::query()->select(['id', 'plano_id'])->chunk(200, function ($lojas) use (&$storesWithoutSubscription, &$storesWithoutPlan, &$subscriptionsWithMissingPlan, &$subscriptionsWithInactivePlan, $latestAssinaturas): void {
            foreach ($lojas as $loja) {
                $assinatura = $latestAssinaturas->get($loja->id);

                if (! $assinatura) {
                    $storesWithoutSubscription++;
                }

                $effectivePlanId = $assinatura?->plano_id ?: $loja->plano_id;
                if (! $effectivePlanId) {
                    $storesWithoutPlan++;
                    continue;
                }

                $plano = $assinatura?->plano;
                if ($assinatura && (! $plano || ! $plano->exists)) {
                    $subscriptionsWithMissingPlan++;
                    continue;
                }

                if ($plano && ! (bool) $plano->ativo) {
                    $subscriptionsWithInactivePlan++;
                }
            }
        });

        $invalidUsageRows = (int) DB::table('saas_usage_logs')
            ->where(function ($query): void {
                $query->whereNull('event_type')
                    ->orWhere('event_type', '')
                    ->orWhere('occurred_at', '>', now()->addMinutes(5))
                    ->orWhere('used_total', '<', 0);
            })
            ->count();

        $invalidBillingCycleRows = (int) Assinatura::query()
            ->whereNotNull('billing_cycle')
            ->whereNotIn('billing_cycle', [Assinatura::BILLING_MONTHLY, Assinatura::BILLING_YEARLY])
            ->count();

        $invalidNextBillingRows = (int) Assinatura::query()
            ->where(function ($query): void {
                $query->whereIn('status', [Assinatura::STATUS_ACTIVE, Assinatura::STATUS_PAST_DUE])
                    ->where(function ($nested): void {
                        $nested->whereNull('next_billing_at')
                            ->orWhere('next_billing_at', '<', now()->subYears(2));
                    });
            })
            ->orWhere(function ($query): void {
                $query->where('status', Assinatura::STATUS_TRIAL)
                    ->whereNull('next_billing_at')
                    ->whereNull('trial_ends_at');
            })
            ->count();

        $expiredStatusInconsistencyRows = (int) Assinatura::query()
            ->where(function ($query): void {
                $query->where(function ($trial): void {
                    $trial->where('status', Assinatura::STATUS_TRIAL)
                        ->whereNotNull('trial_ends_at')
                        ->where('trial_ends_at', '<', now());
                })->orWhere(function ($active): void {
                    $active->where('status', Assinatura::STATUS_ACTIVE)
                        ->whereNotNull('ends_at')
                        ->where('ends_at', '<', now());
                });
            })
            ->count();

        return [
            'ok' => $plans->isNotEmpty()
                && $plansWithoutFeatures === []
                && $plansWithoutLimits === []
                && $orphanFeatureRows === 0
                && $orphanLimitRows === 0
                && $storesWithoutSubscription === 0
                && $storesWithoutPlan === 0
                && $subscriptionsWithMissingPlan === 0
                && $subscriptionsWithInactivePlan === 0
                && $invalidUsageRows === 0
                && $invalidBillingCycleRows === 0
                && $invalidNextBillingRows === 0
                && $expiredStatusInconsistencyRows === 0,
            'missing_tables' => [],
            'plans_total' => $plans->count(),
            'plans_without_features' => $plansWithoutFeatures,
            'plans_without_limits' => $plansWithoutLimits,
            'orphan_feature_rows' => $orphanFeatureRows,
            'orphan_limit_rows' => $orphanLimitRows,
            'stores_without_subscription' => $storesWithoutSubscription,
            'stores_without_plan' => $storesWithoutPlan,
            'subscriptions_with_missing_plan' => $subscriptionsWithMissingPlan,
            'subscriptions_with_inactive_plan' => $subscriptionsWithInactivePlan,
            'invalid_usage_rows' => $invalidUsageRows,
            'subscriptions_with_invalid_billing_cycle' => $invalidBillingCycleRows,
            'subscriptions_with_invalid_next_billing_at' => $invalidNextBillingRows,
            'subscriptions_with_expired_status_inconsistency' => $expiredStatusInconsistencyRows,
        ];
    }

    /**
     * @return list<string>
     */
    public function missingCriticalTables(): array
    {
        $missing = [];

        foreach ($this->criticalTables() as $table) {
            if (! Schema::hasTable($table)) {
                $missing[] = $table;
            }
        }

        return $missing;
    }

    private function shouldSkipRuntimeIntegrityCheck(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        if (! app()->runningInConsole()) {
            return false;
        }

        $command = $_SERVER['argv'][1] ?? null;

        if (! is_string($command) || $command === '') {
            return false;
        }

        $skippedCommands = [
            'migrate',
            'migrate:fresh',
            'migrate:install',
            'migrate:refresh',
            'migrate:reset',
            'migrate:rollback',
            'migrate:status',
            'db:seed',
            'db:wipe',
            'schema:dump',
            'package:discover',
            'vendor:publish',
            'key:generate',
            'saas:check-integrity',
        ];

        return in_array($command, $skippedCommands, true);
    }

    private function formatPlanLabel(int $id, string $name, string $slug): string
    {
        return sprintf('#%d %s (%s)', $id, $name, $slug);
    }
}