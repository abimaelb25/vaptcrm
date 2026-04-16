<?php

declare(strict_types=1);

namespace App\Services\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 15/04/2026 18:55
| Descrição: Controle de acesso a recursos financeiros baseados no plano SaaS.
*/

use App\Models\Loja;

class FinancePlanService
{
    /**
     * Verifica o nível financeiro permitido.
     * Retornos: 'basico', 'pro', 'premium'
     */
    public function getFinanceLevel(): string
    {
        $usuario = auth()->user();
        if (!$usuario || !$usuario->loja_id) return 'basico';

        $loja = Loja::find($usuario->loja_id);
        if (!$loja) return 'basico';

        $assinaturaAtiva = $loja->assinaturaAtiva();
        $plano = $assinaturaAtiva ? $assinaturaAtiva->plano : $loja->plano;
        
        if (!$plano) return 'basico';

        $recursos = is_array($plano->recursos_premium) ? $plano->recursos_premium : [];
        
        if (in_array('financeiro_premium', $recursos)) return 'premium';
        if (in_array('financeiro_pro', $recursos)) return 'pro';
        
        return 'basico';
    }

    public function canUsePro(): bool
    {
        return in_array($this->getFinanceLevel(), ['pro', 'premium']);
    }

    public function canUsePremium(): bool
    {
        return $this->getFinanceLevel() === 'premium';
    }
}
