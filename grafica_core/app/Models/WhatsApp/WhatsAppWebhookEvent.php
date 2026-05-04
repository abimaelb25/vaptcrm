<?php

declare(strict_types=1);

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Model;

class WhatsAppWebhookEvent extends Model
{
    protected $table = 'whatsapp_webhook_events';

    public const STATUS_PENDING   = 'pending';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_SKIPPED   = 'skipped';

    protected $fillable = [
        'loja_id',
        'provider',
        'event_type',
        'meta_message_id',
        'payload',
        'processing_status',
        'processing_error',
        'retry_count',
        'processed_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'processed_at' => 'datetime',
        'retry_count'  => 'integer',
    ];

    public function isPending(): bool
    {
        return $this->processing_status === self::STATUS_PENDING;
    }

    public function scopePending($query)
    {
        return $query->where('processing_status', self::STATUS_PENDING);
    }

    public function scopeRetryable($query, int $maxRetries = 3)
    {
        return $query
            ->where('processing_status', self::STATUS_FAILED)
            ->where('retry_count', '<', $maxRetries);
    }
}
