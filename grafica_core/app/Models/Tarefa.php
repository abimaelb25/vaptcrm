<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-16 (adicionado HasTenancy para isolamento multi-tenant)
*/

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarefa extends Model
{
    use SoftDeletes, HasTenancy;

    protected $table = 'tarefas';

    protected $fillable = [
        'loja_id',
        'titulo',
        'descricao',
        'responsavel_id',
        'solicitante_id',
        'status',
        'prioridade',
        'prazo',
        'setor',
    ];

    protected function casts(): array
    {
        return [
            'prazo' => 'datetime',
        ];
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsavel_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'solicitante_id');
    }
}
