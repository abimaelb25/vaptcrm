<?php

declare(strict_types=1);

namespace Database\Seeders;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:22
*/

use App\Models\SaaS\Plano;
use App\Models\SaaS\PlanoFeature;
use App\Models\SaaS\PlanoLimit;
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
                'preco_mensal' => 49.00,
                'price_monthly' => 49.00,
                'price_yearly' => 490.00,
                'trial_days' => 14,
                'limite_produtos' => 20,
                'limite_funcionarios' => 2,
                'version' => 1,
                'recursos_premium' => [
                    'central_pedidos' => true,
                    'gestao_clientes' => true,
                    'suporte_basico' => true,
                ],
                'features' => [
                    'modulo_produtos' => true,
                    'modulo_pedidos' => true,
                    'modulo_crm' => true,
                    'modulo_financeiro' => false,
                    'modulo_estoque' => false,
                    'modulo_producao' => false,
                    'modulo_kanban' => false,
                    'modulo_api' => false,
                    'multiusuario_avancado' => false,
                    'suporte_prioritario' => false,
                ],
                'limits' => [
                    'max_produtos' => 20,
                    'max_usuarios' => 2,
                    'max_pedidos_mes' => 100,
                    'max_ops_simultaneas' => 50,
                    'max_producao_ativa' => 50,
                    'max_storage_mb' => 1024,
                ],
            ],
            [
                'nome' => 'Prata',
                'slug' => 'prata',
                'preco_mensal' => 149.00,
                'price_monthly' => 149.00,
                'price_yearly' => 1490.00,
                'trial_days' => 14,
                'limite_produtos' => 100,
                'limite_funcionarios' => 5,
                'version' => 1,
                'recursos_premium' => [
                    'central_pedidos' => true,
                    'gestao_clientes' => true,
                    'bi_basico' => true,
                    'suporte_prioritario' => true,
                ],
                'features' => [
                    'modulo_produtos' => true,
                    'modulo_pedidos' => true,
                    'modulo_crm' => true,
                    'modulo_financeiro' => true,
                    'modulo_estoque' => true,
                    'modulo_producao' => true,
                    'modulo_kanban' => true,
                    'modulo_api' => true,
                    'multiusuario_avancado' => true,
                    'suporte_prioritario' => true,
                ],
                'limits' => [
                    'max_produtos' => 100,
                    'max_usuarios' => 5,
                    'max_pedidos_mes' => 500,
                    'max_ops_simultaneas' => 300,
                    'max_producao_ativa' => 300,
                    'max_storage_mb' => 5120,
                ],
            ],
            [
                'nome' => 'Ouro',
                'slug' => 'ouro',
                'preco_mensal' => 299.00,
                'price_monthly' => 299.00,
                'price_yearly' => 2990.00,
                'trial_days' => 14,
                'limite_produtos' => null,
                'limite_funcionarios' => null,
                'version' => 1,
                'recursos_premium' => [
                    'central_pedidos' => true,
                    'gestao_clientes' => true,
                    'bi_avancado' => true,
                    'suporte_prioritario' => true,
                    'multiempresa_opcional' => true,
                ],
                'features' => [
                    'modulo_produtos' => true,
                    'modulo_pedidos' => true,
                    'modulo_crm' => true,
                    'modulo_financeiro' => true,
                    'modulo_financeiro_premium' => true,
                    'modulo_estoque' => true,
                    'modulo_producao' => true,
                    'modulo_kanban' => true,
                    'modulo_api' => true,
                    'multiusuario_avancado' => true,
                    'multiempresa' => true,
                    'relatorios_avancados' => true,
                ],
                'limits' => [
                    'max_produtos' => null,
                    'max_usuarios' => null,
                    'max_pedidos_mes' => null,
                    'max_ops_simultaneas' => null,
                    'max_producao_ativa' => null,
                    'max_storage_mb' => 20480,
                ],
            ],
            [
                'nome' => 'Enterprise',
                'slug' => 'enterprise',
                'preco_mensal' => 0.00,
                'price_monthly' => 0.00,
                'price_yearly' => 0.00,
                'trial_days' => 7,
                'limite_produtos' => null,
                'limite_funcionarios' => null,
                'version' => 1,
                'recursos_premium' => [
                    'central_pedidos' => true,
                    'gestao_clientes' => true,
                    'bi_avancado' => true,
                    'suporte_prioritario' => true,
                    'acesso_full' => true,
                    'sla_dedicado' => true,
                    'onboarding_dedicado' => true,
                    'custom_features' => true,
                ],
                'features' => [
                    'modulo_produtos' => true,
                    'modulo_pedidos' => true,
                    'modulo_crm' => true,
                    'modulo_financeiro' => true,
                    'modulo_financeiro_premium' => true,
                    'modulo_estoque' => true,
                    'modulo_producao' => true,
                    'modulo_kanban' => true,
                    'modulo_api' => true,
                    'multiusuario_avancado' => true,
                    'multiempresa' => true,
                    'relatorios_avancados' => true,
                    'custom_sla' => true,
                ],
                'limits' => [
                    'max_produtos' => null,
                    'max_usuarios' => null,
                    'max_pedidos_mes' => null,
                    'max_ops_simultaneas' => null,
                    'max_producao_ativa' => null,
                    'max_storage_mb' => null,
                ],
            ],
        ];

        foreach ($planos as $planoData) {
            $features = $planoData['features'];
            $limits = $planoData['limits'];

            unset($planoData['features'], $planoData['limits']);

            Plano::updateOrCreate(
                ['slug' => $planoData['slug']],
                $planoData
            );

            $plano = Plano::where('slug', $planoData['slug'])->firstOrFail();

            foreach ($features as $featureKey => $enabled) {
                PlanoFeature::updateOrCreate(
                    ['plano_id' => $plano->id, 'feature_key' => $featureKey],
                    ['enabled' => (bool) $enabled]
                );
            }

            foreach ($limits as $limitKey => $value) {
                PlanoLimit::updateOrCreate(
                    ['plano_id' => $plano->id, 'limit_key' => $limitKey],
                    ['limit_value' => $value]
                );
            }
        }
    }
}
