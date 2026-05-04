<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saas_planos', function (Blueprint $table): void {
            if (! Schema::hasColumn('saas_planos', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('slug');
            }

            if (! Schema::hasColumn('saas_planos', 'legacy_slug')) {
                $table->string('legacy_slug')->nullable()->after('slug');
            }

            if (! Schema::hasColumn('saas_planos', 'is_legacy')) {
                $table->boolean('is_legacy')->default(false)->after('ativo');
            }
        });

        if (! Schema::hasTable('saas_plano_features')) {
            Schema::create('saas_plano_features', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('plano_id')->constrained('saas_planos')->cascadeOnDelete();
                $table->string('feature_key', 120);
                $table->boolean('enabled')->default(true);
                $table->timestamps();

                $table->unique(['plano_id', 'feature_key'], 'saas_plano_features_unique');
                $table->index(['feature_key', 'enabled'], 'saas_plano_features_lookup_idx');
            });
        }

        if (! Schema::hasTable('saas_plano_limits')) {
            Schema::create('saas_plano_limits', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('plano_id')->constrained('saas_planos')->cascadeOnDelete();
                $table->string('limit_key', 120);
                $table->unsignedBigInteger('limit_value')->nullable();
                $table->timestamps();

                $table->unique(['plano_id', 'limit_key'], 'saas_plano_limits_unique');
                $table->index(['limit_key', 'limit_value'], 'saas_plano_limits_lookup_idx');
            });
        }

        if (! Schema::hasTable('saas_usage_logs')) {
            Schema::create('saas_usage_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
                $table->foreignId('assinatura_id')->nullable()->constrained('saas_assinaturas')->nullOnDelete();
                $table->string('event_type', 60)->index();
                $table->string('feature_key', 120)->nullable()->index();
                $table->string('limit_key', 120)->nullable()->index();
                $table->integer('delta')->default(0);
                $table->unsignedBigInteger('used_total')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at')->useCurrent()->index();
                $table->timestamps();

                $table->index(['loja_id', 'limit_key', 'occurred_at'], 'saas_usage_logs_loja_limit_idx');
            });
        }

        Schema::table('saas_assinaturas', function (Blueprint $table): void {
            if (! Schema::hasColumn('saas_assinaturas', 'grace_ends_at')) {
                $table->timestamp('grace_ends_at')->nullable()->after('trial_ends_at');
            }

            if (! Schema::hasColumn('saas_assinaturas', 'plan_version')) {
                $table->unsignedInteger('plan_version')->nullable()->after('plano_id');
            }

            if (! Schema::hasColumn('saas_assinaturas', 'plan_snapshot')) {
                $table->json('plan_snapshot')->nullable()->after('plan_version');
            }

            if (! Schema::hasColumn('saas_assinaturas', 'gateway_provider')) {
                $table->string('gateway_provider', 40)->nullable()->after('stripe_customer_id');
            }

            if (! Schema::hasColumn('saas_assinaturas', 'gateway_subscription_id')) {
                $table->string('gateway_subscription_id')->nullable()->after('gateway_provider');
            }

            if (! Schema::hasColumn('saas_assinaturas', 'gateway_customer_id')) {
                $table->string('gateway_customer_id')->nullable()->after('gateway_subscription_id');
            }

            if (! Schema::hasColumn('saas_assinaturas', 'gateway_status')) {
                $table->string('gateway_status', 50)->nullable()->after('gateway_customer_id');
            }

            if (! Schema::hasColumn('saas_assinaturas', 'financial_status')) {
                $table->string('financial_status', 30)->default('adimplente')->after('gateway_status');
            }

            if (! Schema::hasColumn('saas_assinaturas', 'renews_at')) {
                $table->timestamp('renews_at')->nullable()->after('ends_at');
            }

            if (! Schema::hasColumn('saas_assinaturas', 'canceled_at')) {
                $table->timestamp('canceled_at')->nullable()->after('renews_at');
            }
        });

        $this->ensureIndex('saas_assinaturas', 'saas_assinaturas_loja_status_idx', 'create index saas_assinaturas_loja_status_idx on saas_assinaturas (loja_id, status)');
        $this->ensureIndex('saas_assinaturas', 'saas_assinaturas_plano_idx', 'create index saas_assinaturas_plano_idx on saas_assinaturas (plano_id)');
        $this->ensureIndex('saas_assinaturas', 'saas_assinaturas_gateway_sub_idx', 'create index saas_assinaturas_gateway_sub_idx on saas_assinaturas (gateway_subscription_id)');
        $this->ensureIndex('saas_pagamentos', 'saas_pagamentos_loja_status_vencimento_idx', 'create index saas_pagamentos_loja_status_vencimento_idx on saas_pagamentos (loja_id, status, vencimento_em)');

        $this->backfillPlanData();
        $this->backfillSubscriptionSnapshots();
    }

    public function down(): void
    {
        Schema::table('saas_assinaturas', function (Blueprint $table): void {
            foreach (['grace_ends_at', 'plan_version', 'plan_snapshot', 'gateway_provider', 'gateway_subscription_id', 'gateway_customer_id', 'gateway_status', 'financial_status', 'renews_at', 'canceled_at'] as $column) {
                if (Schema::hasColumn('saas_assinaturas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('saas_usage_logs');
        Schema::dropIfExists('saas_plano_limits');
        Schema::dropIfExists('saas_plano_features');

        Schema::table('saas_planos', function (Blueprint $table): void {
            foreach (['version', 'legacy_slug', 'is_legacy'] as $column) {
                if (Schema::hasColumn('saas_planos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function ensureIndex(string $table, string $indexName, string $createSql): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $exists = DB::table('information_schema.statistics')
                ->whereRaw('table_schema = database()')
                ->where('table_name', $table)
                ->where('index_name', $indexName)
                ->exists();

            if (! $exists) {
                DB::statement($createSql);
            }

            return;
        }

        try {
            DB::statement($createSql);
        } catch (\Throwable) {
            // Index already exists or not supported in current driver.
        }
    }

    private function backfillPlanData(): void
    {
        if (! Schema::hasTable('saas_planos')) {
            return;
        }

        $plans = DB::table('saas_planos')->select(['id', 'slug', 'limite_produtos', 'limite_funcionarios', 'recursos_premium'])->get();

        foreach ($plans as $plan) {
            DB::table('saas_planos')->where('id', $plan->id)->update([
                'legacy_slug' => $plan->slug,
                'version' => DB::raw('coalesce(version, 1)'),
            ]);

            $this->upsertPlanLimit((int) $plan->id, 'max_produtos', $plan->limite_produtos !== null ? (int) $plan->limite_produtos : null);
            $this->upsertPlanLimit((int) $plan->id, 'max_usuarios', $plan->limite_funcionarios !== null ? (int) $plan->limite_funcionarios : null);

            $this->upsertPlanFeature((int) $plan->id, 'modulo_produtos', true);
            $this->upsertPlanFeature((int) $plan->id, 'modulo_pedidos', true);
            $this->upsertPlanFeature((int) $plan->id, 'modulo_crm', true);

            $resources = [];
            if (! empty($plan->recursos_premium)) {
                $decoded = json_decode((string) $plan->recursos_premium, true);
                if (is_array($decoded)) {
                    $resources = $decoded;
                }
            }

            $map = [
                'produtos_configuraveis' => 'modulo_produtos_avancado',
                'produtos_tecnicos' => 'modulo_producao',
                'financeiro_pro' => 'modulo_financeiro',
                'financeiro_premium' => 'modulo_financeiro',
                'bi_basico' => 'modulo_bi',
                'bi_avancado' => 'modulo_bi',
                'api_externa' => 'modulo_api',
                'kanban' => 'modulo_kanban',
            ];

            foreach ($map as $legacyKey => $featureKey) {
                if (array_key_exists($legacyKey, $resources)) {
                    $this->upsertPlanFeature((int) $plan->id, $featureKey, (bool) $resources[$legacyKey]);
                }
            }
        }
    }

    private function backfillSubscriptionSnapshots(): void
    {
        if (! Schema::hasTable('saas_assinaturas')) {
            return;
        }

        $assinaturas = DB::table('saas_assinaturas')
            ->leftJoin('saas_planos', 'saas_planos.id', '=', 'saas_assinaturas.plano_id')
            ->select([
                'saas_assinaturas.id as id',
                'saas_assinaturas.plano_id as plano_id',
                'saas_planos.version as plano_version',
                'saas_planos.nome as plano_nome',
                'saas_planos.slug as plano_slug',
                'saas_planos.preco_mensal as plano_preco_mensal',
            ])
            ->get();

        foreach ($assinaturas as $assinatura) {
            if (! $assinatura->plano_id) {
                continue;
            }

            DB::table('saas_assinaturas')
                ->where('id', $assinatura->id)
                ->update([
                    'plan_version' => $assinatura->plano_version ?? 1,
                    'plan_snapshot' => json_encode([
                        'plano_id' => (int) $assinatura->plano_id,
                        'nome' => $assinatura->plano_nome,
                        'slug' => $assinatura->plano_slug,
                        'preco_mensal' => $assinatura->plano_preco_mensal,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
        }
    }

    private function upsertPlanFeature(int $planId, string $featureKey, bool $enabled): void
    {
        DB::table('saas_plano_features')->updateOrInsert(
            ['plano_id' => $planId, 'feature_key' => $featureKey],
            [
                'enabled' => $enabled,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function upsertPlanLimit(int $planId, string $limitKey, ?int $value): void
    {
        DB::table('saas_plano_limits')->updateOrInsert(
            ['plano_id' => $planId, 'limit_key' => $limitKey],
            [
                'limit_value' => $value,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
};
