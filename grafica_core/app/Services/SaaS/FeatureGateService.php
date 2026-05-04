<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Exceptions\SaaS\PlanFeatureNotAvailableException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class FeatureGateService
{
    public function __construct(
        private readonly PlanService $planService,
    ) {}

    public function allows(string $feature, ?int $lojaId = null): bool
    {
        $resolvedLojaId = $this->planService->resolveLojaId($lojaId);
        $cacheKey = "saas:feature_gate:{$resolvedLojaId}:{$feature}";
        $ttl = max(30, (int) config('saas.cache.feature_ttl_seconds', 180));

        return (bool) $this->rememberCached($cacheKey, $ttl, function () use ($feature, $resolvedLojaId): bool {
            $validation = $this->planService->validateTenantForAccess($resolvedLojaId);
            if (! $validation['valid']) {
                return false;
            }

            return $this->planService->hasFeature($feature, $resolvedLojaId);
        });
    }

    public function ensure(string $feature, ?int $lojaId = null): void
    {
        if ($this->allows($feature, $lojaId)) {
            return;
        }

        throw new PlanFeatureNotAvailableException("Recurso {$feature} indisponivel para o plano atual.");
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
