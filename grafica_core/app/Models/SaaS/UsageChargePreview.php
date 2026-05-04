<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageChargePreview extends Model
{
    use HasTenancy;

    protected $table = 'saas_usage_charge_previews';

    protected $fillable = [
        'loja_id',
        'assinatura_id',
        'cycle_start',
        'cycle_end',
        'currency',
        'total_amount',
        'status',
        'breakdown',
        'generated_at',
    ];

    protected $casts = [
        'cycle_start' => 'date',
        'cycle_end' => 'date',
        'total_amount' => 'decimal:2',
        'breakdown' => 'array',
        'generated_at' => 'datetime',
    ];

    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(Assinatura::class, 'assinatura_id');
    }
}
