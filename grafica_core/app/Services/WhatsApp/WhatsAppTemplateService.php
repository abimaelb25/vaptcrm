<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Support\Facades\Log;

/**
 * Manages WhatsApp message templates:
 *  - syncs templates from Meta API
 *  - resolves system templates for order automation
 */
class WhatsAppTemplateService
{
    public function __construct(
        private WhatsAppProviderResolver $resolver,
    ) {}

    /**
     * Sync all templates from Meta for a given account.
     */
    public function syncFromMeta(WhatsAppAccount $account): int
    {
        if (! $account->isMetaCloud()) {
            return 0;
        }

        $provider  = $this->resolver->resolve($account);
        $templates = $provider->getTemplates($account->waba_id, $account->access_token);

        $synced = 0;
        foreach ($templates as $t) {
            WhatsAppTemplate::updateOrCreate(
                [
                    'whatsapp_account_id' => $account->id,
                    'name'                => $t['name'],
                    'language'            => $t['language'] ?? 'pt_BR',
                ],
                [
                    'loja_id'          => $account->loja_id,
                    'category'         => $t['category']    ?? 'UTILITY',
                    'status'           => strtolower($t['status'] ?? 'pending'),
                    'components'       => $t['components']  ?? [],
                    'meta_template_id' => $t['id']          ?? null,
                ]
            );
            $synced++;
        }

        Log::info('WhatsApp: templates synced', [
            'account_id' => $account->id,
            'count'      => $synced,
        ]);

        return $synced;
    }

    /**
     * Resolve an approved system template by key for a loja account.
     * Returns null if not found or not approved.
     */
    public function resolveSystemTemplate(
        WhatsAppAccount $account,
        string $systemKey
    ): ?WhatsAppTemplate {
        return WhatsAppTemplate::where('whatsapp_account_id', $account->id)
            ->system()
            ->byKey($systemKey)
            ->approved()
            ->first();
    }

    /**
     * Mark a template as system-managed with a given key.
     */
    public function setSystemKey(WhatsAppTemplate $template, string $key): void
    {
        $template->update(['is_system' => true, 'system_key' => $key]);
    }
}
