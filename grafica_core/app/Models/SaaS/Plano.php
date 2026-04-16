<?php

declare(strict_types=1);

namespace App\Models\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:20
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Plano extends Model
{
    use SoftDeletes;

    protected $table = 'saas_planos';

    protected $fillable = [
        'nome',
        'slug',
        'preco_mensal',
        'stripe_price_id',
        'limite_produtos',
        'limite_funcionarios',
        'recursos_premium',
        'ativo',
    ];

    protected $casts = [
        'preco_mensal' => 'float',
        'limite_produtos' => 'integer',
        'limite_funcionarios' => 'integer',
        'recursos_premium' => 'array',
        'ativo' => 'boolean',
    ];

    /**
     * Verifica se o plano tem algum limite específico de funcionários.
     */
    public function temLimiteFuncionarios(): bool
    {
        return $this->limite_funcionarios !== null;
    }

    /**
     * Verifica se o plano tem algum limite específico de produtos.
     */
    public function temLimiteProdutos(): bool
    {
        return $this->limite_produtos !== null;
    }

    /**
     * Define o campo de busca padrão para as rotas do Laravel.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Relacionamento com as assinaturas vinculadas a este plano.
     */
    public function assinaturas(): HasMany
    {
        return $this->hasMany(Assinatura::class, 'plano_id');
    }

    /**
     * Lojas que possuem este plano.
     */
    public function lojas(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Loja::class,
            Assinatura::class,
            'plano_id', // Chave em assinaturas
            'id',       // Chave em lojas
            'id',       // Chave em planos
            'loja_id'   // Chave em assinaturas
        );
    }

    /**
     * Calcula o total de assinantes ativos neste plano.
     */
    public function totalAssinantesAtivos(): int
    {
        return $this->assinaturas()
            ->whereIn('status', ['active', 'trial', 'past_due'])
            ->count();
    }
}
