<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-13 13:20 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimentacoes_financeiras', function (Blueprint $table): void {
            $table->foreignId('caixa_id')->nullable()->after('pagamento_id')->constrained('caixas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimentacoes_financeiras', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('caixa_id');
        });
    }
};
