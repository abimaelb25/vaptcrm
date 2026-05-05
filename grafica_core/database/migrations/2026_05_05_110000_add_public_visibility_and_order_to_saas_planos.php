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
            if (! Schema::hasColumn('saas_planos', 'visivel_publicamente')) {
                $table->boolean('visivel_publicamente')->default(true)->after('ativo');
            }

            if (! Schema::hasColumn('saas_planos', 'ordem_exibicao')) {
                $table->unsignedSmallInteger('ordem_exibicao')->default(99)->after('visivel_publicamente');
            }
        });

        $baseOfficial = [
            'ordem_exibicao' => null,
            'visivel_publicamente' => true,
            'ativo' => true,
        ];

        if (Schema::hasColumn('saas_planos', 'is_legacy')) {
            $baseOfficial['is_legacy'] = false;
        }

        $bronze = $baseOfficial;
        $bronze['ordem_exibicao'] = 1;
        $prata = $baseOfficial;
        $prata['ordem_exibicao'] = 2;
        $ouro = $baseOfficial;
        $ouro['ordem_exibicao'] = 3;
        $diamante = $baseOfficial;
        $diamante['ordem_exibicao'] = 4;

        // Ordem e visibilidade comercial oficiais.
        DB::table('saas_planos')->where('slug', 'bronze')->update($bronze);
        DB::table('saas_planos')->where('slug', 'prata')->update($prata);
        DB::table('saas_planos')->where('slug', 'ouro')->update($ouro);
        DB::table('saas_planos')->where('slug', 'diamante')->update($diamante);

        // Enterprise permanece no histórico, mas sai da operação comercial ativa.
        $legacyUpdates = [
            'ordem_exibicao' => 999,
            'visivel_publicamente' => false,
            'ativo' => false,
        ];

        if (Schema::hasColumn('saas_planos', 'is_legacy')) {
            $legacyUpdates['is_legacy'] = true;
        }

        DB::table('saas_planos')->where('slug', 'enterprise')->update($legacyUpdates);
    }

    public function down(): void
    {
        Schema::table('saas_planos', function (Blueprint $table): void {
            if (Schema::hasColumn('saas_planos', 'ordem_exibicao')) {
                $table->dropColumn('ordem_exibicao');
            }

            if (Schema::hasColumn('saas_planos', 'visivel_publicamente')) {
                $table->dropColumn('visivel_publicamente');
            }
        });
    }
};
