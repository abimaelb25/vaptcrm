<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:35
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class ProductionOrderStep extends Model
{
    use HasTenancy;

    protected $fillable = [
        'loja_id',
        'production_order_id',
        'production_step_id',
        'nome_snapshot',
        'ordem_snapshot',
        'fase_snapshot',
        'status',
        'responsavel_id',
        'data_inicio',
        'data_fim',
        'tempo_estimado',
        'tempo_real',
        'observacao',
        'asset_id',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'tempo_estimado' => 'integer',
        'tempo_real' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function stepDefinition(): BelongsTo
    {
        return $this->belongsTo(ProductionStep::class, 'production_step_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsavel_id');
    }

    /**
     * Equipamento usado nesta execução.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Insumos previstos/consumidos nesta execução.
     */
    public function insumos(): HasMany
    {
        return $this->hasMany(ProductionOrderStepInsumo::class);
    }
}
