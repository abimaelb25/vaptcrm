<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:20
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class Insumo extends Model
{
    use SoftDeletes, HasTenancy;

    protected $fillable = [
        'loja_id',
        'nome',
        'codigo_interno',
        'categoria',
        'unidade_medida',
        'estoque_atual',
        'estoque_minimo',
        'estoque_maximo',
        'custo_medio',
        'ultimo_custo',
        'ativo',
        'observacao',
    ];

    protected $casts = [
        'estoque_atual' => 'float',
        'estoque_minimo' => 'float',
        'estoque_maximo' => 'float',
        'custo_medio' => 'float',
        'ultimo_custo' => 'float',
        'ativo' => 'boolean',
    ];

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(EstoqueMovimentacao::class);
    }

    /**
     * Verifica se o insumo está com estoque baixo.
     */
    public function estaComEstoqueBaixo(): bool
    {
        return $this->estoque_atual <= $this->estoque_minimo;
    }

    /**
     * Retorna a cor/status formatado.
     */
    public function getStatusEstoqueAttribute(): string
    {
        if ($this->estoque_atual <= 0) return 'crítico';
        if ($this->estaComEstoqueBaixo()) return 'baixo';
        return 'ok';
    }
}
