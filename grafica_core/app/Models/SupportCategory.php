<?php

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
*/

use Illuminate\Database\Eloquent\Model;

class SupportCategory extends Model
{
    protected $fillable = [
        'nome',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class, 'categoria_id');
    }
}
