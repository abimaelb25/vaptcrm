<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 19:47 -03:00
*/

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemPedido extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\HasTenancy;

    protected $table = 'itens_pedido';

    protected $fillable = [
        'loja_id',
        'pedido_id',
        'produto_id',
        'descricao_item',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'caminho_arte',
        'servico_arte_incluso',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
