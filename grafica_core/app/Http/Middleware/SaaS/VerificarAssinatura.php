<?php

declare(strict_types=1);

namespace App\Http\Middleware\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:20 (atualizado: bloqueio por inadimplência + suporte AJAX)
*/

use App\Services\SaaS\PlanService;
use App\Services\SaaS\SaaSService;
use App\Models\SaaS\Assinatura;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class VerificarAssinatura
{
    public function __construct(
        protected SaaSService $saasService,
        protected PlanService $planService,
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ignora rotas de checkout, billing e webhooks para evitar loops
        if ($request->is('painel/assinatura*') || $request->is('pagamentos*') || $request->is('api/webhooks*') || $request->is('loja-bloqueada')) {
            return $next($request);
        }

        $user = Auth::user();
        if ($user && $user->loja && $user->loja->estaBloqueada()) {
            return $this->respondBlocked($request, 'Sua loja está bloqueada. Entre em contato com o suporte.');
        }

        try {
            $validation = $this->planService->validateTenantForAccess();
        } catch (RuntimeException $e) {
            // Evita 500 em sessão inconsistente (usuário sem tenant resolvido).
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sessão expirada. Faça login novamente.',
                    'redirect' => route('login'),
                    'error_code' => 'SESSION_EXPIRED',
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with('erro', 'Não foi possível validar sua loja. Faça login novamente.');
        }

        if (! $validation['valid']) {
            return $this->respondPlanBlocked(
                $request,
                (string) ($validation['message'] ?? 'Seu plano nao permite acesso no momento.'),
                (string) ($validation['reason'] ?? 'PLAN_INVALID'),
                (string) ($validation['cta_url'] ?? '/painel/assinatura')
            );
        }

        if ($user && $user->loja_id) {
            $assinatura = Assinatura::query()
                ->where('loja_id', $user->loja_id)
                ->latest('id')
                ->first();

            $locked = $assinatura && in_array($assinatura->status, [
                Assinatura::STATUS_PAST_DUE,
                Assinatura::STATUS_CANCELADA,
                Assinatura::STATUS_SUSPENDED,
            ], true);

            if ($locked && ! $this->isReadOnlyRequest($request)) {
                return $this->respondPlanBlocked(
                    $request,
                    'Seu plano está inadimplente/cancelado. Operações de gravação estão bloqueadas até regularização.',
                    'READ_ONLY_LOCKED',
                    '/painel/assinatura'
                );
            }
        }

        return $next($request);
    }

    /**
     * Responder quando a loja está bloqueada.
     */
    protected function respondBlocked(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'redirect' => route('admin.loja.bloqueada'),
                'error_code' => 'STORE_BLOCKED',
            ], 403);
        }

        return redirect()->route('admin.loja.bloqueada');
    }

    /**
     * Responder quando a assinatura está expirada.
     */
    protected function respondExpired(Request $request): Response
    {
        $message = 'Sua assinatura expirou. Renove para continuar utilizando todos os recursos.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'redirect' => route('admin.billing.index'),
                'error_code' => 'SUBSCRIPTION_EXPIRED',
            ], 402); // 402 Payment Required
        }

        return redirect()
            ->route('admin.billing.index')
            ->with('warning', $message);
    }

    protected function respondPlanBlocked(Request $request, string $message, string $reason, string $ctaUrl): Response
    {
        $errorCode = strtoupper($reason);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => $errorCode,
                'cta_url' => $ctaUrl,
                'redirect' => route('admin.billing.index'),
            ], 402);
        }

        return redirect()
            ->route('admin.billing.index')
            ->with('warning', $message . ' Clique em "Assinatura" para fazer upgrade e desbloquear o sistema.');
    }

    private function isReadOnlyRequest(Request $request): bool
    {
        if ($request->method() === 'GET' || $request->method() === 'HEAD' || $request->method() === 'OPTIONS') {
            return true;
        }

        return false;
    }
}
