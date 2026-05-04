<?php

declare(strict_types=1);

namespace App\Models\SaaS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanoFeature extends Model
{
    protected $table = 'saas_plano_features';

    protected $fillable = [
        'plano_id',
        'feature_key',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }
}
