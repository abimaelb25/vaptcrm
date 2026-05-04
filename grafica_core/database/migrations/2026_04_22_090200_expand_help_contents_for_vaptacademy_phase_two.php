<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('help_contents')) {
            return;
        }

        Schema::table('help_contents', function (Blueprint $table): void {
            if (! Schema::hasColumn('help_contents', 'conteudo_texto')) {
                $table->longText('conteudo_texto')->nullable()->after('descricao');
            }
            if (! Schema::hasColumn('help_contents', 'material_apoio_titulo')) {
                $table->string('material_apoio_titulo')->nullable()->after('thumbnail');
            }
            if (! Schema::hasColumn('help_contents', 'material_apoio_url')) {
                $table->string('material_apoio_url')->nullable()->after('material_apoio_titulo');
            }
            if (! Schema::hasColumn('help_contents', 'quiz_payload')) {
                $table->json('quiz_payload')->nullable()->after('material_apoio_url');
            }
            if (! Schema::hasColumn('help_contents', 'visivel_para_planos')) {
                $table->json('visivel_para_planos')->nullable()->after('required_plan');
            }
            if (! Schema::hasColumn('help_contents', 'obrigatoriedade')) {
                $table->string('obrigatoriedade', 30)->default('livre')->after('visivel_para_planos');
            }
        });

        DB::table('help_contents')
            ->whereNotNull('required_plan')
            ->whereNull('visivel_para_planos')
            ->orderBy('id')
            ->get(['id', 'required_plan'])
            ->each(function (object $content): void {
                $plans = collect(explode(',', (string) $content->required_plan))
                    ->map(fn (string $item): string => trim($item))
                    ->filter()
                    ->values()
                    ->all();

                DB::table('help_contents')
                    ->where('id', $content->id)
                    ->update([
                        'visivel_para_planos' => ! empty($plans) ? json_encode($plans, JSON_UNESCAPED_UNICODE) : null,
                        'obrigatoriedade' => ! empty($plans) ? 'obrigatorio' : 'livre',
                    ]);
            });

        DB::table('help_contents')
            ->where('tipo', 'treinamento')
            ->where('obrigatoriedade', 'livre')
            ->update(['obrigatoriedade' => 'obrigatorio']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('help_contents')) {
            return;
        }

        Schema::table('help_contents', function (Blueprint $table): void {
            foreach (['conteudo_texto', 'material_apoio_titulo', 'material_apoio_url', 'quiz_payload', 'visivel_para_planos', 'obrigatoriedade'] as $column) {
                if (Schema::hasColumn('help_contents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
