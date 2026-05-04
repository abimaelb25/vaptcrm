<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\Loja;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppAccountService;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * WhatsApp account onboarding and account management.
 *
 * Routes prefix: /painel/whatsapp/accounts
 * All routes require auth + check_plan_feature:modulo_whatsapp.
 */
class WhatsAppOnboardingController extends Controller
{
    public function __construct(
        private WhatsAppAccountService  $accountService,
        private WhatsAppTemplateService $templateService,
    ) {}

    // -------------------------------------------------------------------------
    // Account list
    // -------------------------------------------------------------------------

    /**
     * GET /painel/whatsapp/accounts
     */
    public function index(): JsonResponse
    {
        $accounts = WhatsAppAccount::forLoja(Auth::user()->loja_id)
            ->withTrashed(false)
            ->get(['id', 'display_name', 'phone_number', 'status', 'is_primary', 'quality_rating', 'connected_at']);

        $loja  = Auth::user()->loja;
        $limit = $this->accountService->getAccountLimit($loja);

        return response()->json([
            'accounts' => $accounts,
            'limit'    => $limit,
            'count'    => $accounts->count(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Onboarding — complete embedded-signup flow
    // -------------------------------------------------------------------------

    /**
     * POST /painel/whatsapp/accounts/onboard
     *
     * Called by the frontend after the Meta embedded-signup SDK returns
     * the access token, WABA ID and phone number ID.
     *
     * IMPORTANT: the access_token arrives from the browser but is validated
     * and stored encrypted. It is NOT used as-is for any operation at this point.
     */
    public function onboard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'waba_id'         => 'required|string|max:80',
            'phone_number_id' => 'required|string|max:80',
            'phone_number'    => 'required|string|max:30',
            'display_name'    => 'nullable|string|max:120',
            'access_token'    => 'required|string|min:10|max:500',
            'business_id'     => 'nullable|string|max:80',
        ]);

        $loja = Auth::user()->loja;

        try {
            $account = $this->accountService->onboard($loja, $validated);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'WhatsApp conectado com sucesso.',
            'account' => [
                'id'           => $account->id,
                'display_name' => $account->display_name,
                'phone_number' => $account->phone_number,
                'status'       => $account->status,
                'is_primary'   => $account->is_primary,
            ],
        ], 201);
    }

    // -------------------------------------------------------------------------
    // Disconnect
    // -------------------------------------------------------------------------

    /**
     * DELETE /painel/whatsapp/accounts/{account}
     */
    public function disconnect(WhatsAppAccount $account): JsonResponse
    {
        $this->authorizeAccount($account);

        $this->accountService->disconnect($account);

        return response()->json(['message' => 'Número desconectado com sucesso.']);
    }

    // -------------------------------------------------------------------------
    // Template management
    // -------------------------------------------------------------------------

    /**
     * GET /painel/whatsapp/accounts/{account}/templates
     */
    public function templates(WhatsAppAccount $account): JsonResponse
    {
        $this->authorizeAccount($account);

        $templates = WhatsAppTemplate::where('whatsapp_account_id', $account->id)
            ->get(['id', 'name', 'language', 'category', 'status', 'is_system', 'system_key']);

        return response()->json($templates);
    }

    /**
     * POST /painel/whatsapp/accounts/{account}/templates/sync
     */
    public function syncTemplates(WhatsAppAccount $account): JsonResponse
    {
        $this->authorizeAccount($account);

        $count = $this->templateService->syncFromMeta($account);

        return response()->json(['message' => "{$count} template(s) sincronizado(s)."]);
    }

    /**
     * PATCH /painel/whatsapp/accounts/{account}/templates/{template}/set-system-key
     */
    public function setTemplateSystemKey(
        Request $request,
        WhatsAppAccount $account,
        WhatsAppTemplate $template
    ): JsonResponse {
        $this->authorizeAccount($account);

        if ($template->whatsapp_account_id !== $account->id) {
            abort(403);
        }

        $validated = $request->validate([
            'system_key' => 'required|string|in:'
                . implode(',', [
                    WhatsAppTemplate::KEY_ORDER_CREATED,
                    WhatsAppTemplate::KEY_ORDER_QUOTE_SENT,
                    WhatsAppTemplate::KEY_ORDER_PRODUCTION,
                    WhatsAppTemplate::KEY_ORDER_READY,
                    WhatsAppTemplate::KEY_ORDER_DELIVERED,
                ]),
        ]);

        $this->templateService->setSystemKey($template, $validated['system_key']);

        return response()->json(['message' => 'Chave de sistema definida.']);
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function authorizeAccount(WhatsAppAccount $account): void
    {
        if ($account->loja_id !== Auth::user()->loja_id) {
            abort(403, 'Acesso negado.');
        }
    }
}
