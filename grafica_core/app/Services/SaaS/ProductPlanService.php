<?php

declare(strict_types=1);

namespace App\Services\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 15/04/2026 14:45
*/

class ProductPlanService
{
    /**
     * Verifica se a loja possui permissão para usar recursos Premium (Ex: Faixas, Materiais, Acabamentos).
     * No plano Básico (Gratuito/Entry), retorna falso para forçar Upgrade.
     */
    public function canUseAdvancedFeatures(): bool
    {
        $usuario = auth()->user();
        if (!$usuario || !$usuario->loja_id) {
            return false;
        }

        $loja = \App\Models\Loja::find($usuario->loja_id);
        if (!$loja) return false;

        // Tenta obter pela assinatura formal, senão usa o plano direto da conta (Fallback)
        $assinaturaAtiva = $loja->assinaturaAtiva();
        $plano = $assinaturaAtiva ? $assinaturaAtiva->plano : $loja->plano;
        
        if (!$plano) return false;

        // Se o plano tiver recursos_premium definidos como array
        $recursos = is_array($plano->recursos_premium) ? $plano->recursos_premium : [];
        
        return in_array('produtos_configuraveis', $recursos) || in_array('produtos_tecnicos', $recursos);
    }

    /**
     * Verifica se pode cadastrar produtos avançados (Nível 3).
     */
    public function canUseTechnicalModule(): bool
    {
        $usuario = auth()->user();
        if (!$usuario || !$usuario->loja_id) return false;

        $loja = \App\Models\Loja::find($usuario->loja_id);
        if (!$loja) return false;

        $assinaturaAtiva = $loja->assinaturaAtiva();
        $plano = $assinaturaAtiva ? $assinaturaAtiva->plano : $loja->plano;
        
        if (!$plano) return false;

        $recursos = is_array($plano->recursos_premium) ? $plano->recursos_premium : [];
        
        return in_array('produtos_tecnicos', $recursos);
    }
}
