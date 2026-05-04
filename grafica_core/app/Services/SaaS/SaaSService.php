<?php

declare(strict_types=1);

namespace App\Services\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-17 00:15
| Descrição: Serviço central de gestão de assinaturas e limites SaaS.
*/

use App\Models\SaaS\Assinatura;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SaaSService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly PlanService $planService,
    ) {}

    /**
     * Resolve o loja_id atual com fallback para usuário autenticado.
     * Lança exceção se não for possível determinar o tenant — previne
     * colisão de cache entre lojas em contextos sem tenant inicializado.
     */
    private function resolveLojaId(): int
    {
        $lojaId = $this->tenantContext->getLojaId()
            ?? \Illuminate\Support\Facades\Auth::user()?->loja_id;

        if (! $lojaId) {
            throw new \RuntimeException(
                'SaaSService: não foi possível determinar o tenant. ' .
                'Certifique-se de que TenantContext está inicializado antes de invocar este serviço.'
            );
        }

        return (int) $lojaId;
    }

    /**
     * Obtém a assinatura ativa da loja atual.
     * O filtro por loja_id é explícito para garantir isolamento mesmo
     * quando o global scope HasTenancy ainda não está ativo (queues, Artisan).
     */
    public function getAssinatura(): Assinatura
    {
        $lojaId   = $this->resolveLojaId();
        $cacheKey = "saas_assinatura_ativa_loja_{$lojaId}";

        return Cache::remember($cacheKey, 3600, function () use ($lojaId) {
            // Cláusula explícita — independente do global scope do HasTenancy
            $assinatura = Assinatura::with('plano')
                ->where('loja_id', $lojaId)
                ->latest()
                ->first();

            if (! $assinatura) {
                Log::warning("SaaSService: loja_id={$lojaId} sem assinatura ativa (normal durante onboarding).");

                return new Assinatura([
                    'loja_id'       => $lojaId,
                    'status'        => 'cancelada',
                    'trial_ends_at' => null,
                    'ends_at'       => null,
                ]);
            }

            return $assinatura;
        });
    }

    /**
     * Verifica se é possível adicionar um novo recurso (produto ou funcionário).
     */
    public function podeAdicionar(string $recurso): bool
    {
        $limitMap = [
            'produto' => 'max_produtos',
            'funcionario' => 'max_usuarios',
        ];

        $limitKey = $limitMap[$recurso] ?? null;
        if (! $limitKey) {
            return true;
        }

        try {
            return $this->planService->canConsumeLimit($limitKey, 1, $this->resolveLojaId());
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Limpa o cache da assinatura da loja atual.
     */
    public function limparCache(): void
    {
        $this->limparCachePorLojaId($this->resolveLojaId());
    }

    /**
     * Invalida explicitamente o cache de assinatura de uma loja específica.
     * Útil para o SuperAdmin após realizar alterações manuais.
     */
    public function limparCachePorLojaId(int $lojaId): void
    {
        Cache::forget("saas_assinatura_ativa_loja_{$lojaId}");
    }

    /**
     * Invalida todos os caches relacionados a uma loja específica.
     * Inclui cache de assinatura, tenant por host, e configurações.
     * 
     * Autoria: Abimael Borges | https://abimaelborges.adv.br | 2026-04-18
     */
    public function invalidarTodosOsCachesDaLoja(int $lojaId): void
    {
        $loja = \App\Models\Loja::find($lojaId);
        
        if ($loja) {
            $loja->invalidarTodosOsCaches();
        } else {
            // Fallback: limpa apenas o cache de assinatura se a loja não for encontrada
            $this->limparCachePorLojaId($lojaId);
        }
    }
}
