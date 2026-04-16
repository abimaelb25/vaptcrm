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

class ProdutoMaterial extends Model
{
    use \App\Traits\HasTenancy;

    protected $table = 'produto_materiais';

    protected $fillable = [
        'loja_id',
        'produto_id',
        'nome',
        'preco_ajuste',
        'ativo',
    ];

    protected $casts = [
        'preco_ajuste' => 'float',
        'ativo' => 'boolean',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
