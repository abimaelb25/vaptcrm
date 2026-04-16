<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->boolean('exige_arte')->default(false)->after('prazo_estimado');
            $table->decimal('preco_arte', 10, 2)->default(0)->after('exige_arte');
        });

        Schema::table('itens_pedido', function (Blueprint $table) {
            $table->string('caminho_arte')->nullable()->after('valor_total');
            $table->boolean('servico_arte_incluso')->default(false)->after('caminho_arte');
        });
    }

    public function down(): void
    {
        Schema::table('itens_pedido', function (Blueprint $table) {
            $table->dropColumn(['caminho_arte', 'servico_arte_incluso']);
        });

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn(['exige_arte', 'preco_arte']);
        });
    }
};
