<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Jobs\WhatsApp\SendWhatsAppMessageJob;
use App\Models\Cliente;
use App\Models\Loja;
use App\Models\Pedido;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\Plano;
use App\Models\SaaS\PlanoFeature;
use App\Models\SaaS\PlanoLimit;
use App\Models\Usuario;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppConversation;
use App\Models\WhatsApp\WhatsAppConversationNote;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Models\WhatsApp\WhatsAppOptIn;
use App\Models\WhatsApp\WhatsAppStoreSetting;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class WhatsAppOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_screen_loads_and_persists_template_mapping(): void
    {
        [$user, $loja] = $this->createTenantWithWhatsAppFeature();
        $account = $this->createAccount($loja);
        $template = $this->createTemplate($loja, $account, 'pedido_criado_template');

        $this->actingAs($user);

        $page = $this->get(route('admin.whatsapp.index'));
        $page->assertOk();
        $page->assertSee('Conecte seu WhatsApp e automatize mensagens para seus clientes.');
        $page->assertSee('Onboarding guiado');

        $response = $this->post(route('admin.whatsapp.settings.update'), [
            'default_account_id' => $account->id,
            'catalog_link' => 'https://example.com/catalogo-loja',
            'send_mode' => 'manual_link',
            'automations' => [
                WhatsAppTemplate::KEY_ORDER_CREATED => '1',
            ],
            'mappings' => [
                WhatsAppTemplate::KEY_ORDER_CREATED => [
                    'template_id' => $template->id,
                    'variables' => ['cliente_nome', 'pedido_numero'],
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.whatsapp.index'));

        $settings = WhatsAppStoreSetting::where('loja_id', $loja->id)->first();

        $this->assertNotNull($settings);
        $this->assertSame($account->id, $settings->default_account_id);
        $this->assertSame('https://example.com/catalogo-loja', $settings->catalog_link);
        $this->assertSame('manual_link', $settings->send_mode);
        $this->assertTrue($settings->isAutomationEnabled(WhatsAppTemplate::KEY_ORDER_CREATED));
        $this->assertSame($template->id, $settings->mappingFor(WhatsAppTemplate::KEY_ORDER_CREATED)['template_id']);
        $this->assertSame(['cliente_nome', 'pedido_numero'], $settings->mappingFor(WhatsAppTemplate::KEY_ORDER_CREATED)['variables']);
    }

    public function test_test_send_route_queues_template_message_and_creates_optin(): void
    {
        Queue::fake();

        [$user, $loja] = $this->createTenantWithWhatsAppFeature();
        $account = $this->createAccount($loja);
        $template = $this->createTemplate($loja, $account, 'template_teste');

        $this->actingAs($user);

        $response = $this->post(route('admin.whatsapp.settings.test-send'), [
            'account_id' => $account->id,
            'template_id' => $template->id,
            'phone' => '+5511998888777',
            'event_key' => WhatsAppTemplate::KEY_ORDER_CREATED,
            'confirm_optin' => '1',
        ]);

        $response->assertRedirect(route('admin.whatsapp.index'));

        $this->assertDatabaseHas('whatsapp_optins', [
            'loja_id' => $loja->id,
            'phone' => '+5511998888777',
            'status' => 'opted_in',
            'source' => 'admin_test_send',
        ]);

        $this->assertDatabaseHas('whatsapp_messages', [
            'loja_id' => $loja->id,
            'type' => 'template',
            'status' => 'pending',
        ]);

        Queue::assertPushed(SendWhatsAppMessageJob::class);
    }

    public function test_test_send_shows_friendly_error_for_invalid_phone(): void
    {
        [$user, $loja] = $this->createTenantWithWhatsAppFeature();
        $account = $this->createAccount($loja);
        $template = $this->createTemplate($loja, $account, 'template_invalido');

        $this->actingAs($user);

        $response = $this->from(route('admin.whatsapp.index'))->post(route('admin.whatsapp.settings.test-send'), [
            'account_id' => $account->id,
            'template_id' => $template->id,
            'phone' => 'abc123',
            'event_key' => WhatsAppTemplate::KEY_ORDER_CREATED,
            'confirm_optin' => '1',
        ]);

        $response->assertRedirect(route('admin.whatsapp.index'));
        $response->assertSessionHasErrors('whatsapp_test');
    }

    public function test_manual_link_route_redirects_to_wame_with_prefilled_message(): void
    {
        [$user, $loja] = $this->createTenantWithWhatsAppFeature();
        $this->actingAs($user);

        $cliente = Cliente::create([
            'loja_id' => $loja->id,
            'nome' => 'Cliente WhatsApp',
            'telefone' => '(11) 98888-7777',
            'whatsapp' => '(11) 98888-7777',
            'status' => 'ativo',
        ]);

        $pedido = Pedido::create([
            'loja_id' => $loja->id,
            'numero' => 'PED-TESTE-WA',
            'numero_sequencial' => 1,
            'codigo_pedido' => 'L001-000001',
            'origem' => 'interno',
            'cliente_id' => $cliente->id,
            'responsavel_id' => $user->id,
            'status' => Pedido::STATUS_PRONTO,
            'subtotal' => 120,
            'total' => 120,
            'tipo_entrega' => 'retirada',
        ]);

        $response = $this->get(route('admin.whatsapp.manual-link', [
            'pedido' => $pedido->id,
            'event_key' => WhatsAppTemplate::KEY_ORDER_READY,
        ]));

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertStringStartsWith('https://wa.me/5511988887777?text=', $location);
    }

    private function createTenantWithWhatsAppFeature(): array
    {
        $suffix = (string) now()->timestamp . random_int(1000, 9999);

        $plano = Plano::create([
            'nome' => 'Plano WhatsApp ' . $suffix,
            'slug' => 'plano-whatsapp-' . $suffix,
            'preco_mensal' => 149.90,
            'version' => 1,
            'ativo' => true,
        ]);

        PlanoFeature::create([
            'plano_id' => $plano->id,
            'feature_key' => 'modulo_whatsapp',
            'enabled' => true,
        ]);

        PlanoLimit::create([
            'plano_id' => $plano->id,
            'limit_key' => 'whatsapp_accounts',
            'limit_value' => 2,
        ]);

        PlanoLimit::create([
            'plano_id' => $plano->id,
            'limit_key' => 'whatsapp_messages_month',
            'limit_value' => 100,
        ]);

        $loja = Loja::create([
            'nome_fantasia' => 'Loja WhatsApp Ops ' . $suffix,
            'slug' => 'loja-whatsapp-ops-' . $suffix,
            'responsavel_nome' => 'Responsavel Teste',
            'responsavel_email' => 'resp-ops-' . $suffix . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
            'storage_limit_mb' => 1024,
            'storage_used_bytes' => 0,
        ]);

        $user = Usuario::create([
            'loja_id' => $loja->id,
            'nome' => 'Usuario Ops ' . $suffix,
            'email' => 'user-ops-' . $suffix . '@example.com',
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
            'renews_at' => now()->addMonth(),
        ]);

        return [$user, $loja];
    }

    private function createAccount(Loja $loja): WhatsAppAccount
    {
        return WhatsAppAccount::create([
            'loja_id' => $loja->id,
            'provider' => WhatsAppAccount::PROVIDER_META_CLOUD,
            'display_name' => 'Loja Ops Oficial',
            'phone_number' => '+5511999990000',
            'phone_number_id' => 'pn_ops_' . $loja->id,
            'waba_id' => 'waba_ops_' . $loja->id,
            'access_token_encrypted' => Crypt::encryptString('token-ops-store'),
            'webhook_verify_token' => 'verify-ops-' . $loja->id,
            'status' => WhatsAppAccount::STATUS_ACTIVE,
            'is_primary' => true,
            'connected_at' => now(),
        ]);
    }

    private function createTemplate(Loja $loja, WhatsAppAccount $account, string $name): WhatsAppTemplate
    {
        return WhatsAppTemplate::create([
            'loja_id' => $loja->id,
            'whatsapp_account_id' => $account->id,
            'name' => $name,
            'language' => 'pt_BR',
            'category' => 'UTILITY',
            'status' => WhatsAppTemplate::STATUS_APPROVED,
            'components' => [
                [
                    'type' => 'BODY',
                    'text' => 'Olá {{1}}, pedido {{2}}',
                ],
            ],
        ]);
    }

    public function test_dashboard_loads_and_returns_metrics(): void
    {
        [$user, $loja] = $this->createTenantWithWhatsAppFeature();
        $this->actingAs($user);

        $response = $this->get(route('admin.whatsapp.dashboard'));
        $response->assertOk();
        $response->assertSee('Dashboard WhatsApp');
        $response->assertSee('Enviadas');
        $response->assertSee('Recebidas');
    }

    public function test_ai_flags_persist_through_settings_update(): void
    {
        [$user, $loja] = $this->createTenantWithWhatsAppFeature();
        $account = $this->createAccount($loja);
        $this->actingAs($user);

        $this->post(route('admin.whatsapp.settings.update'), [
            'default_account_id'           => $account->id,
            'send_mode'                    => 'official_api',
            'ai_suggestions_enabled'       => '1',
            'ai_handoff_required'          => '1',
            'quote_recovery_enabled'       => '0',
        ])->assertRedirect(route('admin.whatsapp.index'));

        $settings = WhatsAppStoreSetting::where('loja_id', $loja->id)->first();
        $this->assertTrue((bool) ($settings->ai_handoff_required ?? true));
    }

    public function test_note_can_be_added_to_conversation(): void
    {
        [$user, $loja] = $this->createTenantWithWhatsAppFeature();
        $account = $this->createAccount($loja);
        $this->actingAs($user);

        $conversation = WhatsAppConversation::create([
            'loja_id'            => $loja->id,
            'whatsapp_account_id'=> $account->id,
            'contact_phone'      => '+5511900001111',
            'contact_name'       => 'Teste',
            'status'             => WhatsAppConversation::STATUS_OPEN,
        ]);

        $response = $this->post(
            route('admin.whatsapp.page.conversation.note.store', $conversation),
            ['note' => 'Nota de teste interna']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('whatsapp_conversation_notes', [
            'conversation_id' => $conversation->id,
            'note'            => 'Nota de teste interna',
        ]);
    }

    public function test_campaign_can_be_created_and_shows_recipients(): void
    {
        [$user, $loja] = $this->createTenantWithWhatsAppFeature();
        $this->actingAs($user);

        // Create a client with phone + opt-in
        $cliente = Cliente::create([
            'loja_id'  => $loja->id,
            'nome'     => 'Cliente Campanha',
            'whatsapp' => '11988880000',
            'email'    => 'campanha@example.com',
        ]);

        WhatsAppOptIn::create([
            'loja_id'     => $loja->id,
            'cliente_id'  => $cliente->id,
            'phone'       => '+5511988880000',
            'status'      => WhatsAppOptIn::STATUS_OPTED_IN,
            'opted_in_at' => now(),
        ]);

        $response = $this->post(route('admin.whatsapp.campaigns.store'), [
            'nome'           => 'Campanha Teste',
            'segment_type'   => 'opt_in_all',
            'segment_params' => [],
            'message_type'   => 'manual_link',
            'manual_message' => 'Olá {{nome_cliente}}, temos uma oferta!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('whatsapp_campaigns', [
            'loja_id'      => $loja->id,
            'nome'         => 'Campanha Teste',
            'segment_type' => 'opt_in_all',
        ]);
        $this->assertDatabaseHas('whatsapp_campaign_recipients', [
            'loja_id'    => $loja->id,
            'cliente_id' => $cliente->id,
        ]);
    }
}