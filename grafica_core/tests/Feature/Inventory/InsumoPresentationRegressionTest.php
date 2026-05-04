<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\Insumo;
use App\Models\Loja;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\Plano;
use App\Models\SaaS\PlanoFeature;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class InsumoPresentationRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_shows_correct_cost_labels_for_tinta(): void
    {
        [$user, $loja] = $this->createTenantWithInventoryFeature();

        $insumo = Insumo::create([
            'loja_id' => $loja->id,
            'nome' => 'Tinta Pigmentada TJet 4L',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'frasco',
            'quantidade_por_compra' => 1000,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
            'ativo' => true,
            'custo_medio' => 0.15,
            'custo_unitario_consumo' => 0.15,
        ]);

        $response = $this->actingAs($user)->get(route('admin.inventory.insumos.edit', $insumo));

        $response->assertOk();
        $response->assertSee('Custo por unidade de estoque (ml)', false);
        $response->assertSee('Custo por unidade de compra (frasco): R$ 150,00', false);
        $response->assertDontSee('Custo por frasco: R$ 0,15', false);
    }

    public function test_index_page_shows_base_cost_with_purchase_equivalent_label(): void
    {
        [$user, $loja] = $this->createTenantWithInventoryFeature();

        Insumo::create([
            'loja_id' => $loja->id,
            'nome' => 'Tinta Pigmentada TJet 4L',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'frasco',
            'quantidade_por_compra' => 1000,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
            'ativo' => true,
            'custo_medio' => 0.15,
            'custo_unitario_consumo' => 0.15,
        ]);

        $response = $this->actingAs($user)->get(route('admin.inventory.insumos.index'));

        $response->assertOk();
        $response->assertSee('R$ 0,15 / ml', false);
        $response->assertSee('Equivalente a R$ 150,00 / frasco', false);
    }

    private function createTenantWithInventoryFeature(): array
    {
        $suffix = (string) now()->timestamp . random_int(1000, 9999);

        $plano = Plano::create([
            'nome' => 'Plano Estoque ' . $suffix,
            'slug' => 'plano-estoque-' . $suffix,
            'preco_mensal' => 99.90,
            'version' => 1,
            'ativo' => true,
        ]);

        PlanoFeature::create([
            'plano_id' => $plano->id,
            'feature_key' => 'modulo_estoque',
            'enabled' => true,
        ]);

        $loja = Loja::create([
            'nome_fantasia' => 'Loja Estoque ' . $suffix,
            'slug' => 'loja-estoque-' . $suffix,
            'responsavel_nome' => 'Responsavel Teste',
            'responsavel_email' => 'resp-estoque-' . $suffix . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
            'storage_limit_mb' => 1024,
            'storage_used_bytes' => 0,
        ]);

        $user = Usuario::create([
            'loja_id' => $loja->id,
            'nome' => 'Usuario Estoque ' . $suffix,
            'email' => 'user-estoque-' . $suffix . '@example.com',
            'senha' => Hash::make('secret123'),
            'perfil' => 'administrador',
            'ativo' => true,
        ]);

        Assinatura::create([
            'loja_id' => $loja->id,
            'plano_id' => $plano->id,
            'status' => Assinatura::STATUS_ACTIVE,
            'plan_version' => 1,
            'plan_snapshot' => [
                'plano_id' => $plano->id,
                'nome' => $plano->nome,
                'slug' => $plano->slug,
                'version' => 1,
            ],
            'billing_cycle' => Assinatura::BILLING_MONTHLY,
            'next_billing_at' => now()->addMonth(),
        ]);

        return [$user, $loja];
    }
}