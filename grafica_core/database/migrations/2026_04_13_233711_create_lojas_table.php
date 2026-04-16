<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 13/04/2026 23:42
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
        Schema::create('lojas', function (Blueprint $table) {
            $table->id();
            $table->string('nome_fantasia');
            $table->string('slug')->unique();
            $table->string('responsavel_nome');
            $table->string('responsavel_email');
            $table->string('responsavel_whatsapp')->nullable();
            
            $table->enum('status', ['trial', 'ativa', 'suspensa', 'cancelada'])->default('trial');
            
            $table->foreignId('plano_id')->nullable()->constrained('saas_planos')->nullOnDelete();
            
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('assinatura_ativa_ate')->nullable();
            
            $table->integer('storage_limit_mb')->nullable();
            $table->bigInteger('storage_used_bytes')->default(0);
            
            $table->string('subdominio')->nullable()->unique();
            $table->string('dominio_personalizado')->nullable()->unique();
            
            $table->text('observacoes_internas')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lojas');
    }
};
