<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 03:50 (adição: HasTenancy)
*/

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTenancy;

class Depoimento extends Model
{
    use HasTenancy;
    protected $fillable = [
        'loja_id',
        'contexto',
        'nome_autor',
        'cargo_autor',
        'empresa_autor',
        'cidade_autor',
        'avatar_path',
        'depoimento_texto',
        'nota',
        'titulo',
        'destaque',
        'publicado',
        'ordem_exibicao',
    ];

    /**
     * Scopes para filtragem de contexto e status
     */
    public function scopeDaLoja($query)
    {
        return $query->where('contexto', 'loja');
    }

    public function scopeDaPlataforma($query)
    {
        return $query->withoutGlobalScope('loja')->where('contexto', 'plataforma');
    }

    public function scopePublicados($query)
    {
        return $query->where('publicado', true);
    }

    public function scopeDestaques($query)
    {
        return $query->where('destaque', true);
    }

    protected function casts(): array
    {
        return [
            'publicado' => 'boolean',
            'destaque' => 'boolean',
            'nota' => 'integer',
        ];
    }
}
