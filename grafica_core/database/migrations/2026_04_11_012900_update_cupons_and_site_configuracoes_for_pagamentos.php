<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-11 01:29 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_configuracoes', function (Blueprint $table) {
            if (!Schema::hasColumn('site_configuracoes', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('usuarios')->nullOnDelete();
            }
        });

        try {
            Schema::table('site_configuracoes', function (Blueprint $table) {
                $table->dropUnique('site_configuracoes_chave_unique');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('site_configuracoes', function (Blueprint $table) {
                $table->unique(['user_id', 'chave'], 'site_config_user_chave_unique');
            });
        } catch (\Throwable $e) {
        }

        Schema::table('cupons', function (Blueprint $table) {
            if (!Schema::hasColumn('cupons', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('usuarios')->nullOnDelete();
            }

            if (!Schema::hasColumn('cupons', 'valor_minimo_pedido')) {
                $table->decimal('valor_minimo_pedido', 15, 2)->nullable()->after('valor');
            }

            if (!Schema::hasColumn('cupons', 'quantidade_utilizada')) {
                $table->unsignedInteger('quantidade_utilizada')->default(0)->after('limite_uso');
            }

            if (!Schema::hasColumn('cupons', 'data_inicio')) {
                $table->dateTime('data_inicio')->nullable()->after('valor');
            }

            if (!Schema::hasColumn('cupons', 'data_fim')) {
                $table->dateTime('data_fim')->nullable()->after('data_inicio');
            }
        });

        try {
            Schema::table('cupons', function (Blueprint $table) {
                $table->dropUnique('cupons_codigo_unique');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('cupons', function (Blueprint $table) {
                $table->unique(['user_id', 'codigo'], 'cupons_user_codigo_unique');
            });
        } catch (\Throwable $e) {
        }

        DB::table('cupons')
            ->whereNull('data_inicio')
            ->whereNotNull('validade_inicio')
            ->update(['data_inicio' => DB::raw('validade_inicio')]);

        DB::table('cupons')
            ->whereNull('data_fim')
            ->whereNotNull('validade_fim')
            ->update(['data_fim' => DB::raw('validade_fim')]);

        DB::table('cupons')
            ->where('quantidade_utilizada', 0)
            ->where('usos_atuais', '>', 0)
            ->update(['quantidade_utilizada' => DB::raw('usos_atuais')]);

        if (Schema::hasTable('site_configuracoes')) {
            $existeIndiceFrete = DB::table('site_configuracoes')
                ->whereIn('chave', ['frete_fixo_ativo', 'frete_fixo_valor', 'frete_fixo_obrigatorio'])
                ->exists();

            if (! $existeIndiceFrete) {
                DB::table('site_configuracoes')->insert([
                    [
                        'user_id' => null,
                        'chave' => 'frete_fixo_ativo',
                        'valor' => '0',
                        'tipo' => 'boolean',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'user_id' => null,
                        'chave' => 'frete_fixo_valor',
                        'valor' => '0.00',
                        'tipo' => 'decimal',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'user_id' => null,
                        'chave' => 'frete_fixo_obrigatorio',
                        'valor' => '0',
                        'tipo' => 'boolean',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        }
    }

    public function down(): void
    {
        try {
            Schema::table('site_configuracoes', function (Blueprint $table) {
                $table->dropUnique('site_config_user_chave_unique');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('site_configuracoes', function (Blueprint $table) {
                $table->unique('chave');
            });
        } catch (\Throwable $e) {
        }

        Schema::table('site_configuracoes', function (Blueprint $table) {
            if (Schema::hasColumn('site_configuracoes', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        try {
            Schema::table('cupons', function (Blueprint $table) {
                $table->dropUnique('cupons_user_codigo_unique');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('cupons', function (Blueprint $table) {
                $table->unique('codigo');
            });
        } catch (\Throwable $e) {
        }

        Schema::table('cupons', function (Blueprint $table) {
            if (Schema::hasColumn('cupons', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            if (Schema::hasColumn('cupons', 'valor_minimo_pedido')) {
                $table->dropColumn('valor_minimo_pedido');
            }

            if (Schema::hasColumn('cupons', 'quantidade_utilizada')) {
                $table->dropColumn('quantidade_utilizada');
            }

            if (Schema::hasColumn('cupons', 'data_inicio')) {
                $table->dropColumn('data_inicio');
            }

            if (Schema::hasColumn('cupons', 'data_fim')) {
                $table->dropColumn('data_fim');
            }
        });

        DB::table('site_configuracoes')
            ->whereIn('chave', ['frete_fixo_ativo', 'frete_fixo_valor', 'frete_fixo_obrigatorio'])
            ->delete();
    }
};
