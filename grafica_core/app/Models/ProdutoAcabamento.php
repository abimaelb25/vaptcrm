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

class ProdutoAcabamento extends Model
{
    use \App\Traits\HasTenancy;

    protected $table = 'produto_acabamentos';

    protected $fillable = [
        'loja_id',
        'produto_id',
        'nome',
        'preco_ajuste',
        'prazo_ajuste',
        'ativo',
    ];

    protected $casts = [
        'preco_ajuste' => 'float',
        'prazo_ajuste' => 'integer',
        'ativo' => 'boolean',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
