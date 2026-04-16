<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:45
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenancy;

class AssetMaintenance extends Model
{
    use SoftDeletes, HasTenancy;

    protected $table = 'asset_maintenances';

    protected $fillable = [
        'loja_id',
        'asset_id',
        'tipo',
        'data',
        'custo',
        'descricao',
        'responsavel_id',
    ];

    protected $casts = [
        'data' => 'date',
        'custo' => 'float',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsavel_id');
    }
}
