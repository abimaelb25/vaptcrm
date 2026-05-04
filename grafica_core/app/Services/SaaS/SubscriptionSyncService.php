<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Models\Loja;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\Plano;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;

/**
 * Serviço de sincronização automática entre Loja e Assinatura SaaS.
 * 
 * Responsável por:
 * - Criar assinatura ao criar loja com plano
 * - Sincronizar plano entre loja e assinatura
 * - Manter coerência de status (trial, ativa, etc)
 * - Impedir estado intermediário (loja com plano mas sem assinatura)
 */
final class SubscriptionSyncService
{
    /**
     * Sincronizar assinatura para uma loja.
     * 
     * Cria assinatura se não existir, ou atualiza se houver divergência.
     * Executa em transação para atomicidade.
     * 
     * @throws RuntimeException se plano não existir ou estiver inativo
     * @throws LogicException se sincronização resultar em estado inválido
     */
    public function syncSubscriptionForStore(Loja $loja): ?Assinatura
    {
        // Se não tem plano, não faz nada
        if (!$loja->plano_id) {
            return null;
        }

        // Validar que plano existe e está ativo
        $plano = Plano::find($loja->plano_id);
        if (!$plano) {
            throw new RuntimeException(
                "Loja {$loja->id} refere-se a plano inexistente (ID: {$loja->plano_id})"
            );
        }

        if (!$plano->ativo) {
            throw new RuntimeException(
                "Loja {$loja->id} refere-se a plano inativo (ID: {$loja->plano_id})"
            );
        }

        // Usar transação para atomicidade
        return DB::transaction(function () use ($loja, $plano) {
            // Tenta encontrar assinatura existente
            $assinatura = Assinatura::where('loja_id', $loja->id)->first();

            if ($assinatura) {
                // Assinatura existe: sincronizar se plano mudou
                $this->syncExistingSubscription($assinatura, $loja, $plano);
                return $assinatura;
            }

            // Criar nova assinatura
            $created = $this->createNewSubscription($loja, $plano);
            $this->validateStoreHasValidSubscription($loja->fresh());

            return $created;
        });
    }

    /**
     * Sincronizar assinatura existente com novo estado da loja.
     */
    private function syncExistingSubscription(
        Assinatura $assinatura,
        Loja $loja,
        Plano $plano
    ): void {
        // Se plano mudou, atualizar assinatura
        if ($assinatura->plano_id !== $plano->id) {
            $assinatura->update([
                'plano_id' => $plano->id,
                'plan_version' => $plano->version,
                'plan_snapshot' => $this->createPlanSnapshot($plano),
                // Manter status atual, não forçar mudança
            ]);
        }

        // Se trial_ends_at mudou na loja, propagar
        $storeTrial = $loja->trial_ends_at?->toDateTimeString();
        $subTrial = $assinatura->trial_ends_at?->toDateTimeString();

        if ($storeTrial !== $subTrial) {
            $assinatura->update([
                'trial_ends_at' => $loja->trial_ends_at,
            ]);
        }
    }

    /**
     * Criar nova assinatura para uma loja.
     */
    private function createNewSubscription(Loja $loja, Plano $plano): Assinatura
    {
        $status = $loja->trial_ends_at ? Assinatura::STATUS_TRIAL : Assinatura::STATUS_ACTIVE;
        $renewsAt = match ($status) {
            Assinatura::STATUS_TRIAL => $loja->trial_ends_at,
            default => now()->addMonth(),
        };

        return Assinatura::create([
            'loja_id' => $loja->id,
            'plano_id' => $plano->id,
            'status' => $status,
            'billing_cycle' => Assinatura::BILLING_MONTHLY,
            'plan_version' => $plano->version ?? 1,
            'plan_snapshot' => $this->createPlanSnapshot($plano),
            'trial_ends_at' => $loja->trial_ends_at,
            'renews_at' => $renewsAt,
            'next_billing_at' => $renewsAt,
        ]);
    }

    /**
     * Criar snapshot do plano para referência histórica.
     */
    private function createPlanSnapshot(Plano $plano): array
    {
        return [
            'plano_id' => $plano->id,
            'nome' => $plano->nome,
            'slug' => $plano->slug,
            'version' => $plano->version ?? 1,
            'preco_mensal' => $plano->preco_mensal,
            'price_monthly' => $plano->price_monthly ?? $plano->preco_mensal,
            'price_yearly' => $plano->price_yearly,
        ];
    }

    /**
     * Validar que loja tem assinatura válida.
     * 
     * Lança exceção se inconsistência for detectada.
     */
    public function validateStoreHasValidSubscription(Loja $loja): void
    {
        // Se não tem plano, não precisa de assinatura
        if (!$loja->plano_id) {
            return;
        }

        $assinatura = Assinatura::where('loja_id', $loja->id)->first();
        if (!$assinatura) {
            throw new RuntimeException(
                "Loja {$loja->id} ({$loja->nome_fantasia}) tem plano vinculado " .
                "mas nenhuma assinatura SaaS correspondente"
            );
        }

        // Validar coerência
        if ($assinatura->plano_id !== $loja->plano_id) {
            throw new RuntimeException(
                "Loja {$loja->id}: Plano da loja ({$loja->plano_id}) " .
                "diverge da assinatura ({$assinatura->plano_id})"
            );
        }
    }

    /**
     * Encontrar lojas órfãs sem assinatura.
     * 
     * Retorna coleção de lojas que têm plano_id mas não têm assinatura.
     */
    public function findOrphanStores()
    {
        return Loja::whereNotNull('plano_id')
            ->whereNotIn('id', function ($query) {
                $query->select('loja_id')
                    ->from('saas_assinaturas');
            })
            ->get();
    }
}
