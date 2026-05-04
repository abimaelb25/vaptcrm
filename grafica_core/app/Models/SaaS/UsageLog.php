<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLog extends Model
{
    use HasTenancy;

    protected $table = 'saas_usage_logs';

    protected $fillable = [
        'loja_id',
        'assinatura_id',
        'event_type',
        'feature_key',
        'limit_key',
        'delta',
        'used_total',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(Assinatura::class, 'assinatura_id');
    }
}
