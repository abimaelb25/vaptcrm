<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saas_planos', function (Blueprint $table): void {
            if (! Schema::hasColumn('saas_planos', 'price_monthly')) {
                $table->decimal('price_monthly', 10, 2)->nullable()->after('preco_mensal');
            }

            if (! Schema::hasColumn('saas_planos', 'price_yearly')) {
                $table->decimal('price_yearly', 10, 2)->nullable()->after('price_monthly');
            }

            if (! Schema::hasColumn('saas_planos', 'trial_days')) {
                $table->unsignedSmallInteger('trial_days')->default(7)->after('price_yearly');
            }

            if (! Schema::hasColumn('saas_planos', 'stripe_price_yearly_id')) {
                $table->string('stripe_price_yearly_id')->nullable()->after('stripe_price_id');
            }
        });

        Schema::table('saas_assinaturas', function (Blueprint $table): void {
            if (! Schema::hasColumn('saas_assinaturas', 'billing_cycle')) {
                $table->string('billing_cycle', 20)->default('monthly')->after('status');
            }

            if (! Schema::hasColumn('saas_assinaturas', 'next_billing_at')) {
                $table->timestamp('next_billing_at')->nullable()->after('renews_at');
            }

            if (! Schema::hasColumn('saas_assinaturas', 'last_payment_at')) {
                $table->timestamp('last_payment_at')->nullable()->after('next_billing_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('saas_assinaturas', function (Blueprint $table): void {
            foreach (['billing_cycle', 'next_billing_at', 'last_payment_at'] as $column) {
                if (Schema::hasColumn('saas_assinaturas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('saas_planos', function (Blueprint $table): void {
            foreach (['price_monthly', 'price_yearly', 'trial_days', 'stripe_price_yearly_id'] as $column) {
                if (Schema::hasColumn('saas_planos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
