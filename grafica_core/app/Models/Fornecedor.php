<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:15
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class Fornecedor extends Model
{
    use SoftDeletes, HasTenancy;

    protected $table = 'fornecedores';

    protected $fillable = [
        'loja_id',
        'nome',
        'razao_social',
        'cnpj_cpf',
        'telefone',
        'whatsapp',
        'email',
        'endereco',
        'cidade',
        'uf',
        'observacao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(EstoqueMovimentacao::class);
    }
}
