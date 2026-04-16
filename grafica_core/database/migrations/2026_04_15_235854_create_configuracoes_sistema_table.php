<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 23:58
*/

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuracoes_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();
            $table->text('valor')->nullable();
            $table->timestamps();
        });

        // Seed inicial com valores padrão do VaptCRM
        \DB::table('configuracoes_sistema')->insert([
            ['chave' => 'plataforma_nome', 'valor' => 'VaptCRM', 'created_at' => now()],
            ['chave' => 'plataforma_logo', 'valor' => null, 'created_at' => now()],
            ['chave' => 'plataforma_logo_dark', 'valor' => null, 'created_at' => now()],
            ['chave' => 'plataforma_favicon', 'valor' => null, 'created_at' => now()],
            ['chave' => 'plataforma_cor_primaria', 'valor' => '#FF7A00', 'created_at' => now()],
            ['chave' => 'plataforma_cor_secundaria', 'valor' => '#1E293B', 'created_at' => now()],
            ['chave' => 'plataforma_email_suporte', 'valor' => 'suporte@vaptcrm.com.br', 'created_at' => now()],
            ['chave' => 'plataforma_whatsapp_suporte', 'valor' => '5575999279354', 'created_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracoes_sistema');
    }
};
