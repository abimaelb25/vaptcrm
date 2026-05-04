<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Exceptions\SaaS\PlanFeatureNotAvailableException;
use App\Exceptions\SaaS\PlanLimitExceededException;
use App\Exceptions\SaaS\PlanSubscriptionInactiveException;
use App\Models\Loja;
use App\Models\Pedido;
use App\Models\ProductionOrder;
use App\Models\Produto;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\UsageLog;
use App\Models\Usuario;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PlanService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    public function resolveLojaId(?int $lojaId = null): int
    {
        if ($lojaId) {
            return $lojaId;
        }

        $resolved = $this->tenantContext->getLojaId() ?? Auth::user()?->loja_id;

        if (! $resolved) {
            throw new \RuntimeException('Tenant nao resolvido para validar plano.');
        }

        return (int) $resolved;
    }

    public function getCurrentSubscription(?int $lojaId = null): Assinatura
    {
        $resolvedLojaId = $this->resolveLojaId($lojaId);
        $subscriptionFingerprint = (string) (Assinatura::query()
            ->where('loja_id', $resolvedLojaId)
            ->max('updated_at') ?? 'none');
        $cacheKey = 'saas:subscription:' . $resolvedLojaId . ':' . sha1($subscriptionFingerprint);
        $ttl = max(30, (int) config('saas.cache.subscription_ttl_seconds', 180));

        return $this->rememberCached($cacheKey, $ttl, function () use ($resolvedLojaId): Assinatura {
            $assinatura = Assinatura::query()
                ->with(['plano.features', 'plano.limits'])
                ->where('loja_id', $resolvedLojaId)
                ->latest('id')
                ->first();

            if ($assinatura) {
                return $assinatura;
            }

            return new Assinatura([
                'loja_id' => $resolvedLojaId,
                'status' => Assinatura::STATUS_CANCELADA,
                'plano_id' => Loja::query()->where('id', $resolvedLojaId)->value('plano_id'),
            ]);
        });
    }

    /**
     * @return array{valid: bool, reason: string|null, message: string|null, cta_url: string|null}
     */
    public function validateTenantForAccess(?int $lojaId = null): array
    {
        $resolvedLojaId = $this->resolveLojaId($lojaId);
        $loja = Loja::query()->find($resolvedLojaId);

        if (! $loja) {
            return [
                'valid' => false,
                'reason' => 'loja_inexistente',
                'message' => 'Loja nao encontrada. Faça login novamente ou contate o suporte.',
                'cta_url' => '/painel/assinatura',
            ];
        }

        $assinatura = $this->getCurrentSubscription($resolvedLojaId);
        $effectivePlanId = $assinatura->plano_id ?: $loja->plano_id;

        if (! $effectivePlanId) {
            return [
                'valid' => false,
                'reason' => 'loja_sem_plano',
                'message' => 'Sua loja ainda nao possui plano ativo. Escolha um plano para continuar.',
                'cta_url' => '/painel/assinatura',
            ];
        }

        $plano = $assinatura->plano;

        if (! $plano || ! $plano->exists) {
            return [
                'valid' => false,
                'reason' => 'plano_inexistente',
                'message' => 'O plano vinculado a sua loja nao existe mais. Selecione um novo plano.',
                'cta_url' => '/painel/assinatura',
            ];
        }

        if (! (bool) $plano->ativo) {
            return [
                'valid' => false,
                'reason' => 'plano_inativo',
                'message' => 'Seu plano atual foi desativado. Faça upgrade para liberar o acesso.',
                'cta_url' => '/painel/assinatura',
            ];
        }

        if ($assinatura->suspensa()) {
            return [
                'valid' => false,
                'reason' => 'plano_suspenso',
                'message' => 'Sua assinatura esta suspensa. Regularize para reativar o sistema.',
                'cta_url' => '/painel/assinatura',
            ];
        }

        if ($assinatura->expirada()) {
            return [
                'valid' => false,
                'reason' => 'plano_expirado',
                'message' => 'Seu plano expirou. Faça upgrade para retomar o uso completo do sistema.',
                'cta_url' => '/painel/assinatura',
            ];
        }

        return [
            'valid' => true,
            'reason' => null,
            'message' => null,
            'cta_url' => null,
        ];
    }

    public function hasFeature(string $featureKey, ?int $lojaId = null): bool
    {
        $resolvedLojaId = $this->resolveLojaId($lojaId);
        $validation = $this->validateTenantForAccess($resolvedLojaId);
        if (! $validation['valid']) {
            return false;
        }

        $cacheKey = "saas:feature:{$resolvedLojaId}:{$featureKey}";
        $ttl = max(30, (int) config('saas.cache.feature_ttl_seconds', 180));

        return (bool) $this->rememberCached($cacheKey, $ttl, function () use ($featureKey, $resolvedLojaId): bool {
            $assinatura = $this->getCurrentSubscription($resolvedLojaId);
            if (! $assinatura->ativa()) {
                return false;
            }

            $plano = $assinatura->plano;
            if (! $plano) {
                return false;
            }

            if ($this->isLimitOverrideEnabled($assinatura->loja_id)) {
                return true;
            }

            return $plano->featureEnabled($featureKey);
        });
    }

    public function ensureSubscriptionIsOperational(?int $lojaId = null): void
    {
        $validation = $this->validateTenantForAccess($lojaId);

        if (! $validation['valid']) {
            throw new PlanSubscriptionInactiveException($validation['message'] ?? 'Assinatura inativa ou expirada. Regularize o plano para continuar.');
        }
    }

    public function ensureFeature(string $featureKey, ?int $lojaId = null): void
    {
        if (! $this->hasFeature($featureKey, $lojaId)) {
            throw new PlanFeatureNotAvailableException("Recurso {$featureKey} nao disponivel no plano atual.");
        }
    }

    public function getLimit(string $limitKey, ?int $lojaId = null): ?int
    {
        $resolvedLojaId = $this->resolveLojaId($lojaId);
        $cacheKey = "saas:limit:{$resolvedLojaId}:{$limitKey}";
        $ttl = max(30, (int) config('saas.cache.limit_ttl_seconds', 180));

        return $this->rememberCached($cacheKey, $ttl, function () use ($resolvedLojaId, $limitKey): ?int {
            $assinatura = $this->getCurrentSubscription($resolvedLojaId);
            $plano = $assinatura->plano;

            if (! $plano) {
                return null;
            }

            return $plano->resolveLimit($limitKey);
        });
    }

    public function currentUsage(string $limitKey, ?int $lojaId = null): int
    {
        $resolvedLojaId = $this->resolveLojaId($lojaId);

        return match ($limitKey) {
            'max_produtos' => Produto::withoutGlobalScope('loja')->where('loja_id', $resolvedLojaId)->where('ativo', true)->count(),
            // max_usuarios representa acessos sistêmicos ativos (usuarios.ativo)
            // e NAO quantidade de colaboradores cadastrados em employees.
            'max_usuarios' => Usuario::withoutGlobalScope('loja')
                ->where('loja_id', $resolvedLojaId)
                ->where('ativo', true)
                ->count(),
            'max_pedidos_mes' => Pedido::withoutGlobalScope('loja')
                ->where('loja_id', $resolvedLojaId)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'max_ops_simultaneas' => ProductionOrder::withoutGlobalScope('loja')
                ->where('loja_id', $resolvedLojaId)
                ->whereNull('data_finalizacao')
                ->count(),
            'max_producao_ativa' => ProductionOrder::withoutGlobalScope('loja')
                ->where('loja_id', $resolvedLojaId)
                ->where('status', 'em_producao')
                ->whereNull('data_finalizacao')
                ->count(),
            'max_storage_mb' => (int) floor(((int) Loja::query()->where('id', $resolvedLojaId)->value('storage_used_bytes')) / 1024 / 1024),
            default => 0,
        };
    }

    public function canConsumeLimit(string $limitKey, int $increment = 1, ?int $lojaId = null): bool
    {
        $resolvedLojaId = $this->resolveLojaId($lojaId);

        if (! $this->isSubscriptionOperational($resolvedLojaId)) {
            return false;
        }

        if ($this->isLimitOverrideEnabled($resolvedLojaId)) {
            return true;
        }

        $limit = $this->getLimit($limitKey, $resolvedLojaId);
        if ($limit === null) {
            return true;
        }

        $usage = $this->currentUsage($limitKey, $resolvedLojaId);

        return ($usage + $increment) <= $limit;
    }

    public function ensureLimit(string $limitKey, int $increment = 1, ?int $lojaId = null): void
    {
        $resolvedLojaId = $this->resolveLojaId($lojaId);

        $this->ensureSubscriptionIsOperational($resolvedLojaId);

        if (! $this->canConsumeLimit($limitKey, $increment, $resolvedLojaId)) {
            $limit = $this->getLimit($limitKey, $resolvedLojaId);
            $usage = $this->currentUsage($limitKey, $resolvedLojaId);

            throw new PlanLimitExceededException("Limite {$limitKey} excedido ({$usage}/{$limit}).");
        }
    }

    public function recordUsage(string $eventType, array $payload, ?int $lojaId = null): void
    {
        $resolvedLojaId = $this->resolveLojaId($lojaId);
        $assinatura = $this->getCurrentSubscription($resolvedLojaId);

        UsageLog::create([
            'loja_id' => $resolvedLojaId,
            'assinatura_id' => $assinatura->exists ? $assinatura->id : null,
            'event_type' => $eventType,
            'feature_key' => $payload['feature_key'] ?? null,
            'limit_key' => $payload['limit_key'] ?? null,
            'delta' => (int) ($payload['delta'] ?? 0),
            'used_total' => isset($payload['used_total']) ? (int) $payload['used_total'] : null,
            'metadata' => $payload['metadata'] ?? null,
            'occurred_at' => now(),
        ]);
    }

    public function isStorageWithinLimit(?int $incomingBytes = null, ?int $lojaId = null): bool
    {
        return (bool) $this->evaluateStoragePolicy($incomingBytes, $lojaId)['allowed'];
    }

    public function evaluateStoragePolicy(?int $incomingBytes = null, ?int $lojaId = null): array
    {
        $resolvedLojaId = $this->resolveLojaId($lojaId);
        $incoming = max(0, (int) $incomingBytes);
        $snapshot = $this->usageSnapshot('max_storage_mb', $resolvedLojaId, $incoming);

        if ($snapshot['limit'] === null) {
            return [
                'allowed' => true,
                'level' => 'free',
                'message' => null,
                'snapshot' => $snapshot,
            ];
        }

        $warnAt = (int) config('saas.storage.warn_threshold_percent', 80);
        $criticalAt = (int) config('saas.storage.critical_threshold_percent', 95);
        $hardBlockAt = (int) config('saas.storage.hard_block_threshold_percent', 100);
        $softBlockMinBytes = (int) config('saas.storage.soft_block_min_upload_bytes', 5 * 1024 * 1024);

        if ($snapshot['future_percent'] >= $hardBlockAt) {
            return [
                'allowed' => false,
                'level' => 'blocked',
                'message' => 'Limite de armazenamento atingido. Faça upgrade para continuar enviando arquivos.',
                'snapshot' => $snapshot,
            ];
        }

        if ($snapshot['percent'] >= $criticalAt && $incoming >= $softBlockMinBytes) {
            return [
                'allowed' => false,
                'level' => 'soft_blocked',
                'message' => 'Armazenamento em estado crítico. Uploads grandes foram bloqueados temporariamente. Faça upgrade para liberar.',
                'snapshot' => $snapshot,
            ];
        }

        if ($snapshot['percent'] >= $criticalAt) {
            return [
                'allowed' => true,
                'level' => 'critical',
                'message' => 'Armazenamento crítico. Recomendamos upgrade imediato para evitar bloqueio.',
                'snapshot' => $snapshot,
            ];
        }

        if ($snapshot['percent'] >= $warnAt) {
            return [
                'allowed' => true,
                'level' => 'warning',
                'message' => 'Você está próximo do limite de armazenamento. Considere upgrade de plano.',
                'snapshot' => $snapshot,
            ];
        }

        return [
            'allowed' => true,
            'level' => 'normal',
            'message' => null,
            'snapshot' => $snapshot,
        ];
    }

    public function usageSnapshot(string $limitKey, ?int $lojaId = null, int $incoming = 0): array
    {
        $resolvedLojaId = $this->resolveLojaId($lojaId);
        $usage = $this->currentUsage($limitKey, $resolvedLojaId);
        $limit = $this->getLimit($limitKey, $resolvedLojaId);
        $futureUsage = max(0, $usage + max(0, $incoming));

        $percent = $limit ? min(100.0, round(($usage / $limit) * 100, 2)) : 0.0;
        $futurePercent = $limit ? min(100.0, round(($futureUsage / $limit) * 100, 2)) : 0.0;

        return [
            'limit_key' => $limitKey,
            'usage' => $usage,
            'limit' => $limit,
            'remaining' => $limit !== null ? max(0, $limit - $usage) : null,
            'percent' => $percent,
            'future_usage' => $futureUsage,
            'future_percent' => $futurePercent,
        ];
    }

    public function usageDashboard(?int $lojaId = null): array
    {
        $resolvedLojaId = $this->resolveLojaId($lojaId);

        return [
            'produtos' => $this->usageSnapshot('max_produtos', $resolvedLojaId),
            'usuarios' => $this->usageSnapshot('max_usuarios', $resolvedLojaId),
            'pedidos_mes' => $this->usageSnapshot('max_pedidos_mes', $resolvedLojaId),
            'storage_mb' => $this->usageSnapshot('max_storage_mb', $resolvedLojaId),
            'storage_policy' => $this->evaluateStoragePolicy(0, $resolvedLojaId),
        ];
    }

    private function isLimitOverrideEnabled(int $lojaId): bool
    {
        $loja = Loja::query()->find($lojaId);

        return (bool) $loja?->limitesDesbloqueados();
    }

    private function isSubscriptionOperational(int $lojaId): bool
    {
        return (bool) $this->validateTenantForAccess($lojaId)['valid'];
    }

    private function cache(): Repository
    {
        $preferredStore = (string) config('saas.cache.store', 'redis');

        try {
            return Cache::store($preferredStore);
        } catch (\Throwable) {
            return Cache::store(config('cache.default'));
        }
    }

    private function rememberCached(string $key, int $ttlSeconds, callable $callback): mixed
    {
        if (app()->environment('testing')) {
            return $callback();
        }

        $expiresAt = now()->addSeconds(max(30, $ttlSeconds));

        try {
            return $this->cache()->remember($key, $expiresAt, $callback);
        } catch (\Throwable) {
            foreach (['file', 'array'] as $fallbackStore) {
                try {
                    return Cache::store($fallbackStore)->remember($key, $expiresAt, $callback);
                } catch (\Throwable) {
                    // tenta o proximo fallback
                }
            }
        }

        return $callback();
    }
}
