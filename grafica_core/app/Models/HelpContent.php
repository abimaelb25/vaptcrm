<?php

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
*/

use Illuminate\Database\Eloquent\Model;

class HelpContent extends Model
{
    protected $fillable = [
        'titulo',
        'tipo',
        'descricao',
        'youtube_url',
        'thumbnail',
        'ordem',
        'destaque',
        'publicado',
        'required_plan',
    ];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
            'destaque' => 'boolean',
            'publicado' => 'boolean',
        ];
    }

    public function scopePublicados($query)
    {
        return $query->where('publicado', true);
    }
}
