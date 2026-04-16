<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-10 17:29 -03:00
*/

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\HasTenancy;

    protected $table = 'categorias';

    protected $fillable = [
        'loja_id',
        'nome',
        'slug',
        'descricao',
        'texto_destaque',
        'banner',
        'ordem_exibicao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem_exibicao' => 'integer',
    ];

    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class, 'categoria_id');
    }

    public function produtosAtivos(): HasMany
    {
        return $this->hasMany(Produto::class, 'categoria_id')
            ->where('ativo', true)
            ->whereIn('visibilidade', ['publico', 'ambos']);
    }
}
