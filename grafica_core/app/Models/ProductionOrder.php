<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-20 03:30 (Modificado)
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
        'item_pedido_id',
        'produto_id',
        'cliente_nome',
        'produto_nome',
        'quantidade',
        'valor_total',
        'status',
        'status_atual',
        'production_step_id',
        'prioridade',
        'data_inicio',
        'data_previsao',
        'data_finalizacao',
        'data_conclusao',
        'responsavel_id',
        'observacao',
        'observacoes',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'valor_total' => 'float',
        'data_inicio' => 'datetime',
        'data_previsao' => 'datetime',
        'data_finalizacao' => 'datetime',
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

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function itemPedido(): BelongsTo
    {
        return $this->belongsTo(ItemPedido::class, 'item_pedido_id');
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(ProductionStep::class, 'production_step_id')
            ->withDefault(['nome' => 'Sem etapa']);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ProductionOrderStep::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ProductionOrderHistory::class)
            ->orderByDesc('data_movimentacao');
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
