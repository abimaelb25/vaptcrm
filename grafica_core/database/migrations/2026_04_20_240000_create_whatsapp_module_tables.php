<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: WhatsApp Business Platform Integration Module
 *
 * Creates all tables for the official WhatsApp Business integration:
 *   - whatsapp_accounts        : per-store WABA credentials (encrypted)
 *   - whatsapp_templates       : approved HSM/template registry
 *   - whatsapp_conversations   : conversation threads (one per contact+account)
 *   - whatsapp_messages        : individual messages (inbound + outbound)
 *   - whatsapp_webhook_events  : raw webhook payload audit log
 *   - whatsapp_optins          : opt-in records for compliance
 */
return new class extends Migration
{
    public function up(): void
    {
        // -----------------------------------------------------------------------
        // 1. whatsapp_accounts — one per WhatsApp Business number, per loja
        // -----------------------------------------------------------------------
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id')->index();
            $table->string('provider', 30)->default('meta_cloud'); // meta_cloud | bsp_adapter
            $table->string('display_name', 120)->nullable();
            $table->string('phone_number', 30)->nullable();          // E.164 format
            $table->string('phone_number_id', 80)->nullable();       // Meta phone number ID
            $table->string('waba_id', 80)->nullable();               // WhatsApp Business Account ID
            $table->string('business_id', 80)->nullable();           // Meta Business ID
            $table->text('access_token_encrypted')->nullable();      // AES-256 encrypted
            $table->string('webhook_verify_token', 80)->nullable();  // per-account verify token
            $table->string('quality_rating', 20)->nullable();        // GREEN | YELLOW | RED
            $table->enum('status', ['pending', 'active', 'suspended', 'banned', 'disconnected'])->default('pending');
            $table->boolean('is_primary')->default(false);           // primary number for this loja
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->json('meta')->nullable();                        // provider-specific extra data
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('loja_id')->references('id')->on('lojas')->onDelete('cascade');
            $table->index(['loja_id', 'status']);
            $table->index(['phone_number_id']);
            $table->index(['waba_id']);
        });

        // -----------------------------------------------------------------------
        // 2. whatsapp_templates — approved Meta HSM templates synced per account
        // -----------------------------------------------------------------------
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id')->index();
            $table->unsignedBigInteger('whatsapp_account_id')->index();
            $table->string('name', 120);                              // Meta template name
            $table->string('language', 10)->default('pt_BR');
            $table->string('category', 40);                          // MARKETING | UTILITY | AUTHENTICATION
            $table->enum('status', ['pending', 'approved', 'rejected', 'paused', 'disabled'])->default('pending');
            $table->json('components')->nullable();                   // header/body/footer/buttons
            $table->string('meta_template_id', 80)->nullable();      // Meta template ID
            $table->boolean('is_system')->default(false);            // system-managed (order status, etc.)
            $table->string('system_key', 60)->nullable();            // e.g.: order_created, order_ready
            $table->timestamps();

            $table->foreign('loja_id')->references('id')->on('lojas')->onDelete('cascade');
            $table->foreign('whatsapp_account_id')->references('id')->on('whatsapp_accounts')->onDelete('cascade');
            $table->unique(['whatsapp_account_id', 'name', 'language']);
        });

        // -----------------------------------------------------------------------
        // 3. whatsapp_conversations — one thread per (account + contact phone)
        // -----------------------------------------------------------------------
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id')->index();
            $table->unsignedBigInteger('whatsapp_account_id')->index();
            $table->unsignedBigInteger('cliente_id')->nullable()->index();
            $table->unsignedBigInteger('pedido_id')->nullable()->index();
            $table->string('contact_phone', 30);                     // E.164 customer phone
            $table->string('contact_name', 120)->nullable();
            $table->enum('status', [
                'open',           // actively attended
                'waiting',        // waiting for customer reply
                'resolved',       // closed/resolved
                'bot',            // handled by automation
            ])->default('open');
            $table->unsignedBigInteger('assigned_to')->nullable();   // usuario_id
            $table->timestamp('window_expires_at')->nullable();      // 24h window expiry
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedBigInteger('last_message_id')->nullable();
            $table->boolean('is_unread')->default(false);
            $table->json('tags')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('loja_id')->references('id')->on('lojas')->onDelete('cascade');
            $table->foreign('whatsapp_account_id')->references('id')->on('whatsapp_accounts')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('set null');
            $table->index(['loja_id', 'status', 'last_message_at']);
            $table->unique(['whatsapp_account_id', 'contact_phone', 'deleted_at'], 'wa_conv_account_phone_unique');
        });

        // -----------------------------------------------------------------------
        // 4. whatsapp_messages — individual messages (inbound + outbound)
        // -----------------------------------------------------------------------
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id')->index();
            $table->unsignedBigInteger('whatsapp_account_id')->index();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->string('meta_message_id', 120)->nullable()->unique(); // wamid from Meta
            $table->enum('direction', ['inbound', 'outbound'])->index();
            $table->enum('type', [
                'text', 'image', 'document', 'audio', 'video', 'sticker',
                'location', 'contacts', 'template', 'interactive', 'reaction', 'system',
            ])->default('text');
            $table->enum('status', [
                'pending',    // queued locally
                'sent',       // sent to Meta API
                'delivered',  // delivered to device
                'read',       // read by contact
                'failed',     // failed to send
                'received',   // inbound received
            ])->default('pending');
            $table->text('body')->nullable();                         // text content
            $table->string('media_url', 500)->nullable();             // stored media URL
            $table->string('media_mime_type', 80)->nullable();
            $table->json('template_data')->nullable();                // template params used
            $table->string('error_code', 20)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('sent_by')->nullable();        // usuario_id (null = automated)
            $table->boolean('is_automated')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('loja_id')->references('id')->on('lojas')->onDelete('cascade');
            $table->foreign('whatsapp_account_id')->references('id')->on('whatsapp_accounts')->onDelete('cascade');
            $table->foreign('conversation_id')->references('id')->on('whatsapp_conversations')->onDelete('cascade');
            $table->index(['conversation_id', 'created_at']);
            $table->index(['loja_id', 'direction', 'created_at']);
        });

        // -----------------------------------------------------------------------
        // 5. whatsapp_webhook_events — raw webhook audit log (never modified)
        // -----------------------------------------------------------------------
        Schema::create('whatsapp_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id')->nullable()->index();
            $table->string('provider', 30)->default('meta_cloud');
            $table->string('event_type', 80)->nullable();            // message | status | template_status
            $table->string('meta_message_id', 120)->nullable()->index();
            $table->json('payload');                                  // full raw payload stored
            $table->enum('processing_status', ['pending', 'processed', 'failed', 'skipped'])->default('pending');
            $table->string('processing_error', 500)->nullable();
            $table->unsignedSmallInteger('retry_count')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'event_type', 'processing_status'], 'wa_webhook_events_status_idx');
            $table->index('created_at');
        });

        // -----------------------------------------------------------------------
        // 6. whatsapp_optins — opt-in compliance record per contact per loja
        // -----------------------------------------------------------------------
        Schema::create('whatsapp_optins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loja_id')->index();
            $table->unsignedBigInteger('cliente_id')->nullable()->index();
            $table->string('phone', 30);                             // E.164
            $table->enum('status', ['opted_in', 'opted_out'])->default('opted_in');
            $table->string('source', 60)->nullable();                // checkout | form | whatsapp_button
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('opted_in_at')->nullable();
            $table->timestamp('opted_out_at')->nullable();
            $table->timestamps();

            $table->foreign('loja_id')->references('id')->on('lojas')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');
            $table->unique(['loja_id', 'phone']);
            $table->index(['loja_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_optins');
        Schema::dropIfExists('whatsapp_webhook_events');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_conversations');
        Schema::dropIfExists('whatsapp_templates');
        Schema::dropIfExists('whatsapp_accounts');
    }
};
