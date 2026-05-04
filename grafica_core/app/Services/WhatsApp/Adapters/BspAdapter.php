<?php

declare(strict_types=1);

namespace App\Services\WhatsApp\Adapters;

use App\Contracts\WhatsApp\WhatsAppProviderInterface;

/**
 * Stub BSP (Business Solution Provider) adapter.
 *
 * Implement this class when integrating a third-party BSP (e.g. Twilio, Zenvia).
 * All methods throw until implemented.
 */
class BspAdapter implements WhatsAppProviderInterface
{
    public function sendTextMessage(
        string $phoneNumberId,
        string $accessToken,
        string $toPhone,
        string $text
    ): array {
        throw new \BadMethodCallException('BspAdapter: not yet implemented.');
    }

    public function sendTemplateMessage(
        string $phoneNumberId,
        string $accessToken,
        string $toPhone,
        string $templateName,
        string $languageCode,
        array  $components = []
    ): array {
        throw new \BadMethodCallException('BspAdapter: not yet implemented.');
    }

    public function sendMediaMessage(
        string $phoneNumberId,
        string $accessToken,
        string $toPhone,
        string $type,
        string $mediaUrl,
        ?string $caption = null
    ): array {
        throw new \BadMethodCallException('BspAdapter: not yet implemented.');
    }

    public function getTemplates(string $wabaId, string $accessToken): array
    {
        return [];
    }

    public function markAsRead(
        string $phoneNumberId,
        string $accessToken,
        string $metaMessageId
    ): bool {
        return false;
    }

    public function validateWebhook(
        string $mode,
        string $token,
        string $challenge,
        string $expectedToken
    ): string|false {
        return false;
    }

    public function parseWebhookPayload(array $payload): ?array
    {
        return null;
    }
}
