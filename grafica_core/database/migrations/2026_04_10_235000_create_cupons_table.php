<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 20:41 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupons', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->enum('tipo', ['percentual', 'valor_fixo']);
            $table->decimal('valor', 15, 2);
            
            $table->dateTime('validade_inicio')->nullable();
            $table->dateTime('validade_fim')->nullable();
            
            $table->unsignedInteger('limite_uso')->nullable();
            $table->unsignedInteger('usos_atuais')->default(0);
            
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('cupom_id')->nullable()->after('responsavel_id')->constrained('cupons')->nullOnDelete();
            $table->decimal('valor_desconto_cupom', 15, 2)->default(0)->after('desconto');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cupom_id');
            $table->dropColumn('valor_desconto_cupom');
        });
        Schema::dropIfExists('cupons');
    }
};
