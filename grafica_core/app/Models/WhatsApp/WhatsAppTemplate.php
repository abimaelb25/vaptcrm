<?php

declare(strict_types=1);

namespace App\Models\WhatsApp;

use App\Models\Loja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PAUSED   = 'paused';
    public const STATUS_DISABLED = 'disabled';

    public const CATEGORY_MARKETING     = 'MARKETING';
    public const CATEGORY_UTILITY       = 'UTILITY';
    public const CATEGORY_AUTHENTICATION = 'AUTHENTICATION';

    // System keys for order automation
    public const KEY_ORDER_CREATED    = 'order_created';
    public const KEY_ORDER_QUOTE_SENT = 'order_quote_sent';
    public const KEY_PAYMENT_CONFIRMED = 'payment_confirmed';
    public const KEY_ORDER_PRODUCTION = 'order_in_production';
    public const KEY_ORDER_READY      = 'order_ready';
    public const KEY_ORDER_DELIVERED  = 'order_delivered';

    protected $fillable = [
        'loja_id',
        'whatsapp_account_id',
        'name',
        'language',
        'category',
        'status',
        'components',
        'meta_template_id',
        'is_system',
        'system_key',
    ];

    protected $casts = [
        'components' => 'array',
        'is_system'  => 'boolean',
    ];

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class, 'whatsapp_account_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('system_key', $key);
    }
}
