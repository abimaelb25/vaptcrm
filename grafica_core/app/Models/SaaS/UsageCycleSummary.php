<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageCycleSummary extends Model
{
    use HasTenancy;

    protected $table = 'saas_usage_cycle_summaries';

    protected $fillable = [
        'loja_id',
        'assinatura_id',
        'cycle_start',
        'cycle_end',
        'metric_key',
        'consumed',
        'limit_value',
        'overage',
        'unit_price',
        'subtotal',
        'metadata',
        'consolidated_at',
    ];

    protected $casts = [
        'cycle_start' => 'date',
        'cycle_end' => 'date',
        'consumed' => 'integer',
        'limit_value' => 'integer',
        'overage' => 'integer',
        'unit_price' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'metadata' => 'array',
        'consolidated_at' => 'datetime',
    ];

    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(Assinatura::class, 'assinatura_id');
    }
}
