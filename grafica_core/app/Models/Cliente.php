<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-05 00:16 -03:00
*/

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Authorable, \App\Traits\HasTenancy;

    protected $table = 'clientes';

    protected $fillable = [
        'loja_id',
        'nome',
        'empresa',
        'tipo_pessoa',
        'cpf_cnpj',
        'avatar',
        'telefone',
        'whatsapp',
        'email',
        'endereco',
        'cidade',
        'data_nascimento',
        'observacoes',
        'origem_lead',
        'status',
        'created_by_id',
        'updated_by_id',
    ];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }

    public function contatos(): HasMany
    {
        return $this->hasMany(Contato::class, 'cliente_id');
    }
}
