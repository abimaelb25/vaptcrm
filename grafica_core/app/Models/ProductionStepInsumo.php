<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-20
| Descrição: BOM (Bill of Materials) — vínculo insumo ↔ etapa de produção (definição).
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenancy;

class ProductionStepInsumo extends Model
{
    use HasTenancy;

    protected $fillable = [
        'loja_id',
        'production_step_id',
        'insumo_id',
        'quantidade_por_unidade',
        'unidade_medida',
        'observacao',
    ];

    protected $casts = [
        'quantidade_por_unidade' => 'float',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(ProductionStep::class, 'production_step_id');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }
}
