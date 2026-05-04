<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Loja;
use App\Models\Produto;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\Plano;
use App\Models\SaaS\PlanoFeature;
use App\Models\SaaS\PlanoLimit;
use App\Models\Usuario;
use App\Services\SaaS\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SaaSPlanGovernanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_limit_blocks_new_product_when_limit_is_reached(): void
    {
        [$user, $loja] = $this->createTenantWithPlan(
            features: ['modulo_produtos' => true],
            limits: ['max_produtos' => 1]
        );

        Produto::create([
            'loja_id' => $loja->id,
            'nome' => 'Produto Base',
            'slug' => 'produto-base',
            'categoria' => 'geral',
            'ativo' => true,
        ]);

        $this->actingAs($user);

        /** @var PlanService $service */
        $service = app(PlanService::class);

        $this->assertFalse($service->canConsumeLimit('max_produtos', 1, $loja->id));
    }

    public function test_feature_middleware_blocks_access_for_disabled_module(): void
    {
        [$user] = $this->createTenantWithPlan(
            features: ['modulo_financeiro' => false],
            limits: []
        );

        Route::middleware(['web', 'auth', 'assinatura', 'check_plan_feature:modulo_financeiro'])
            ->get('/_test/finance-module', static fn () => response('ok'));

        $response = $this->actingAs($user)->get('/_test/finance-module');

        $response->assertStatus(302);
    }

    public function test_tenant_isolation_keeps_queries_scoped_by_loja(): void
    {
        [$userA, $lojaA] = $this->createTenantWithPlan(
            features: ['modulo_produtos' => true],
            limits: ['max_produtos' => null]
        );

        [$userB, $lojaB] = $this->createTenantWithPlan(
            features: ['modulo_produtos' => true],
            limits: ['max_produtos' => null]
        );

        Produto::create([
            'loja_id' => $lojaA->id,
            'nome' => 'Produto Loja A',
            'slug' => 'produto-loja-a',
            'categoria' => 'geral',
            'ativo' => true,
        ]);

        Produto::create([
            'loja_id' => $lojaB->id,
            'nome' => 'Produto Loja B',
            'slug' => 'produto-loja-b',
            'categoria' => 'geral',
            'ativo' => true,
        ]);

        $this->actingAs($userA);
        /** @var PlanService $service */
        $service = app(PlanService::class);
        $this->assertSame(1, $service->currentUsage('max_produtos', $lojaA->id));

        $this->actingAs($userB);
        $this->assertSame(1, $service->currentUsage('max_produtos', $lojaB->id));
    }

    public function test_plan_upgrade_changes_limit_without_data_loss(): void
    {
        [$user, $loja, $assinatura] = $this->createTenantWithPlan(
            features: ['modulo_produtos' => true],
            limits: ['max_produtos' => 2]
        );

        $planoNovo = Plano::create([
            'nome' => 'Plano Escala',
            'slug' => 'plano-escala',
            'preco_mensal' => 399.90,
            'version' => 2,
            'ativo' => true,
        ]);

        PlanoLimit::create([
            'plano_id' => $planoNovo->id,
            'limit_key' => 'max_produtos',
            'limit_value' => 25,
        ]);

        $assinatura->update([
            'plano_id' => $planoNovo->id,
            'plan_version' => 2,
            'plan_snapshot' => [
                'plano_id' => $planoNovo->id,
                'nome' => $planoNovo->nome,
                'slug' => $planoNovo->slug,
                'version' => 2,
            ],
        ]);

        $this->actingAs($user);

        /** @var PlanService $service */
        $service = app(PlanService::class);

        $this->assertSame(25, $service->getLimit('max_produtos', $loja->id));
    }

    public function test_subscription_lifecycle_respects_expiration_and_grace_period(): void
    {
        [, , $assinatura] = $this->createTenantWithPlan(
            features: ['modulo_produtos' => true],
            limits: []
        );

        $assinatura->update([
            'status' => Assinatura::STATUS_ACTIVE,
            'ends_at' => now()->subDay(),
            'grace_ends_at' => null,
        ]);

        $assinatura->refresh();
        $this->assertTrue($assinatura->expirada());

        $assinatura->update([
            'grace_ends_at' => now()->addDays(2),
        ]);

        $assinatura->refresh();
        $this->assertTrue($assinatura->ativa());
        $this->assertFalse($assinatura->expirada());
    }

    /**
     * @return array{0: Usuario, 1: Loja, 2: Assinatura}
     */
    private function createTenantWithPlan(array $features, array $limits): array
    {
        $suffix = (string) now()->timestamp . random_int(1000, 9999);

        $plano = Plano::create([
            'nome' => 'Plano Teste ' . $suffix,
            'slug' => 'plano-teste-' . $suffix,
            'preco_mensal' => 99.90,
            'version' => 1,
            'ativo' => true,
        ]);

        foreach ($features as $featureKey => $enabled) {
            PlanoFeature::create([
                'plano_id' => $plano->id,
                'feature_key' => (string) $featureKey,
                'enabled' => (bool) $enabled,
            ]);
        }

        foreach ($limits as $limitKey => $value) {
            PlanoLimit::create([
                'plano_id' => $plano->id,
                'limit_key' => (string) $limitKey,
                'limit_value' => $value,
            ]);
        }

        $loja = Loja::create([
            'nome_fantasia' => 'Loja Teste ' . $suffix,
            'slug' => 'loja-teste-' . $suffix,
            'responsavel_nome' => 'Responsavel Teste',
            'responsavel_email' => 'resp-' . $suffix . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
            'storage_limit_mb' => 1024,
            'storage_used_bytes' => 0,
        ]);

        $user = Usuario::create([
            'loja_id' => $loja->id,
            'nome' => 'Usuario Teste ' . $suffix,
            'email' => 'user-' . $suffix . '@example.com',
            'senha' => Hash::make('secret123'),
            'perfil' => 'administrador',
            'ativo' => true,
        ]);

        $assinatura = Assinatura::create([
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
            'renews_at' => now()->addMonth(),
        ]);

        return [$user, $loja, $assinatura];
    }
}
