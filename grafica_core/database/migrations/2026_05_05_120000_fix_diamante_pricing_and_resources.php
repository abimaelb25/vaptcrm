<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix Diamante plan: correct price to R$399 and update recursos_premium
 * to replace the generic 'acesso_full' with explicit 'whatsapp_api_oficial_beta'.
 *
 * Production instruction:
 *   Run: php artisan migrate
 *   This migration is idempotent — safe to run even if Diamante already has
 *   the correct values, since it does a controlled JSON merge.
 */
return new class extends Migration
{
    public function up(): void
    {
        $plano = DB::table('saas_planos')->where('slug', 'diamante')->first();

        if (! $plano) {
            return;
        }

        // ── 1. Fix pricing columns ────────────────────────────────────────
        $priceUpdate = [
            'preco_mensal' => 399.00,
        ];

        if (Schema::hasColumn('saas_planos', 'price_monthly')) {
            $priceUpdate['price_monthly'] = 399.00;
        }

        if (Schema::hasColumn('saas_planos', 'price_yearly')) {
            $priceUpdate['price_yearly'] = 3990.00;
        }

        // ── 2. Fix recursos_premium ───────────────────────────────────────
        // Decode current JSON (may be old row with 'acesso_full' or newer with
        // 'whatsapp_api_oficial'). Normalize to the canonical key set.
        $current = [];
        if (! empty($plano->recursos_premium)) {
            $decoded = json_decode((string) $plano->recursos_premium, true);
            if (is_array($decoded)) {
                $current = $decoded;
            }
        }

        // Remove legacy/generic keys
        unset($current['acesso_full'], $current['whatsapp_api_oficial']);

        // Ensure canonical Ouro set + Diamante exclusive feature
        $canonical = [
            'central_pedidos'          => true,
            'gestao_clientes'          => true,
            'bi_avancado'              => true,
            'suporte_prioritario'      => true,
            'multiempresa_opcional'    => true,
            'whatsapp_api_oficial_beta' => true,
        ];

        // Merge: canonical values take precedence
        $merged = array_merge($current, $canonical);

        $priceUpdate['recursos_premium'] = json_encode($merged, JSON_UNESCAPED_UNICODE);

        DB::table('saas_planos')
            ->where('slug', 'diamante')
            ->update($priceUpdate);
    }

    public function down(): void
    {
        // Revert pricing only — recursos_premium old state is unknown, skip.
        $revert = ['preco_mensal' => 399.90];

        if (Schema::hasColumn('saas_planos', 'price_monthly')) {
            $revert['price_monthly'] = null;
        }

        if (Schema::hasColumn('saas_planos', 'price_yearly')) {
            $revert['price_yearly'] = null;
        }

        DB::table('saas_planos')->where('slug', 'diamante')->update($revert);
    }
};
