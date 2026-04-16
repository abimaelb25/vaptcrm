<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:30
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class ProductionOrder extends Model
{
    use SoftDeletes, HasTenancy;

    protected $fillable = [
        'loja_id',
        'pedido_id',
        'status',
        'prioridade',
        'data_inicio',
        'data_previsao',
        'data_conclusao',
        'responsavel_id',
        'observacao',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_previsao' => 'datetime',
        'data_conclusao' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsavel_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ProductionOrderStep::class);
    }

    /**
     * Calcula o progresso baseado nas etapas concluídas.
     */
    public function getProgressoAttribute(): int
    {
        $total = $this->stages()->count();
        if ($total === 0) return 0;

        $concluidas = $this->stages()->where('status', 'concluido')->count();
        return (int) round(($concluidas / $total) * 100);
    }
}
