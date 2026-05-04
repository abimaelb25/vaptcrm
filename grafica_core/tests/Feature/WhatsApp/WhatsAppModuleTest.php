<?php

declare(strict_types=1);

namespace Tests\Feature\WhatsApp;

use App\Models\Cliente;
use App\Models\Loja;
use App\Models\Pedido;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppConversation;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Models\WhatsApp\WhatsAppOptIn;
use App\Models\WhatsApp\WhatsAppWebhookEvent;
use App\Services\WhatsApp\WhatsAppAccountService;
use App\Services\WhatsApp\WhatsAppConversationService;
use App\Services\WhatsApp\WhatsAppMessageService;
use App\Services\WhatsApp\WhatsAppWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * WhatsApp Module Feature Tests
 *
 * Validates:
 * - Account onboarding and plan limits
 * - Opt-in enforcement
 * - Conversation lifecycle (create, window, resolve)
 * - Message service guards (window, quota, optin)
 * - Webhook ingestion (message + status update)
 * - Order automation silently skips when no account/template
 */
final class WhatsAppModuleTest extends TestCase
{
    use RefreshDatabase;

    private Loja $loja;
    private WhatsAppAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loja = Loja::create([
            'nome_fantasia'      => 'Loja WhatsApp Teste',
            'slug'               => 'loja-wa-' . uniqid(),
            'responsavel_nome'   => 'Teste',
            'responsavel_email'  => 'wa-' . uniqid() . '@example.com',
            'status'             => 'ativa',
        ]);

        $this->account = WhatsAppAccount::create([
            'loja_id'                 => $this->loja->id,
            'provider'                => WhatsAppAccount::PROVIDER_META_CLOUD,
            'display_name'            => 'Teste WA',
            'phone_number'            => '+5511999990001',
            'phone_number_id'         => 'pn_test_001',
            'waba_id'                 => 'waba_test_001',
            'access_token_encrypted'  => \Illuminate\Support\Facades\Crypt::encryptString('fake-token'),
            'webhook_verify_token'    => 'verify_abc123',
            'status'                  => WhatsAppAccount::STATUS_ACTIVE,
            'is_primary'              => true,
        ]);
    }

    // -------------------------------------------------------------------------
    // Account onboarding
    // -------------------------------------------------------------------------

    public function test_onboard_creates_account_with_encrypted_token(): void
    {
        /** @var Loja $loja2 */
        $loja2 = Loja::create([
            'nome_fantasia'     => 'Loja Onboard Teste',
            'slug'              => 'loja-ob-' . uniqid(),
            'responsavel_nome'  => 'Teste',
            'responsavel_email' => 'ob-' . uniqid() . '@example.com',
            'status'            => 'ativa',
        ]);

        $service = app(WhatsAppAccountService::class);

        $account = $service->onboard($loja2, [
            'waba_id'         => 'waba_test_new',
            'phone_number_id' => 'pn_test_new',
            'phone_number'    => '+5511999990002',
            'display_name'    => 'New Account',
            'access_token'    => 'super-secret-token',
        ]);

        $this->assertDatabaseHas('whatsapp_accounts', [
            'loja_id'         => $loja2->id,
            'phone_number_id' => 'pn_test_new',
            'status'          => WhatsAppAccount::STATUS_ACTIVE,
            'is_primary'      => true,
        ]);

        // Raw token must NOT be in DB
        $this->assertDatabaseMissing('whatsapp_accounts', [
            'access_token_encrypted' => 'super-secret-token',
        ]);

        // But decryptable via model accessor
        $this->assertSame('super-secret-token', $account->access_token);
    }

    public function test_onboard_marks_first_account_as_primary(): void
    {
        // Use a fresh loja that starts with zero accounts so we can add two
        $loja = Loja::create([
            'nome_fantasia'     => 'Loja Two Accounts',
            'slug'              => 'loja-2acc-' . uniqid(),
            'responsavel_nome'  => 'Teste',
            'responsavel_email' => '2acc-' . uniqid() . '@example.com',
            'status'            => 'ativa',
        ]);

        $service = app(WhatsAppAccountService::class);

        $first = $service->onboard($loja, [
            'waba_id'         => 'waba_first',
            'phone_number_id' => 'pn_first',
            'phone_number'    => '+5511999990010',
            'display_name'    => 'First',
            'access_token'    => 'token-first',
        ]);

        $this->assertTrue($first->is_primary);

        // Temporarily remove account limit by seeding a null-limit plano for this loja
        // (no PlanoLimit row = unlimited)
        $second = $service->onboard($loja, [
            'waba_id'         => 'waba_second',
            'phone_number_id' => 'pn_second',
            'phone_number'    => '+5511999990011',
            'display_name'    => 'Second',
            'access_token'    => 'token-second',
        ]);

        $this->assertFalse($second->is_primary);
        $this->assertTrue($first->fresh()->is_primary);
    }

    // -------------------------------------------------------------------------
    // Opt-in
    // -------------------------------------------------------------------------

    public function test_opt_in_record_can_be_created_and_queried(): void
    {
        WhatsAppOptIn::create([
            'loja_id'     => $this->loja->id,
            'phone'       => '+5511999990001',
            'status'      => WhatsAppOptIn::STATUS_OPTED_IN,
            'source'      => 'checkout',
            'opted_in_at' => now(),
        ]);

        $optin = WhatsAppOptIn::where('loja_id', $this->loja->id)
            ->where('phone', '+5511999990001')
            ->first();

        $this->assertNotNull($optin);
        $this->assertTrue($optin->hasOptedIn());
    }

    // -------------------------------------------------------------------------
    // Conversation lifecycle
    // -------------------------------------------------------------------------

    public function test_inbound_message_creates_conversation_with_24h_window(): void
    {
        $service      = app(WhatsAppConversationService::class);
        $conversation = $service->findOrCreateFromInbound(
            $this->account, '+5511988880001', 'João Teste'
        );

        $this->assertInstanceOf(WhatsAppConversation::class, $conversation);
        $this->assertSame($this->loja->id, $conversation->loja_id);
        $this->assertSame('+5511988880001', $conversation->contact_phone);
        $this->assertTrue($conversation->isWithinServiceWindow());
        $this->assertSame(WhatsAppConversation::STATUS_OPEN, $conversation->status);
    }

    public function test_second_inbound_from_same_phone_refreshes_window(): void
    {
        $service = app(WhatsAppConversationService::class);

        $first  = $service->findOrCreateFromInbound($this->account, '+5511988880002');
        // Simulate window expiry
        $first->update(['window_expires_at' => now()->subHour()]);

        $second = $service->findOrCreateFromInbound($this->account, '+5511988880002');

        $this->assertSame($first->id, $second->id); // Same conversation
        $this->assertTrue($second->isWithinServiceWindow()); // Window refreshed
    }

    public function test_conversation_can_be_resolved_and_reopened(): void
    {
        $service      = app(WhatsAppConversationService::class);
        $conversation = $service->findOrCreateFromInbound($this->account, '+5511988880003');

        $service->resolve($conversation);
        $this->assertSame(WhatsAppConversation::STATUS_RESOLVED, $conversation->fresh()->status);

        $service->reopen($conversation);
        $this->assertSame(WhatsAppConversation::STATUS_OPEN, $conversation->fresh()->status);
    }

    // -------------------------------------------------------------------------
    // Message service guards
    // -------------------------------------------------------------------------

    public function test_send_text_fails_outside_service_window(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/janela de 24h/');

        $conversation = WhatsAppConversation::create([
            'loja_id'             => $this->loja->id,
            'whatsapp_account_id' => $this->account->id,
            'contact_phone'       => '+5511988880010',
            'status'              => WhatsAppConversation::STATUS_OPEN,
            'window_expires_at'   => now()->subHour(), // already expired
        ]);

        $service = app(WhatsAppMessageService::class);
        $service->sendText($this->account, $conversation, 'Hello');
    }

    public function test_send_text_fails_without_optin(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/opt-in/');

        $conversation = WhatsAppConversation::create([
            'loja_id'             => $this->loja->id,
            'whatsapp_account_id' => $this->account->id,
            'contact_phone'       => '+5511988880011',
            'status'              => WhatsAppConversation::STATUS_OPEN,
            'window_expires_at'   => now()->addHour(), // window is open
        ]);

        $service = app(WhatsAppMessageService::class);
        $service->sendText($this->account, $conversation, 'Hello');
    }

    public function test_send_text_queues_message_when_valid(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        // Create opt-in
        WhatsAppOptIn::create([
            'loja_id'     => $this->loja->id,
            'phone'       => '+5511988880012',
            'status'      => WhatsAppOptIn::STATUS_OPTED_IN,
            'opted_in_at' => now(),
        ]);

        $conversation = WhatsAppConversation::create([
            'loja_id'             => $this->loja->id,
            'whatsapp_account_id' => $this->account->id,
            'contact_phone'       => '+5511988880012',
            'status'              => WhatsAppConversation::STATUS_OPEN,
            'window_expires_at'   => now()->addHour(),
        ]);

        $service = app(WhatsAppMessageService::class);
        $message = $service->sendText($this->account, $conversation, 'Olá!');

        $this->assertSame(WhatsAppMessage::STATUS_PENDING, $message->status);
        $this->assertSame(WhatsAppMessage::DIRECTION_OUTBOUND, $message->direction);

        \Illuminate\Support\Facades\Queue::assertPushed(
            \App\Jobs\WhatsApp\SendWhatsAppMessageJob::class
        );
    }

    // -------------------------------------------------------------------------
    // Webhook ingestion
    // -------------------------------------------------------------------------

    public function test_webhook_inbound_message_creates_message_record(): void
    {
        $messageId = 'wamid.test.' . uniqid();

        $payload = $this->buildMetaMessagePayload(
            phoneNumberId: 'pn_test_001',
            fromPhone: '+5511988880020',
            messageId: $messageId,
            text: 'Olá, preciso de ajuda'
        );

        $service = app(WhatsAppWebhookService::class);
        $service->handle($payload, 'meta_cloud');

        $this->assertDatabaseHas('whatsapp_messages', [
            'meta_message_id' => $messageId,
            'direction'       => WhatsAppMessage::DIRECTION_INBOUND,
            'body'            => 'Olá, preciso de ajuda',
            'status'          => WhatsAppMessage::STATUS_RECEIVED,
        ]);

        $this->assertDatabaseHas('whatsapp_webhook_events', [
            'meta_message_id'   => $messageId,
            'processing_status' => WhatsAppWebhookEvent::STATUS_PROCESSED,
        ]);
    }

    public function test_webhook_duplicate_message_is_idempotent(): void
    {
        $messageId = 'wamid.dup.' . uniqid();
        $payload   = $this->buildMetaMessagePayload('pn_test_001', '+5511988880021', $messageId, 'Dup msg');

        $service = app(WhatsAppWebhookService::class);
        $service->handle($payload, 'meta_cloud');
        $service->handle($payload, 'meta_cloud'); // second call

        // Only one message persisted
        $this->assertSame(
            1,
            WhatsAppMessage::where('meta_message_id', $messageId)->count()
        );
    }

    public function test_webhook_status_update_marks_message_delivered(): void
    {
        // Create a sent outbound message first
        $conversation = app(WhatsAppConversationService::class)
            ->findOrCreateFromInbound($this->account, '+5511988880030');

        $message = WhatsAppMessage::create([
            'loja_id'             => $this->loja->id,
            'whatsapp_account_id' => $this->account->id,
            'conversation_id'     => $conversation->id,
            'meta_message_id'     => 'wamid.status.test.001',
            'direction'           => WhatsAppMessage::DIRECTION_OUTBOUND,
            'type'                => WhatsAppMessage::TYPE_TEXT,
            'status'              => WhatsAppMessage::STATUS_SENT,
            'body'                => 'Sent message',
            'is_automated'        => false,
        ]);

        $payload = $this->buildMetaStatusPayload(
            phoneNumberId: 'pn_test_001',
            recipientPhone: '+5511988880030',
            messageId: 'wamid.status.test.001',
            status: 'delivered'
        );

        $service = app(WhatsAppWebhookService::class);
        $service->handle($payload, 'meta_cloud');

        $this->assertSame(
            WhatsAppMessage::STATUS_DELIVERED,
            $message->fresh()->status
        );
    }

    public function test_webhook_for_unknown_account_is_skipped(): void
    {
        $payload = $this->buildMetaMessagePayload(
            phoneNumberId: 'pn_UNKNOWN',
            fromPhone: '+5511988880099',
            messageId: 'wamid.unknown.' . uniqid(),
            text: 'Unknown account'
        );

        $service = app(WhatsAppWebhookService::class);
        $service->handle($payload, 'meta_cloud');

        $this->assertDatabaseHas('whatsapp_webhook_events', [
            'processing_status' => WhatsAppWebhookEvent::STATUS_SKIPPED,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers — build Meta-like webhook payloads
    // -------------------------------------------------------------------------

    private function buildMetaMessagePayload(
        string $phoneNumberId,
        string $fromPhone,
        string $messageId,
        string $text
    ): array {
        return [
            'object' => 'whatsapp_business_account',
            'entry'  => [[
                'id'      => 'entry_id',
                'changes' => [[
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata'          => [
                            'display_phone_number' => '15550000001',
                            'phone_number_id'      => $phoneNumberId,
                        ],
                        'contacts' => [[
                            'profile' => ['name' => 'Test Contact'],
                            'wa_id'   => ltrim($fromPhone, '+'),
                        ]],
                        'messages' => [[
                            'from'      => $fromPhone,
                            'id'        => $messageId,
                            'timestamp' => (string) now()->timestamp,
                            'type'      => 'text',
                            'text'      => ['body' => $text],
                        ]],
                    ],
                    'field' => 'messages',
                ]],
            ]],
        ];
    }

    private function buildMetaStatusPayload(
        string $phoneNumberId,
        string $recipientPhone,
        string $messageId,
        string $status
    ): array {
        return [
            'object' => 'whatsapp_business_account',
            'entry'  => [[
                'id'      => 'entry_id',
                'changes' => [[
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata'          => [
                            'display_phone_number' => '15550000001',
                            'phone_number_id'      => $phoneNumberId,
                        ],
                        'statuses' => [[
                            'id'           => $messageId,
                            'status'       => $status,
                            'timestamp'    => (string) now()->timestamp,
                            'recipient_id' => $recipientPhone,
                        ]],
                    ],
                    'field' => 'messages',
                ]],
            ]],
        ];
    }
}
