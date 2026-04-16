<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 19:49 -03:00
*/

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoPedido extends Model
{
    use HasFactory, \App\Traits\HasTenancy;

    protected $table = 'historicos_pedido';

    protected $fillable = [
        'loja_id',
        'pedido_id',
        'status_anterior',
        'status_novo',
        'descricao',
        'usuario_id',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
