<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('saas_planos')) {
            return;
        }

        $updates = [
            'ativo' => false,
        ];

        if (Schema::hasColumn('saas_planos', 'is_legacy')) {
            $updates['is_legacy'] = true;
        }

        if (Schema::hasColumn('saas_planos', 'visivel_publicamente')) {
            $updates['visivel_publicamente'] = false;
        }

        if (Schema::hasColumn('saas_planos', 'ordem_exibicao')) {
            $updates['ordem_exibicao'] = 999;
        }

        DB::table('saas_planos')->where('slug', 'enterprise')->update($updates);
    }

    public function down(): void
    {
        if (! Schema::hasTable('saas_planos')) {
            return;
        }

        $updates = [
            'ativo' => true,
        ];

        if (Schema::hasColumn('saas_planos', 'is_legacy')) {
            $updates['is_legacy'] = false;
        }

        if (Schema::hasColumn('saas_planos', 'visivel_publicamente')) {
            $updates['visivel_publicamente'] = true;
        }

        DB::table('saas_planos')->where('slug', 'enterprise')->update($updates);
    }
};
