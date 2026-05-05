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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class Plano extends Model
{
    use SoftDeletes;

    public const OFFICIAL_OPERATIONAL_SLUGS = ['bronze', 'prata', 'ouro', 'diamante'];

    private static array $planColumnCache = [];

    protected $table = 'saas_planos';

    protected $fillable = [
        'nome',
        'slug',
        'legacy_slug',
        'version',
        'preco_mensal',
        'price_monthly',
        'price_yearly',
        'trial_days',
        'stripe_price_id',
        'stripe_price_yearly_id',
        'limite_produtos',
        'limite_funcionarios',
        'recursos_premium',
        'ativo',
        'is_legacy',
        'visivel_publicamente',
        'ordem_exibicao',
    ];

    protected $casts = [
        'preco_mensal' => 'float',
        'price_monthly' => 'float',
        'price_yearly' => 'float',
        'trial_days' => 'integer',
        'limite_produtos' => 'integer',
        'limite_funcionarios' => 'integer',
        'recursos_premium' => 'array',
        'ativo' => 'boolean',
        'version' => 'integer',
        'is_legacy' => 'boolean',
        'visivel_publicamente' => 'boolean',
        'ordem_exibicao' => 'integer',
    ];

    public function scopeOperational(Builder $query): Builder
    {
        $query->where('ativo', true)
            ->whereIn('slug', self::OFFICIAL_OPERATIONAL_SLUGS);

        if (self::hasPlanColumn('is_legacy')) {
            $query->where('is_legacy', false);
        }

        return $query;
    }

    public function scopePublicVisible(Builder $query): Builder
    {
        $query->operational();

        if (self::hasPlanColumn('visivel_publicamente')) {
            $query->where('visivel_publicamente', true);
        }

        if (self::hasPlanColumn('ordem_exibicao')) {
            $query->orderBy('ordem_exibicao');
        }

        return $query->orderBy('preco_mensal');
    }

    public function scopeCommercialOrder(Builder $query): Builder
    {
        if (self::hasPlanColumn('ordem_exibicao')) {
            $query->orderBy('ordem_exibicao');
        }

        return $query->orderBy('preco_mensal')->orderBy('nome');
    }

    public function isOperationalOffer(): bool
    {
        return (bool) $this->ativo
            && (! self::hasPlanColumn('is_legacy') || ! (bool) $this->is_legacy)
            && (! self::hasPlanColumn('visivel_publicamente') || (bool) $this->visivel_publicamente)
            && in_array($this->slug, self::OFFICIAL_OPERATIONAL_SLUGS, true);
    }

    private static function hasPlanColumn(string $column): bool
    {
        if (! array_key_exists($column, self::$planColumnCache)) {
            self::$planColumnCache[$column] = Schema::hasColumn('saas_planos', $column);
        }

        return self::$planColumnCache[$column];
    }

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

    public function features(): HasMany
    {
        return $this->hasMany(PlanoFeature::class, 'plano_id');
    }

    public function limits(): HasMany
    {
        return $this->hasMany(PlanoLimit::class, 'plano_id');
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

    public function commercialMonthlyPrice(): float
    {
        return (float) ($this->price_monthly ?? $this->preco_mensal ?? 0.0);
    }

    public function commercialYearlyPrice(): ?float
    {
        if ($this->price_yearly === null) {
            return null;
        }

        return (float) $this->price_yearly;
    }

    public function featureEnabled(string $key): bool
    {
        $feature = $this->features->firstWhere('feature_key', $key);
        if ($feature) {
            return (bool) $feature->enabled;
        }

        $legacy = Arr::get($this->recursos_premium ?? [], $key);
        if ($legacy !== null) {
            return (bool) $legacy;
        }

        // Compatibilidade: se o feature nao estiver explicitamente configurado, mantemos liberado.
        return true;
    }

    public function resolveLimit(string $key): ?int
    {
        $limit = $this->limits->firstWhere('limit_key', $key);
        if ($limit) {
            return $limit->limit_value !== null ? (int) $limit->limit_value : null;
        }

        return match ($key) {
            'max_produtos' => $this->limite_produtos,
            'max_usuarios' => $this->limite_funcionarios,
            default => null,
        };
    }
}
