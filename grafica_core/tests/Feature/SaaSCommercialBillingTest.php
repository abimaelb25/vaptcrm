<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Loja;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\Plano;
use App\Models\SaaS\PagamentoSaaS;
use App\Models\Usuario;
use App\Services\SaaS\CommercialSubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class SaaSCommercialBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_upgrade_imediato_gera_prorata_e_atualiza_plano(): void
    {
        [$loja, $assinatura] = $this->createStoreWithSubscription(79.0, 279.0);

        $planoOuro = Plano::query()->create([
            'nome' => 'Ouro Comercial',
            'slug' => 'ouro-comercial-' . uniqid(),
            'preco_mensal' => 279.0,
            'price_monthly' => 279.0,
            'price_yearly' => 2790.0,
            'trial_days' => 14,
            'version' => 1,
            'ativo' => true,
        ]);

        $service = app(CommercialSubscriptionService::class);
        $result = $service->changePlan($loja, $planoOuro, Assinatura::BILLING_MONTHLY, false);

        $assinatura->refresh();

        $this->assertSame($planoOuro->id, $assinatura->plano_id);
        $this->assertSame(Assinatura::BILLING_MONTHLY, $assinatura->billing_cycle);
        $this->assertGreaterThan(0, $result['prorata']);
        $this->assertDatabaseHas('saas_pagamentos', [
            'assinatura_id' => $assinatura->id,
            'status' => 'pendente',
        ]);
    }

    public function test_webhook_stripe_payment_failed_marca_past_due(): void
    {
        [, $assinatura] = $this->createStoreWithSubscription(99.0, 99.0);
        $assinatura->update([
            'gateway_subscription_id' => 'sub_test_123',
            'status' => Assinatura::STATUS_ACTIVE,
            'financial_status' => 'adimplente',
        ]);

        $response = $this->postJson('/api/webhooks/saas/stripe', [
            'type' => 'invoice.payment_failed',
            'data' => [
                'object' => [
                    'subscription' => 'sub_test_123',
                ],
            ],
        ]);

        $response->assertOk();

        $assinatura->refresh();
        $this->assertSame(Assinatura::STATUS_PAST_DUE, $assinatura->status);
        $this->assertSame('inadimplente', $assinatura->financial_status);
    }

    public function test_webhook_stripe_invoice_paid_reativa_e_registra_pagamento(): void
    {
        [, $assinatura] = $this->createStoreWithSubscription(99.0, 99.0);
        $assinatura->update([
            'gateway_subscription_id' => 'sub_paid_123',
            'status' => Assinatura::STATUS_PAST_DUE,
            'financial_status' => 'inadimplente',
        ]);

        $response = $this->postJson('/api/webhooks/saas/stripe', [
            'type' => 'invoice.paid',
            'data' => [
                'object' => [
                    'id' => 'in_test_001',
                    'subscription' => 'sub_paid_123',
                    'amount_paid' => 14900,
                    'currency' => 'brl',
                    'period_start' => now()->subDays(2)->timestamp,
                    'period_end' => now()->addMonth()->timestamp,
                ],
            ],
        ]);

        $response->assertOk();

        $assinatura->refresh();
        $this->assertSame(Assinatura::STATUS_ACTIVE, $assinatura->status);
        $this->assertSame('adimplente', $assinatura->financial_status);

        $pagamento = PagamentoSaaS::query()->where('assinatura_id', $assinatura->id)->latest('id')->first();
        $this->assertNotNull($pagamento);
        $this->assertSame('pago', $pagamento->status);
    }

    public function test_modo_read_only_bloqueia_post_mas_permite_get(): void
    {
        [$loja, $assinatura] = $this->createStoreWithSubscription(99.0, 99.0);

        $usuario = Usuario::query()->create([
            'loja_id' => $loja->id,
            'nome' => 'Tester',
            'email' => 'tester-' . uniqid() . '@example.com',
            'senha' => '12345678',
            'perfil' => 'admin',
            'cargo' => 'Owner',
            'ativo' => true,
        ]);

        $assinatura->update([
            'status' => Assinatura::STATUS_PAST_DUE,
            'financial_status' => 'inadimplente',
        ]);

        Route::middleware(['web', 'auth', 'assinatura'])->get('/_saas_lock_get', fn () => response()->json(['ok' => true]));
        Route::middleware(['web', 'auth', 'assinatura'])->post('/_saas_lock_post', fn () => response()->json(['ok' => true]));

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->actingAs($usuario, 'web')
            ->getJson('/_saas_lock_get')
            ->assertOk();

        $this->actingAs($usuario, 'web')
            ->postJson('/_saas_lock_post', ['x' => 1])
            ->assertStatus(402)
            ->assertJsonPath('error_code', 'READ_ONLY_LOCKED');
    }

    /**
     * @return array{Loja, Assinatura}
     */
    private function createStoreWithSubscription(float $monthlyPrice, float $yearlyPrice): array
    {
        $plano = Plano::query()->create([
            'nome' => 'Plano Base ' . uniqid(),
            'slug' => 'plano-base-' . uniqid(),
            'preco_mensal' => $monthlyPrice,
            'price_monthly' => $monthlyPrice,
            'price_yearly' => $yearlyPrice,
            'trial_days' => 14,
            'version' => 1,
            'ativo' => true,
        ]);

        $loja = Loja::query()->create([
            'nome_fantasia' => 'Loja Comercial ' . uniqid(),
            'slug' => 'loja-comercial-' . uniqid(),
            'responsavel_nome' => 'Owner',
            'responsavel_email' => 'owner-' . uniqid() . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
        ]);

        $assinatura = Assinatura::query()->where('loja_id', $loja->id)->latest('id')->first();
        if (! $assinatura) {
            $assinatura = Assinatura::query()->create([
                'loja_id' => $loja->id,
                'plano_id' => $plano->id,
                'status' => Assinatura::STATUS_ACTIVE,
                'billing_cycle' => Assinatura::BILLING_MONTHLY,
                'renews_at' => now()->addMonth(),
                'next_billing_at' => now()->addMonth(),
                'plan_version' => 1,
                'plan_snapshot' => ['nome' => $plano->nome],
            ]);
        }

        $assinatura->update([
            'status' => Assinatura::STATUS_ACTIVE,
            'billing_cycle' => Assinatura::BILLING_MONTHLY,
            'renews_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
            'financial_status' => 'adimplente',
        ]);

        return [$loja, $assinatura->fresh()];
    }
}
