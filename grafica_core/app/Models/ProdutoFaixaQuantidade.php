<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 15/04/2026 14:40
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutoFaixaQuantidade extends Model
{
    use \App\Traits\HasTenancy;

    protected $table = 'produto_faixas_quantidade';

    protected $fillable = [
        'loja_id',
        'produto_id',
        'quantidade_minima',
        'preco_unitario',
        'custo_unitario',
    ];

    protected $casts = [
        'quantidade_minima' => 'integer',
        'preco_unitario' => 'float',
        'custo_unitario' => 'float',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
