<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-06 01:10 -03:00
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    use \App\Traits\HasTenancy;

    protected $table = 'auditorias';

    protected $fillable = [
        'loja_id',
        'usuario_id',
        'modulo',
        'acao',
        'registro_id',
        'ip_address',
        'user_agent',
        'valores_antigos',
        'valores_novos',
    ];

    protected function casts(): array
    {
        return [
            'valores_antigos' => 'array',
            'valores_novos' => 'array',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
