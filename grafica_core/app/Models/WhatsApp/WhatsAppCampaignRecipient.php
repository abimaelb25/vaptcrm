<?php

declare(strict_types=1);

namespace App\Models\WhatsApp;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppCampaignRecipient extends Model
{
    protected $table = 'whatsapp_campaign_recipients';

    protected $fillable = [
        'campaign_id',
        'loja_id',
        'cliente_id',
        'phone',
        'status',
        'error_reason',
        'sent_at',
        'wa_me_link',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_SENT     = 'sent';
    public const STATUS_FAILED   = 'failed';
    public const STATUS_SKIPPED  = 'skipped';

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsAppCampaign::class, 'campaign_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
