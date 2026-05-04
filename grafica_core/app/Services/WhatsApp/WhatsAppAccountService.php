<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Models\Loja;
use App\Models\SaaS\PlanoLimit;
use App\Models\WhatsApp\WhatsAppAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Manages WhatsApp account onboarding, credential storage and plan limits.
 *
 * Security: access_token is always stored via the model's setter which
 * uses Crypt::encryptString(). The raw token NEVER touches the DB directly.
 */
class WhatsAppAccountService
{
    // Limit key used in saas_plano_limits
    public const LIMIT_KEY_ACCOUNTS  = 'whatsapp_accounts';
    public const LIMIT_KEY_MESSAGES  = 'whatsapp_messages_month';

    // -------------------------------------------------------------------------
    // Onboarding
    // -------------------------------------------------------------------------

    /**
     * Complete the embedded-signup onboarding and persist the WhatsApp account.
     *
     * Called after the frontend OAuth/embedded-signup flow returns the credentials.
     *
     * @param  Loja   $loja
     * @param  array{
     *   waba_id: string,
     *   phone_number_id: string,
     *   phone_number: string,
     *   display_name: string,
     *   access_token: string,
     *   business_id?: string,
     * } $data
     */
    public function onboard(Loja $loja, array $data): WhatsAppAccount
    {
        $this->assertAccountLimitNotExceeded($loja);

        return DB::transaction(function () use ($loja, $data) {
            // If first account, mark as primary
            $isPrimary = WhatsAppAccount::forLoja($loja->id)->count() === 0;

            $account = new WhatsAppAccount();
            $account->loja_id         = $loja->id;
            $account->provider        = WhatsAppAccount::PROVIDER_META_CLOUD;
            $account->waba_id         = $data['waba_id'];
            $account->phone_number_id = $data['phone_number_id'];
            $account->phone_number    = $data['phone_number'];
            $account->display_name    = $data['display_name'] ?? null;
            $account->business_id     = $data['business_id'] ?? null;
            $account->status          = WhatsAppAccount::STATUS_ACTIVE;
            $account->is_primary      = $isPrimary;
            $account->connected_at    = now();
            $account->webhook_verify_token = Str::random(40);

            // Uses model mutator → Crypt::encryptString
            $account->access_token = $data['access_token'];

            $account->save();

            return $account;
        });
    }

    /**
     * Rotate the access token for an account (e.g. after a token refresh).
     */
    public function rotateToken(WhatsAppAccount $account, string $newToken): void
    {
        // Uses model mutator → Crypt::encryptString
        $account->access_token = $newToken;
        $account->save();
    }

    /**
     * Disconnect / soft-delete a WhatsApp account for a loja.
     */
    public function disconnect(WhatsAppAccount $account): void
    {
        $account->update(['status' => WhatsAppAccount::STATUS_DISCONNECTED]);
        $account->delete();
    }

    // -------------------------------------------------------------------------
    // Plan limits
    // -------------------------------------------------------------------------

    /**
     * Check how many WhatsApp accounts this loja's plan allows.
     * Throws if the limit is reached.
     */
    public function assertAccountLimitNotExceeded(Loja $loja): void
    {
        $limit = $this->getAccountLimit($loja);

        if ($limit !== null) {
            $current = WhatsAppAccount::forLoja($loja->id)->count();
            if ($current >= $limit) {
                throw new \DomainException(
                    "Seu plano permite no máximo {$limit} número(s) de WhatsApp conectado(s). " .
                    "Faça upgrade para adicionar mais."
                );
            }
        }
    }

    public function getAccountLimit(Loja $loja): ?int
    {
        $planoId = $loja->assinatura?->plano_id ?? $loja->plano_id ?? null;
        if ($planoId === null) {
            return null; // no plan assigned — no quota enforced yet
        }

        $row = PlanoLimit::where('plano_id', $planoId)
            ->where('limit_key', self::LIMIT_KEY_ACCOUNTS)
            ->first();

        return $row?->limit_value;
    }

    public function getMonthlyMessageLimit(Loja $loja): ?int
    {
        $planoId = $loja->assinatura?->plano_id ?? $loja->plano_id ?? null;
        if ($planoId === null) {
            return null;
        }

        $row = PlanoLimit::where('plano_id', $planoId)
            ->where('limit_key', self::LIMIT_KEY_MESSAGES)
            ->first();

        return $row?->limit_value;
    }

    /**
     * Count outbound messages sent this calendar month for a loja.
     */
    public function countMonthlyMessagesSent(int $lojaId): int
    {
        return \App\Models\WhatsApp\WhatsAppMessage::where('loja_id', $lojaId)
            ->where('direction', \App\Models\WhatsApp\WhatsAppMessage::DIRECTION_OUTBOUND)
            ->where('status', '!=', \App\Models\WhatsApp\WhatsAppMessage::STATUS_FAILED)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
    }

    public function assertMonthlyMessageLimitNotExceeded(Loja $loja): void
    {
        $limit = $this->getMonthlyMessageLimit($loja);
        if ($limit === null) {
            return; // unlimited
        }

        $used = $this->countMonthlyMessagesSent($loja->id);
        if ($used >= $limit) {
            throw new \DomainException(
                "Limite mensal de {$limit} mensagens WhatsApp atingido. " .
                "Faça upgrade do plano para enviar mais mensagens."
            );
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve the primary active account for a loja, or null if none.
     */
    public function getPrimaryAccount(Loja $loja): ?WhatsAppAccount
    {
        return WhatsAppAccount::forLoja($loja->id)
            ->active()
            ->where('is_primary', true)
            ->first()
            ?? WhatsAppAccount::forLoja($loja->id)->active()->first();
    }
}
