<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| WhatsApp Module Evolution v2
|
| Adds:
|  - AI flags to whatsapp_store_settings
|  - priority + internal notes + quote_recovery_sent_at to whatsapp_conversations
|  - whatsapp_conversation_notes (internal notes)
|  - whatsapp_campaigns (segment campaigns)
|  - quote_recovery_at + product_share_token to pedidos (if columns missing)
*/

return new class extends Migration
{
    public function up(): void
    {
        // 1. AI flags + campaign features on store settings
        Schema::table('whatsapp_store_settings', function (Blueprint $table): void {
            if (!Schema::hasColumn('whatsapp_store_settings', 'ai_suggestions_enabled')) {
                $table->boolean('ai_suggestions_enabled')->default(false)->after('send_mode');
            }
            if (!Schema::hasColumn('whatsapp_store_settings', 'ai_auto_classification_enabled')) {
                $table->boolean('ai_auto_classification_enabled')->default(false)->after('ai_suggestions_enabled');
            }
            if (!Schema::hasColumn('whatsapp_store_settings', 'ai_handoff_required')) {
                $table->boolean('ai_handoff_required')->default(true)->after('ai_auto_classification_enabled');
            }
            if (!Schema::hasColumn('whatsapp_store_settings', 'quote_recovery_enabled')) {
                $table->boolean('quote_recovery_enabled')->default(false)->after('ai_handoff_required');
            }
            if (!Schema::hasColumn('whatsapp_store_settings', 'quote_recovery_delay_hours')) {
                $table->unsignedSmallInteger('quote_recovery_delay_hours')->default(24)->after('quote_recovery_enabled');
            }
            if (!Schema::hasColumn('whatsapp_store_settings', 'click_to_whatsapp_enabled')) {
                $table->boolean('click_to_whatsapp_enabled')->default(false)->after('quote_recovery_delay_hours');
            }
        });

        // 2. Priority and quote recovery tracking on conversations
        Schema::table('whatsapp_conversations', function (Blueprint $table): void {
            if (!Schema::hasColumn('whatsapp_conversations', 'priority')) {
                $table->string('priority', 20)->default('normal')->after('status');
            }
            if (!Schema::hasColumn('whatsapp_conversations', 'quote_recovery_sent_at')) {
                $table->timestamp('quote_recovery_sent_at')->nullable()->after('window_expires_at');
            }
            if (!Schema::hasColumn('whatsapp_conversations', 'origin_source')) {
                $table->string('origin_source', 60)->nullable()->after('quote_recovery_sent_at');
            }
            if (!Schema::hasColumn('whatsapp_conversations', 'ai_intent')) {
                $table->string('ai_intent', 60)->nullable()->after('origin_source');
            }
            if (!Schema::hasColumn('whatsapp_conversations', 'ai_summary')) {
                $table->text('ai_summary')->nullable()->after('ai_intent');
            }
        });

        // 3. Internal conversation notes
        Schema::create('whatsapp_conversation_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('conversation_id')
                ->constrained('whatsapp_conversations')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('usuarios')->cascadeOnDelete();
            $table->text('note');
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        // 4. Campaigns table
        Schema::create('whatsapp_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')
                ->nullable()
                ->constrained('whatsapp_accounts')
                ->nullOnDelete();
            $table->string('nome', 120);
            $table->string('segment_type', 60);  // opt_in_all, pending_quote, repeat_product, inactive_days
            $table->json('segment_params')->nullable();
            $table->string('message_type', 20)->default('manual_link'); // manual_link | template
            $table->string('template_name', 120)->nullable();
            $table->string('template_language', 20)->nullable();
            $table->text('manual_message')->nullable();
            $table->string('status', 30)->default('draft'); // draft | scheduled | running | done | cancelled
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('created_by')->constrained('usuarios')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['loja_id', 'status']);
        });

        // 5. Campaign recipients log
        Schema::create('whatsapp_campaign_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')
                ->constrained('whatsapp_campaigns')
                ->cascadeOnDelete();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('phone', 30);
            $table->string('status', 30)->default('pending'); // pending | sent | failed | skipped
            $table->text('error_reason')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('wa_me_link', 1000)->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_campaign_recipients');
        Schema::dropIfExists('whatsapp_campaigns');
        Schema::dropIfExists('whatsapp_conversation_notes');

        Schema::table('whatsapp_conversations', function (Blueprint $table): void {
            foreach (['priority', 'quote_recovery_sent_at', 'origin_source', 'ai_intent', 'ai_summary'] as $col) {
                if (Schema::hasColumn('whatsapp_conversations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('whatsapp_store_settings', function (Blueprint $table): void {
            foreach ([
                'ai_suggestions_enabled', 'ai_auto_classification_enabled', 'ai_handoff_required',
                'quote_recovery_enabled', 'quote_recovery_delay_hours', 'click_to_whatsapp_enabled',
            ] as $col) {
                if (Schema::hasColumn('whatsapp_store_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
