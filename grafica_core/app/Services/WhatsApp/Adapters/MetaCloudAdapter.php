<?php

declare(strict_types=1);

namespace App\Services\WhatsApp\Adapters;

use App\Contracts\WhatsApp\WhatsAppProviderInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Meta Cloud API adapter — official WhatsApp Business Platform.
 *
 * Docs: https://developers.facebook.com/docs/whatsapp/cloud-api
 * API version controlled via config('whatsapp.meta.api_version').
 *
 * Security:
 *  - All tokens are received already decrypted from WhatsAppAccountService
 *  - Never logs access tokens
 *  - Uses Laravel HTTP client (Guzzle) with timeout
 */
class MetaCloudAdapter implements WhatsAppProviderInterface
{
    private string $apiVersion;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiVersion = config('whatsapp.meta.api_version', 'v19.0');
        $this->baseUrl    = "https://graph.facebook.com/{$this->apiVersion}";
    }

    // -------------------------------------------------------------------------
    // WhatsAppProviderInterface
    // -------------------------------------------------------------------------

    public function sendTextMessage(
        string $phoneNumberId,
        string $accessToken,
        string $toPhone,
        string $text
    ): array {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $toPhone,
            'type'              => 'text',
            'text'              => ['preview_url' => false, 'body' => $text],
        ];

        return $this->post("{$this->baseUrl}/{$phoneNumberId}/messages", $accessToken, $payload);
    }

    public function sendTemplateMessage(
        string $phoneNumberId,
        string $accessToken,
        string $toPhone,
        string $templateName,
        string $languageCode,
        array  $components = []
    ): array {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $toPhone,
            'type'              => 'template',
            'template'          => [
                'name'       => $templateName,
                'language'   => ['code' => $languageCode],
                'components' => $components,
            ],
        ];

        return $this->post("{$this->baseUrl}/{$phoneNumberId}/messages", $accessToken, $payload);
    }

    public function sendMediaMessage(
        string $phoneNumberId,
        string $accessToken,
        string $toPhone,
        string $type,
        string $mediaUrl,
        ?string $caption = null
    ): array {
        $mediaPayload = ['link' => $mediaUrl];
        if ($caption !== null) {
            $mediaPayload['caption'] = $caption;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $toPhone,
            'type'              => $type,
            $type               => $mediaPayload,
        ];

        return $this->post("{$this->baseUrl}/{$phoneNumberId}/messages", $accessToken, $payload);
    }

    public function getTemplates(string $wabaId, string $accessToken): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->get("{$this->baseUrl}/{$wabaId}/message_templates", [
                    'fields' => 'name,status,category,language,components,id',
                ]);

            $response->throw();

            return $response->json('data', []);
        } catch (RequestException $e) {
            Log::warning('WhatsApp: failed to fetch templates', [
                'waba_id' => $wabaId,
                'status'  => $e->response->status(),
                'error'   => $e->response->json(),
            ]);
            return [];
        }
    }

    public function markAsRead(
        string $phoneNumberId,
        string $accessToken,
        string $metaMessageId
    ): bool {
        try {
            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post("{$this->baseUrl}/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'status'            => 'read',
                    'message_id'        => $metaMessageId,
                ]);

            $response->throw();
            return true;
        } catch (RequestException $e) {
            Log::warning('WhatsApp: failed to mark message as read', [
                'message_id' => $metaMessageId,
                'error'      => $e->response->json(),
            ]);
            return false;
        }
    }

    public function validateWebhook(
        string $mode,
        string $token,
        string $challenge,
        string $expectedToken
    ): string|false {
        if ($mode === 'subscribe' && hash_equals($expectedToken, $token)) {
            return $challenge;
        }
        return false;
    }

    public function parseWebhookPayload(array $payload): ?array
    {
        // Navigate to entry → changes → value
        $entry   = $payload['entry'][0]   ?? null;
        $changes = $entry['changes'][0]   ?? null;
        $value   = $changes['value']      ?? null;

        if ($value === null) {
            return null;
        }

        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

        // Message received
        $message = $value['messages'][0] ?? null;
        if ($message !== null) {
            $type = $message['type'] ?? 'text';
            $body = match ($type) {
                'text'     => $message['text']['body']            ?? null,
                'image'    => $message['image']['caption']        ?? null,
                'document' => $message['document']['filename']    ?? null,
                default    => null,
            };

            return [
                'event_type'      => 'message',
                'phone_number_id' => $phoneNumberId,
                'from_phone'      => $message['from']                 ?? null,
                'contact_name'    => $value['contacts'][0]['profile']['name'] ?? null,
                'message_id'      => $message['id']                   ?? null,
                'type'            => $type,
                'body'            => $body,
                'timestamp'       => (int) ($message['timestamp']     ?? 0),
                'status'          => null,
                'raw_message'     => $message,
            ];
        }

        // Status update
        $status = $value['statuses'][0] ?? null;
        if ($status !== null) {
            return [
                'event_type'      => 'status',
                'phone_number_id' => $phoneNumberId,
                'from_phone'      => $status['recipient_id'] ?? null,
                'contact_name'    => null,
                'message_id'      => $status['id']           ?? null,
                'type'            => 'status',
                'body'            => null,
                'timestamp'       => (int) ($status['timestamp'] ?? 0),
                'status'          => $status['status']       ?? null,
                'raw_message'     => $status,
            ];
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * POST to Meta Graph API, return normalised response.
     *
     * @throws \RuntimeException on non-2xx response
     */
    private function post(string $url, string $accessToken, array $payload): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->timeout(20)
                ->post($url, $payload);

            $response->throw();

            $data = $response->json();

            return [
                'message_id' => $data['messages'][0]['id'] ?? ($data['id'] ?? ''),
                'raw'        => $data,
            ];
        } catch (RequestException $e) {
            $error = $e->response->json();
            $code  = $error['error']['code']    ?? 'unknown';
            $msg   = $error['error']['message'] ?? $e->getMessage();

            Log::error('WhatsApp: Meta API error', [
                'url'        => $url,
                'error_code' => $code,
                'error_msg'  => $msg,
            ]);

            throw new \RuntimeException("[WhatsApp Meta API {$code}] {$msg}", (int) $code);
        }
    }
}
