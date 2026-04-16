<?php

declare(strict_types=1);

namespace Database\Seeders;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:22
*/

use App\Models\SaaS\Plano;
use Illuminate\Database\Seeder;

class SaasPlanoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planos = [
            [
                'nome' => 'Bronze',
                'slug' => 'bronze',
                'preco_mensal' => 49.90,
                'limite_produtos' => 20,
                'limite_funcionarios' => 2,
                'recursos_premium' => [
                    'central_pedidos' => true,
                    'gestao_clientes' => true,
                ],
            ],
            [
                'nome' => 'Prata',
                'slug' => 'prata',
                'preco_mensal' => 99.90,
                'limite_produtos' => 100,
                'limite_funcionarios' => 5,
                'recursos_premium' => [
                    'central_pedidos' => true,
                    'gestao_clientes' => true,
                    'bi_basico' => true,
                ],
            ],
            [
                'nome' => 'Ouro',
                'slug' => 'ouro',
                'preco_mensal' => 199.90,
                'limite_produtos' => null, // Ilimitado
                'limite_funcionarios' => 10,
                'recursos_premium' => [
                    'central_pedidos' => true,
                    'gestao_clientes' => true,
                    'bi_avancado' => true,
                    'suporte_prioritario' => true,
                ],
            ],
            [
                'nome' => 'Diamante',
                'slug' => 'diamante',
                'preco_mensal' => 399.90,
                'limite_produtos' => null,
                'limite_funcionarios' => null,
                'recursos_premium' => [
                    'central_pedidos' => true,
                    'gestao_clientes' => true,
                    'bi_avancado' => true,
                    'suporte_prioritario' => true,
                    'acesso_full' => true,
                ],
            ],
        ];

        foreach ($planos as $planoData) {
            Plano::updateOrCreate(
                ['slug' => $planoData['slug']],
                $planoData
            );
        }
    }
}
