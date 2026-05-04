<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academy_tracks')) {
            return;
        }

        Schema::create('academy_tracks', function (Blueprint $table): void {
            $table->id();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('destaque')->default(false);
            $table->boolean('publicado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_tracks');
    }
};
