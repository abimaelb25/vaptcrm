<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_store_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id')->unique();
            $table->unsignedBigInteger('default_account_id')->nullable();
            $table->string('catalog_link', 500)->nullable();
            $table->json('automations')->nullable();
            $table->json('event_mappings')->nullable();
            $table->string('last_test_phone', 30)->nullable();
            $table->timestamps();

            $table->foreign('loja_id')->references('id')->on('lojas')->onDelete('cascade');
            $table->foreign('default_account_id')->references('id')->on('whatsapp_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_store_settings');
    }
};