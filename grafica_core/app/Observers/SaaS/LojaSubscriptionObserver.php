<?php

declare(strict_types=1);

namespace App\Observers\SaaS;

use App\Models\Loja;
use App\Services\SaaS\SubscriptionSyncService;

/**
 * Observer para sincronizar assinatura SaaS ao criar/atualizar Loja.
 * 
 * Garante que toda loja com plano_id vinculado tenha uma assinatura correspondente.
 * Executa automaticamente sem necessidade de intervenção do controller.
 */
final class LojaSubscriptionObserver
{
    public function __construct(
        private SubscriptionSyncService $syncService
    ) {}

    /**
     * Sincronizar assinatura após criar loja.
     * 
     * Se loja foi criada com plano_id, cria assinatura automaticamente.
     */
    public function created(Loja $loja): void
    {
        if ($loja->plano_id) {
            $this->syncService->syncSubscriptionForStore($loja);
        }
    }

    /**
     * Sincronizar assinatura após atualizar loja.
     * 
     * Se plano ou trial_ends_at mudaram, sincronizar com assinatura.
     */
    public function updated(Loja $loja): void
    {
        // Se mudou plano ou trial_ends_at, sincronizar
        if ($loja->isDirty('plano_id') || $loja->isDirty('trial_ends_at')) {
            if ($loja->plano_id) {
                $this->syncService->syncSubscriptionForStore($loja);
            }
        }
    }
}
