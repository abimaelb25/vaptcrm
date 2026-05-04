<?php

declare(strict_types=1);

namespace App\Models\WhatsApp;

use App\Models\Loja;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppCampaign extends Model
{
    use SoftDeletes;

    protected $table = 'whatsapp_campaigns';

    protected $fillable = [
        'loja_id',
        'whatsapp_account_id',
        'nome',
        'segment_type',
        'segment_params',
        'message_type',
        'template_name',
        'template_language',
        'manual_message',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
        'scheduled_at',
        'started_at',
        'finished_at',
        'created_by',
    ];

    protected $casts = [
        'segment_params' => 'array',
        'scheduled_at'   => 'datetime',
        'started_at'     => 'datetime',
        'finished_at'    => 'datetime',
    ];

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_RUNNING   = 'running';
    public const STATUS_DONE      = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    public const MESSAGE_TYPE_MANUAL   = 'manual_link';
    public const MESSAGE_TYPE_TEMPLATE = 'template';

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class, 'whatsapp_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsAppCampaignRecipient::class, 'campaign_id');
    }

    public function humanStatus(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT     => 'Rascunho',
            self::STATUS_SCHEDULED => 'Agendada',
            self::STATUS_RUNNING   => 'Em andamento',
            self::STATUS_DONE      => 'Concluída',
            self::STATUS_CANCELLED => 'Cancelada',
            default                => ucfirst((string) $this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT     => 'bg-slate-100 text-slate-600',
            self::STATUS_SCHEDULED => 'bg-amber-100 text-amber-700',
            self::STATUS_RUNNING   => 'bg-sky-100 text-sky-700',
            self::STATUS_DONE      => 'bg-emerald-100 text-emerald-700',
            self::STATUS_CANCELLED => 'bg-rose-100 text-rose-700',
            default                => 'bg-slate-100 text-slate-600',
        };
    }
}
