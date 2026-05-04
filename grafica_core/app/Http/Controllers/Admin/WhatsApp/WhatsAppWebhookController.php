<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Services\WhatsApp\WhatsAppWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Handles official Meta Cloud webhook verification and event ingestion.
 *
 * Routes:
 *   GET  /webhooks/whatsapp/{account}  → verification challenge
 *   POST /webhooks/whatsapp/{account}  → event ingestion
 *
 * Security:
 *  - Verification uses hash_equals() to prevent timing attacks
 *  - POST signature is verified via X-Hub-Signature-256 before any processing
 *  - Raw events are always persisted for audit; processing errors never return 500
 *    (Meta retries on non-200, which would flood the log)
 */
class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private WhatsAppWebhookService $webhookService,
    ) {}

    // -------------------------------------------------------------------------
    // GET — webhook verification (Meta embedded-signup challenge)
    // -------------------------------------------------------------------------

    public function verify(Request $request, WhatsAppAccount $account): Response
    {
        $mode      = $request->query('hub_mode', '');
        $token     = $request->query('hub_verify_token', '');
        $challenge = $request->query('hub_challenge', '');

        if (
            $mode === 'subscribe'
            && hash_equals((string) $account->webhook_verify_token, (string) $token)
        ) {
            return response($challenge, 200);
        }

        Log::warning('WhatsApp: webhook verify failed', [
            'account_id' => $account->id,
            'mode'       => $mode,
        ]);

        return response('Forbidden', 403);
    }

    // -------------------------------------------------------------------------
    // POST — incoming events
    // -------------------------------------------------------------------------

    public function receive(Request $request, WhatsAppAccount $account): Response
    {
        // Verify Meta signature — HMAC-SHA256
        if (! $this->verifySignature($request, $account)) {
            Log::warning('WhatsApp: invalid webhook signature', ['account_id' => $account->id]);
            return response('Forbidden', 403);
        }

        $payload = $request->json()->all();

        // Dispatch handling — always return 200 to Meta immediately.
        // Actual processing happens inside (sync) or via queued job.
        try {
            $this->webhookService->handle($payload, $account->provider);
        } catch (\Throwable $e) {
            // Already logged inside handle(); return 200 so Meta doesn't retry
            Log::error('WhatsApp: unhandled exception in webhook', ['error' => $e->getMessage()]);
        }

        return response('OK', 200);
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function verifySignature(Request $request, WhatsAppAccount $account): bool
    {
        $appSecret = config('whatsapp.meta.app_secret');

        // If app_secret is not configured, skip signature check (dev only)
        if (empty($appSecret)) {
            return app()->isLocal();
        }

        $signature = $request->header('X-Hub-Signature-256', '');
        if (empty($signature)) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);

        return hash_equals($expected, $signature);
    }
}
