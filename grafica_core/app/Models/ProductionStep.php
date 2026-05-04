<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:25
| Modificado: 2026-04-20 (Adicionado vínculo com ProductionPhase)
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class ProductionStep extends Model
{
    use SoftDeletes, HasTenancy;

    protected $fillable = [
        'loja_id',
        'production_phase_id',
        'nome',
        'ordem',
        'ativo',
        'asset_id',
        'tempo_estimado_minutos',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
        'tempo_estimado_minutos' => 'integer',
    ];

    /**
     * Fase de produção a qual esta etapa pertence.
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProductionPhase::class, 'production_phase_id');
    }

    /**
     * Alias para phase() - nomenclatura em português.
     */
    public function fase(): BelongsTo
    {
        return $this->phase();
    }

    public function orderSteps(): HasMany
    {
        return $this->hasMany(ProductionOrderStep::class);
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class, 'production_step_id');
    }

    public function historyOrigins(): HasMany
    {
        return $this->hasMany(ProductionOrderHistory::class, 'etapa_origem_id');
    }

    public function historyDestinations(): HasMany
    {
        return $this->hasMany(ProductionOrderHistory::class, 'etapa_destino_id');
    }

    /**
     * Equipamento vinculado a esta etapa.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Insumos necessários nesta etapa (BOM).
     */
    public function insumos(): HasMany
    {
        return $this->hasMany(ProductionStepInsumo::class);
    }
}
