<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academy_courses')) {
            return;
        }

        Schema::table('academy_courses', function (Blueprint $table): void {
            if (! Schema::hasColumn('academy_courses', 'track_id')) {
                $table->foreignId('track_id')->nullable()->after('id')->constrained('academy_tracks')->nullOnDelete();
            }
        });

        if (Schema::hasTable('academy_tracks') && DB::table('academy_tracks')->count() === 0) {
            DB::table('academy_tracks')->insert([
                'titulo' => 'Biblioteca Geral',
                'slug' => Str::slug('biblioteca-geral'),
                'descricao' => 'Trilha inicial criada para preservar os modulos legados da VaptAcademy.',
                'ordem' => 0,
                'destaque' => false,
                'publicado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $trackId = DB::table('academy_tracks')->orderBy('id')->value('id');

        if ($trackId) {
            DB::table('academy_courses')
                ->whereNull('track_id')
                ->update([
                    'track_id' => $trackId,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('academy_courses')) {
            return;
        }

        Schema::table('academy_courses', function (Blueprint $table): void {
            if (Schema::hasColumn('academy_courses', 'track_id')) {
                $table->dropConstrainedForeignId('track_id');
            }
        });
    }
};
