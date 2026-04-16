<?php

declare(strict_types=1);

namespace App\Services\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:26
*/

use App\Models\SaaS\Assinatura;
use App\Models\SaaS\Plano;
use App\Models\Produto;
use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;

class SaaSService
{
    /**
     * Obtém a assinatura ativa da loja atual.
     */
    public function getAssinatura(): Assinatura
    {
        $tenantContext = app(\App\Services\SaaS\TenantContext::class);
        $lojaId = $tenantContext->getLojaId();
        
        if (!$lojaId) {
            $lojaId = \Illuminate\Support\Facades\Auth::user()?->loja_id;
        }

        $cacheKey = "saas_assinatura_ativa_loja_{$lojaId}";

        return Cache::remember($cacheKey, 3600, function () use ($lojaId) {
            // Busca a assinatura mais recente para garantir consistência em caso de trocas de plano
            $assinatura = Assinatura::with('plano')
                ->latest()
                ->first();

            if (! $assinatura) {
                // Log de aviso se não houver assinatura (no onboarding isso é normal até o provisionamento concluir)
                if ($lojaId) {
                    \Illuminate\Support\Facades\Log::warning("Loja ID {$lojaId} acessando sem assinatura ativa.");
                }

                return new Assinatura([
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
        $assinatura = $this->getAssinatura();
        
        if ($assinatura->expirada()) {
            return false;
        }

        $plano = $assinatura->plano;

        return match ($recurso) {
            'produto' => $this->validarLimiteProduto($plano),
            'funcionario' => $this->validarLimiteFuncionario($plano),
            default => true,
        };
    }

    private function validarLimiteProduto(Plano $plano): bool
    {
        if (! $plano->temLimiteProdutos()) {
            return true;
        }

        // HasTenancy garante que o count() seja apenas desta loja
        $count = Produto::where('ativo', true)->count();
        return $count < $plano->limite_produtos;
    }

    private function validarLimiteFuncionario(Plano $plano): bool
    {
        if (! $plano->temLimiteFuncionarios()) {
            return true;
        }

        // HasTenancy garante que o count() seja apenas desta loja
        $count = Usuario::where('ativo', true)->count();
        return $count < $plano->limite_funcionarios;
    }

    /**
     * Limpa o cache da assinatura da loja atual.
     */
    public function limparCache(): void
    {
        $lojaId = \Illuminate\Support\Facades\Auth::user()?->loja_id ?? session('loja_id');
        Cache::forget("saas_assinatura_ativa_loja_{$lojaId}");
    }
}
