<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 03:55
| Nota: Migration de segurança. A tabela saas_assinaturas está na lista da migration principal
|       add_loja_id_to_operational_tables, mas esta migration garante a adição idempotente
|       caso a ordem de execução mude. O hasColumn() previne erros de duplicação.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('saas_assinaturas', function (Blueprint $table) {
            if (!Schema::hasColumn('saas_assinaturas', 'loja_id')) {
                $table->foreignId('loja_id')->nullable()->after('id')->constrained('lojas')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saas_assinaturas', function (Blueprint $table) {
            $table->dropForeign(['loja_id']);
            $table->dropColumn('loja_id');
        });
    }
};
