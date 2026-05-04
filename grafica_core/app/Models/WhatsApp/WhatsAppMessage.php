<?php

declare(strict_types=1);

namespace App\Models\WhatsApp;

use App\Models\Loja;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppMessage extends Model
{
    use SoftDeletes;

    protected $table = 'whatsapp_messages';

    public const DIRECTION_INBOUND  = 'inbound';
    public const DIRECTION_OUTBOUND = 'outbound';

    public const TYPE_TEXT        = 'text';
    public const TYPE_IMAGE       = 'image';
    public const TYPE_DOCUMENT    = 'document';
    public const TYPE_AUDIO       = 'audio';
    public const TYPE_VIDEO       = 'video';
    public const TYPE_TEMPLATE    = 'template';
    public const TYPE_INTERACTIVE = 'interactive';
    public const TYPE_SYSTEM      = 'system';

    public const STATUS_PENDING   = 'pending';
    public const STATUS_SENT      = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_READ      = 'read';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_RECEIVED  = 'received';

    protected $fillable = [
        'loja_id',
        'whatsapp_account_id',
        'conversation_id',
        'meta_message_id',
        'direction',
        'type',
        'status',
        'body',
        'media_url',
        'media_mime_type',
        'template_data',
        'error_code',
        'error_message',
        'sent_by',
        'is_automated',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
    ];

    protected $casts = [
        'template_data' => 'array',
        'is_automated'  => 'boolean',
        'sent_at'       => 'datetime',
        'delivered_at'  => 'datetime',
        'read_at'       => 'datetime',
        'failed_at'     => 'datetime',
    ];

    public function isOutbound(): bool
    {
        return $this->direction === self::DIRECTION_OUTBOUND;
    }

    public function isInbound(): bool
    {
        return $this->direction === self::DIRECTION_INBOUND;
    }

    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class, 'whatsapp_account_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'sent_by');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', self::DIRECTION_OUTBOUND);
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', self::DIRECTION_INBOUND);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }
}
