<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanoLimit extends Model
{
    protected $table = 'saas_plano_limits';

    protected $fillable = [
        'plano_id',
        'limit_key',
        'limit_value',
    ];

    protected $casts = [
        'limit_value' => 'integer',
    ];

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }
}
