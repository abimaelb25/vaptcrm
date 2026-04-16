<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:25
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenancy;

class EstoqueMovimentacao extends Model
{
    use HasTenancy;

    protected $table = 'estoque_movimentacoes';

    protected $fillable = [
        'loja_id',
        'insumo_id',
        'tipo',
        'origem',
        'quantidade',
        'custo_unitario',
        'valor_total',
        'fornecedor_id',
        'referencia_id',
        'descricao',
        'data_movimentacao',
        'usuario_id',
    ];

    protected $casts = [
        'quantidade' => 'float',
        'custo_unitario' => 'float',
        'valor_total' => 'float',
        'data_movimentacao' => 'datetime',
    ];

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
