<?php

declare(strict_types=1);

namespace App\Http\Controllers\Financeiro;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-07 22:32 -03:00
*/

use App\Http\Controllers\Controller;
use App\Models\Pagamento;
use App\Models\Pedido;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeController extends Controller
{
    public function __construct(private readonly StripeService $stripeService) {}

    /**
     * Inicia pagamento online — redireciona para o Stripe Checkout.
     * SEGURANÇA: autenticado no painel, valor vem do banco nunca do request.
     */
    public function checkoutOnline(Pedido $pedido): RedirectResponse
    {
        try {
            // Valida que o pedido está em estado que permite cobrança
            $this->validarEstadoPedido($pedido);

            $pagamento = $this->stripeService->criarCheckoutOnline($pedido);

            return redirect()->away($pagamento->stripe_checkout_url);

        } catch (\InvalidArgumentException $e) {
            return back()->with('erro', $e->getMessage());
        } catch (\RuntimeException $e) {
            Log::error('[Stripe] Erro ao criar checkout online.', [
                'pedido_id' => $pedido->id,
                'error'     => $e->getMessage(),
            ]);
            return back()->with('erro', 'Não foi possível iniciar o pagamento online. Verifique a integração Stripe.');
        }
    }

    /**
     * Gera sessão de checkout para uso presencial (QR Code no painel).
     * Retorna JSON com a URL do checkout para renderizar o QR Code.
     * SEGURANÇA: autenticado, valor sempre do banco.
     */
    public function checkoutPresencial(Pedido $pedido): JsonResponse
    {
        try {
            $this->validarEstadoPedido($pedido);

            $pagamento = $this->stripeService->criarCheckoutPresencial($pedido);

            return response()->json([
                'sucesso'      => true,
                'checkout_url' => $pagamento->stripe_checkout_url,
                'session_id'   => $pagamento->stripe_session_id,
                'expira_em'    => $pagamento->stripe_expires_at?->toIso8601String(),
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['sucesso' => false, 'mensagem' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            Log::error('[Stripe] Erro ao criar checkout presencial.', [
                'pedido_id' => $pedido->id,
                'error'     => $e->getMessage(),
            ]);
            return response()->json(['sucesso' => false, 'mensagem' => 'Erro interno ao gerar sessão de pagamento.'], 500);
        }
    }

    /**
     * Página de retorno após pagamento bem-sucedido.
     * SEGURANÇA: valida a session_id via banco — não confia em parâmetros da URL para atualizar status.
     */
    public function sucesso(Request $request)
    {
        $sessionId    = $request->query('session_id', '');
        $numeroPedido = $request->query('pedido', '');

        // Valida parâmetros básicos
        if (empty($sessionId) || empty($numeroPedido)) {
            abort(400, 'Parâmetros inválidos.');
        }

        // Busca o pagamento pelo session_id do banco — nunca pelo parâmetro da URL direto
        $pagamento = Pagamento::query()
            ->where('stripe_session_id', $sessionId)
            ->with('pedido.cliente')
            ->first();

        $pedido = $pagamento?->pedido ?? Pedido::query()->where('numero', $numeroPedido)->first();

        return view('publico.stripe.sucesso', [
            'pedido'   => $pedido,
            'pagamento' => $pagamento,
        ]);
    }

    /**
     * Página de retorno após cancelamento/abandono do checkout.
     */
    public function cancelado(Request $request)
    {
        $numeroPedido = $request->query('pedido', '');

        $pedido = null;
        if ($numeroPedido) {
            $pedido = Pedido::query()->where('numero', (string) $numeroPedido)->first();
        }

        return view('publico.stripe.cancelado', compact('pedido'));
    }

    /**
     * Endpoint de webhook recebido do Stripe.
     * SEGURANÇA CRÍTICA:
     * - Sem autenticação de sessão (rota pública API)
     * - Sem CSRF (excluído no bootstrap/app.php)
     * - Com verificação obrigatória de assinatura HMAC (Stripe-Signature header)
     * - Rate limiting aplicado no grupo de rota API
     */
    public function webhook(Request $request): Response
    {
        try {
            $this->stripeService->processarWebhook($request);
            return response('OK', 200);

        } catch (SignatureVerificationException $e) {
            // Assinatura inválida — possível fraude
            return response('Assinatura inválida', 400);

        } catch (UnexpectedValueException $e) {
            // Payload malformado
            return response('Payload inválido', 400);

        } catch (\Throwable $e) {
            Log::error('[Stripe] Erro inesperado no webhook.', ['error' => $e->getMessage()]);
            return response('Erro interno', 500);
        }
    }

    /**
     * Valida que o pedido pode receber cobrança via Stripe.
     * Impede cobrar pedidos em status inadequados.
     */
    private function validarEstadoPedido(Pedido $pedido): void
    {
        $statusPermitidos = ['rascunho', 'aguardando_aprovacao', 'aprovado'];

        if (! in_array($pedido->status, $statusPermitidos, true)) {
            throw new \InvalidArgumentException(
                "O pedido #{$pedido->numero} está com status '{$pedido->status}' e não pode ser cobrado agora."
            );
        }

        if ($pedido->total <= 0) {
            throw new \InvalidArgumentException(
                "O pedido #{$pedido->numero} tem valor zero e não pode ser cobrado."
            );
        }
    }
}
