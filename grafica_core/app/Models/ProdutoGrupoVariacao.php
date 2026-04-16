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
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdutoGrupoVariacao extends Model
{
    use \App\Traits\HasTenancy;

    protected $table = 'produto_grupos_variacao';

    protected $fillable = [
        'loja_id',
        'produto_id',
        'nome_grupo',
        'tipo_exibicao',
        'obrigatorio',
        'ordem',
    ];

    protected $casts = [
        'obrigatorio' => 'boolean',
        'ordem' => 'integer',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function opcoes(): HasMany
    {
        return $this->hasMany(ProdutoOpcaoVariacao::class, 'grupo_id')->orderBy('ordem');
    }
}
