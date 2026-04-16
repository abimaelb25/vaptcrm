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

class ProdutoOpcaoVariacao extends Model
{
    protected $table = 'produto_opcoes_variacao';

    protected $fillable = [
        'grupo_id',
        'nome_opcao',
        'acrescimo_preco',
        'acrescimo_custo',
        'acrescimo_prazo',
        'ordem',
    ];

    protected $casts = [
        'acrescimo_preco' => 'float',
        'acrescimo_custo' => 'float',
        'acrescimo_prazo' => 'integer',
        'ordem' => 'integer',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(ProdutoGrupoVariacao::class, 'grupo_id');
    }
}
