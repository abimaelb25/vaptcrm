<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderHistory extends Model
{
    protected $fillable = [
        'production_order_id',
        'etapa_origem_id',
        'etapa_destino_id',
        'usuario_id',
        'data_movimentacao',
        'observacao',
    ];

    protected $casts = [
        'data_movimentacao' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function etapaOrigem(): BelongsTo
    {
        return $this->belongsTo(ProductionStep::class, 'etapa_origem_id')
            ->withDefault(['nome' => 'Início']);
    }

    public function etapaDestino(): BelongsTo
    {
        return $this->belongsTo(ProductionStep::class, 'etapa_destino_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id')
            ->withDefault(['nome' => 'Sistema']);
    }
}
