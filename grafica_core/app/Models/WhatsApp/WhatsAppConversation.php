<?php

declare(strict_types=1);

namespace App\Models\WhatsApp;

use App\Models\Cliente;
use App\Models\Loja;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppConversation extends Model
{
    use SoftDeletes;

    protected $table = 'whatsapp_conversations';

    public const STATUS_OPEN     = 'open';
    public const STATUS_WAITING  = 'waiting';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_BOT      = 'bot';

    public const PRIORITY_LOW    = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH   = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'loja_id',
        'whatsapp_account_id',
        'cliente_id',
        'pedido_id',
        'contact_phone',
        'contact_name',
        'status',
        'priority',
        'assigned_to',
        'window_expires_at',
        'last_message_at',
        'last_message_id',
        'is_unread',
        'tags',
        'quote_recovery_sent_at',
        'origin_source',
        'ai_intent',
        'ai_summary',
    ];

    protected $casts = [
        'window_expires_at'      => 'datetime',
        'last_message_at'        => 'datetime',
        'quote_recovery_sent_at' => 'datetime',
        'is_unread'              => 'boolean',
        'tags'                   => 'array',
    ];

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Returns true if the 24h service conversation window is still open. */
    public function isWithinServiceWindow(): bool
    {
        return $this->window_expires_at !== null
            && $this->window_expires_at->isFuture();
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class, 'whatsapp_account_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(WhatsAppConversationNote::class, 'conversation_id');
    }

    public function priorityBadgeClass(): string
    {
        return match ($this->priority ?? self::PRIORITY_NORMAL) {
            self::PRIORITY_LOW    => 'bg-slate-100 text-slate-500',
            self::PRIORITY_HIGH   => 'bg-amber-100 text-amber-700',
            self::PRIORITY_URGENT => 'bg-rose-100 text-rose-700',
            default               => 'bg-sky-100 text-sky-600',
        };
    }

    public function humanPriority(): string
    {
        return match ($this->priority ?? self::PRIORITY_NORMAL) {
            self::PRIORITY_LOW    => 'Baixa',
            self::PRIORITY_HIGH   => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
            default               => 'Normal',
        };
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_unread', true);
    }

    public function scopeForLoja($query, int $lojaId)
    {
        return $query->where('loja_id', $lojaId);
    }
}
