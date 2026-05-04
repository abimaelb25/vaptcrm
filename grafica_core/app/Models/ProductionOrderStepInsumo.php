<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-20
| Descrição: Consumo real de insumos por etapa da OP (rastreio de baixa futura).
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenancy;

class ProductionOrderStepInsumo extends Model
{
    use HasTenancy;

    protected $fillable = [
        'loja_id',
        'production_order_step_id',
        'insumo_id',
        'quantidade_prevista',
        'quantidade_consumida',
        'baixa_estoque_realizada',
    ];

    protected $casts = [
        'quantidade_prevista'     => 'float',
        'quantidade_consumida'    => 'float',
        'baixa_estoque_realizada' => 'boolean',
    ];

    public function orderStep(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderStep::class, 'production_order_step_id');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }
}
