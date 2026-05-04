<?php

declare(strict_types=1);

namespace App\Contracts\WhatsApp;

/**
 * Contract for WhatsApp Business Platform providers.
 *
 * Implementations: MetaCloudAdapter, BspAdapter (future).
 * All methods receive/return plain arrays to stay provider-agnostic.
 */
interface WhatsAppProviderInterface
{
    /**
     * Send a free-form text message (only valid within 24h service window).
     *
     * @param  string $phoneNumberId  Provider phone number ID
     * @param  string $accessToken    Decrypted access token
     * @param  string $toPhone        Recipient E.164 phone number
     * @param  string $text           Message text
     * @return array{message_id: string}
     */
    public function sendTextMessage(
        string $phoneNumberId,
        string $accessToken,
        string $toPhone,
        string $text
    ): array;

    /**
     * Send a pre-approved template message.
     *
     * @param  string $phoneNumberId
     * @param  string $accessToken
     * @param  string $toPhone
     * @param  string $templateName
     * @param  string $languageCode
     * @param  array  $components    Variable placeholders for the template
     * @return array{message_id: string}
     */
    public function sendTemplateMessage(
        string $phoneNumberId,
        string $accessToken,
        string $toPhone,
        string $templateName,
        string $languageCode,
        array  $components = []
    ): array;

    /**
     * Send a media message (image, document, etc.).
     *
     * @param  string $phoneNumberId
     * @param  string $accessToken
     * @param  string $toPhone
     * @param  string $type          image|document|audio|video
     * @param  string $mediaUrl      Publicly accessible URL
     * @param  string|null $caption
     * @return array{message_id: string}
     */
    public function sendMediaMessage(
        string $phoneNumberId,
        string $accessToken,
        string $toPhone,
        string $type,
        string $mediaUrl,
        ?string $caption = null
    ): array;

    /**
     * Retrieve all approved templates for a WABA.
     *
     * @return array<int, array>
     */
    public function getTemplates(string $wabaId, string $accessToken): array;

    /**
     * Mark a message as read (sends read receipt to Meta).
     */
    public function markAsRead(
        string $phoneNumberId,
        string $accessToken,
        string $metaMessageId
    ): bool;

    /**
     * Validate a webhook verify token against provider rules.
     */
    public function validateWebhook(
        string $mode,
        string $token,
        string $challenge,
        string $expectedToken
    ): string|false;

    /**
     * Parse a raw incoming webhook payload into a normalised event array.
     *
     * Returns null if the payload is not a message event.
     *
     * @return array{
     *   event_type: string,
     *   phone_number_id: string,
     *   from_phone: string,
     *   message_id: string,
     *   type: string,
     *   body: string|null,
     *   timestamp: int,
     *   status: string|null,
     * }|null
     */
    public function parseWebhookPayload(array $payload): ?array;
}
