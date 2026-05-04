<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Contracts\WhatsApp\WhatsAppProviderInterface;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Services\WhatsApp\Adapters\MetaCloudAdapter;
use App\Services\WhatsApp\Adapters\BspAdapter;
use Illuminate\Support\Str;

/**
 * Resolves the correct provider adapter for a given WhatsAppAccount.
 * All other services depend on this resolver.
 */
class WhatsAppProviderResolver
{
    public function resolve(WhatsAppAccount $account): WhatsAppProviderInterface
    {
        return match ($account->provider) {
            WhatsAppAccount::PROVIDER_META_CLOUD => app(MetaCloudAdapter::class),
            WhatsAppAccount::PROVIDER_BSP        => app(BspAdapter::class),
            default => throw new \InvalidArgumentException(
                "Unknown WhatsApp provider: {$account->provider}"
            ),
        };
    }
}
