<?php

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
        Schema::create('help_contents', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('tipo')->default('video');
            $table->text('descricao')->nullable();
            $table->string('youtube_url');
            $table->string('thumbnail')->nullable();
            $table->integer('ordem')->default(0);
            $table->boolean('destaque')->default(false);
            $table->boolean('publicado')->default(true);
            $table->string('required_plan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_contents');
    }
};
