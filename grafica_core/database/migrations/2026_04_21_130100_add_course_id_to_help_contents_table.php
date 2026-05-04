<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('help_contents', function (Blueprint $table) {
            $table->foreignId('course_id')
                ->nullable()
                ->after('id')
                ->constrained('academy_courses')
                ->nullOnDelete();

            $table->index(['course_id', 'ordem']);
            $table->index(['publicado', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::table('help_contents', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'ordem']);
            $table->dropIndex(['publicado', 'tipo']);
            $table->dropConstrainedForeignId('course_id');
        });
    }
};
