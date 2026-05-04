<?php

declare(strict_types=1);

namespace App\Models\WhatsApp;

use App\Models\Loja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppStoreSetting extends Model
{
    public const SEND_MODE_MANUAL = 'manual_link';
    public const SEND_MODE_API = 'official_api';

    protected $table = 'whatsapp_store_settings';

    protected $fillable = [
        'loja_id',
        'default_account_id',
        'catalog_link',
        'send_mode',
        'automations',
        'event_mappings',
        'last_test_phone',
        'ai_suggestions_enabled',
        'ai_auto_classification_enabled',
        'ai_handoff_required',
        'quote_recovery_enabled',
        'quote_recovery_delay_hours',
        'click_to_whatsapp_enabled',
    ];

    protected $casts = [
        'automations'                    => 'array',
        'event_mappings'                 => 'array',
        'ai_suggestions_enabled'         => 'boolean',
        'ai_auto_classification_enabled' => 'boolean',
        'ai_handoff_required'            => 'boolean',
        'quote_recovery_enabled'         => 'boolean',
        'click_to_whatsapp_enabled'      => 'boolean',
    ];

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function defaultAccount(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class, 'default_account_id');
    }

    public function isAutomationEnabled(string $eventKey): bool
    {
        return (bool) ($this->automations[$eventKey] ?? false);
    }

    public function mappingFor(string $eventKey): array
    {
        return (array) ($this->event_mappings[$eventKey] ?? []);
    }

    public function isManualMode(): bool
    {
        return $this->send_mode === self::SEND_MODE_MANUAL;
    }

    public function isApiMode(): bool
    {
        return ($this->send_mode ?? self::SEND_MODE_API) === self::SEND_MODE_API;
    }

    public function aiSuggestionsEnabled(): bool
    {
        return (bool) ($this->ai_suggestions_enabled ?? false);
    }

    public function aiHandoffRequired(): bool
    {
        return (bool) ($this->ai_handoff_required ?? true);
    }

    public function quoteRecoveryEnabled(): bool
    {
        return (bool) ($this->quote_recovery_enabled ?? false);
    }
}