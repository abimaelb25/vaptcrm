<?php

declare(strict_types=1);

namespace App\Services;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-07 22:32 -03:00
*/

use App\Models\HistoricoPedido;
use App\Models\MovimentacaoFinanceira;
use App\Models\Pagamento;
use App\Models\Pedido;
use App\Models\SaaS\Plano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeService
{
    private StripeClient $stripe;
    private string $webhookSecret;

    public function __construct()
    {
        $secret = config('stripe.secret');

        if (empty($secret)) {
            throw new \RuntimeException('A chave secreta do Stripe não está configurada no .env (STRIPE_SECRET).');
        }

        $this->stripe = new StripeClient([
            'api_key' => $secret,
            'stripe_version' => config('stripe.api_version'),
        ]);

        // Evita erro de "SSL certificate problem" no servidor embutido do Windows/XAMPP
        if (app()->isLocal() || config('app.env') === 'local') {
            \Stripe\Stripe::setVerifySslCerts(false);
        }

        $this->webhookSecret = (string) config('stripe.webhook_secret');
    }

    /**
     * Cria uma Checkout Session do Stripe para pagamento online.
     * O valor é SEMPRE lido do banco — nunca aceito do frontend.
     */
    public function criarCheckoutOnline(Pedido $pedido): Pagamento
    {
        return $this->criarCheckoutSession($pedido, 'online');
    }

    /**
     * Cria uma Checkout Session do Stripe para exibição presencial (QR Code).
     * Mesmo mecanismo do online — diferenciado pelo campo tipo_cobranca.
     */
    public function criarCheckoutPresencial(Pedido $pedido): Pagamento
    {
        return $this->criarCheckoutSession($pedido, 'presencial');
    }

    /**
     * Método centralizado que cria a Checkout Session.
     * Segurança: o valor é calculado exclusivamente a partir do banco de dados.
     */
    private function criarCheckoutSession(Pedido $pedido, string $tipoCobranca): Pagamento
    {
        // Garante que o pedido tem cliente carregado
        $pedido->load('cliente');

        // SEGURANÇA: recarrega o total do banco para evitar manipulação
        $totalBanco = (float) Pedido::query()
            ->select('total')
            ->where('id', $pedido->id)
            ->value('total');

        // Converte para centavos (Stripe usa inteiro)
        $valorCentavos = (int) round($totalBanco * 100);

        // Validação de valor mínimo (R$ 0,50 = 50 centavos)
        $minimo = (int) config('stripe.valor_minimo_centavos', 50);
        if ($valorCentavos < $minimo) {
            throw new \InvalidArgumentException(
                "O valor do pedido (R$ " . number_format($totalBanco, 2, ',', '.') . ") é inferior ao mínimo aceito pelo Stripe (R$ 0,50)."
            );
        }

        $urlBase = config('app.url');
        $numeroPedido = $pedido->numero;

        // Idempotency key baseada no pedido e tipo — evita cobranças duplicadas
        $idempotencyKey = "pedido-{$pedido->id}-{$tipoCobranca}-" . date('Ymd');

        $metodosPagamento = config('stripe.metodos_pagamento', ['card']);

        $sessionPayload = [
            'mode' => 'payment',
            'customer_email' => $pedido->cliente->email ?? null,
            'line_items' => [
                [
                    'price_data' => [
                        'currency'     => config('stripe.currency', 'BRL'),
                        'unit_amount'  => $valorCentavos,
                        'product_data' => [
                            'name'        => "Pedido #{$numeroPedido}",
                            'description' => "CRM Gráfica — Pedido {$numeroPedido}",
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'payment_method_types' => $metodosPagamento,
            'success_url' => $urlBase . "/checkout/sucesso/{$numeroPedido}?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url'  => $urlBase . "/pagamentos/stripe/cancelado?pedido={$numeroPedido}",
            'metadata' => [
                'pedido_id'    => $pedido->id,
                'pedido_num'   => $numeroPedido,
                'tipo'         => $tipoCobranca,
                'sistema'      => 'crm-grafica',
            ],
        ];

        // Configurações específicas para boleto (prazo 3 dias), apenas se estiver ativado
        if (in_array('boleto', $metodosPagamento)) {
            $sessionPayload['payment_method_options'] = [
                'boleto' => [
                    'expires_after_days' => 3,
                ],
            ];
        }

        $session = $this->stripe->checkout->sessions->create(
            $sessionPayload,
            ['idempotency_key' => $idempotencyKey]
        );

        // Persiste o registro de pagamento no banco
        $pagamento = Pagamento::query()->create([
            'pedido_id'            => $pedido->id,
            'gateway'              => 'stripe',
            'metodo'               => 'stripe_checkout',
            'valor'                => $totalBanco,
            'status'               => 'pendente',
            'tipo_cobranca'        => $tipoCobranca,
            'stripe_session_id'    => $session->id,
            'stripe_checkout_url'  => $session->url,
            'stripe_expires_at'    => $session->expires_at
                ? \Illuminate\Support\Carbon::createFromTimestamp($session->expires_at)
                : null,
            'payload_original'     => [
                'session_id' => $session->id,
                'status'     => $session->status,
                'amount'     => $valorCentavos,
                'currency'   => $session->currency,
                'created_at' => $session->created,
            ],
        ]);

        return $pagamento;
    }

    /**
     * Cria uma Checkout Session para Assinatura Recorrente (SaaS).
     */
    public function criarCheckoutAssinatura(Plano $plano, string $stripeCustomerId = null): array
    {
        $urlBase = config('app.url');

        if (empty($plano->stripe_price_id)) {
            throw new \InvalidArgumentException("O plano selecionado ({$plano->nome}) não possui um ID de preço configurado no Stripe.");
        }

        $sessionPayload = [
            'mode' => 'subscription',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $plano->stripe_price_id,
                'quantity' => 1,
            ]],
            'subscription_data' => [
                'trial_period_days' => 15, // Teste grátis solicitado pelo usuário
            ],
            'success_url' => $urlBase . "/painel/assinatura?sucesso=1",
            'cancel_url'  => $urlBase . "/painel/assinatura?cancelado=1",
            'customer'    => $stripeCustomerId,
        ];

        // Se não houver customer, Stripe criará um novo vinculado à sessão.
        $session = $this->stripe->checkout->sessions->create($sessionPayload);

        return [
            'id' => $session->id,
            'url' => $session->url,
        ];
    }

    /**
     * Gera uma URL para o Portal de Faturamento do Stripe.
     */
    public function portalCliente(string $stripeCustomerId): string
    {
        $session = $this->stripe->billingPortal->sessions->create([
            'customer' => $stripeCustomerId,
            'return_url' => config('app.url') . "/painel/assinatura",
        ]);

        return $session->url;
    }

    /**
     * Valida e processa o payload recebido do webhook Stripe.
     * SEGURANÇA: Verificação HMAC obrigatória — rejeita payloads sem assinatura válida.
     */
    public function processarWebhook(Request $request): void
    {
        if (empty($this->webhookSecret)) {
            Log::error('[Stripe] STRIPE_WEBHOOK_SECRET não configurado — webhook rejeitado.');
            throw new \RuntimeException('Webhook secret não configurado.');
        }

        $payload   = $request->getContent();
        $assinatura = $request->header('Stripe-Signature');

        try {
            $evento = Webhook::constructEvent($payload, $assinatura, $this->webhookSecret);
        } catch (UnexpectedValueException $e) {
            Log::warning('[Stripe] Payload de webhook inválido.', ['error' => $e->getMessage()]);
            throw $e;
        } catch (SignatureVerificationException $e) {
            Log::warning('[Stripe] Assinatura do webhook inválida — possível tentativa de fraude.', [
                'ip' => $request->ip(),
            ]);
            throw $e;
        }

        // Roteia para o handler correto
        match ($evento->type) {
            'checkout.session.completed'          => $this->handleCheckoutCompleted($evento),
            'checkout.session.expired'            => $this->handleCheckoutExpired($evento),
            'payment_intent.payment_failed'       => $this->handlePagamentoFalhou($evento),
            'customer.subscription.updated'       => $this->handleSubscriptionUpdated($evento),
            'customer.subscription.deleted'       => $this->handleSubscriptionDeleted($evento),
            default                               => null, 
        };
    }

    /**
     * Sincroniza o status da assinatura local com os dados do Stripe.
     */
    private function handleSubscriptionUpdated(Event $evento): void
    {
        $subscription = $evento->data->object;
        
        $assinaturaLocal = \App\Models\SaaS\Assinatura::where('stripe_subscription_id', $subscription->id)
            ->orWhere('stripe_customer_id', $subscription->customer)
            ->first();

        if ($assinaturaLocal) {
            $assinaturaLocal->update([
                'status'                    => $subscription->status,
                'stripe_subscription_id'    => $subscription->id,
                'stripe_customer_id'        => $subscription->customer,
                'trial_ends_at'             => $subscription->trial_end ? \Carbon\Carbon::createFromTimestamp($subscription->trial_end) : null,
                'ends_at'                   => $subscription->cancel_at ? \Carbon\Carbon::createFromTimestamp($subscription->cancel_at) : null,
            ]);

            Cache::forget('saas_assinatura_ativa');
        }
    }

    /**
     * Assinatura cancelada ou encerrada no Stripe.
     */
    private function handleSubscriptionDeleted(Event $evento): void
    {
        $subscription = $evento->data->object;

        $assinaturaLocal = \App\Models\SaaS\Assinatura::where('stripe_subscription_id', $subscription->id)->first();

        if ($assinaturaLocal) {
            $assinaturaLocal->update([
                'status'  => 'canceled',
                'ends_at' => now(),
            ]);

            Cache::forget('saas_assinatura_ativa');
        }
    }

    /**
     * Pagamento confirmado com sucesso (Checkout Único).
     */
    private function handleCheckoutCompleted(Event $evento): void
    {
        /** @var Session $session */
        $session = $evento->data->object;

        $pagamento = Pagamento::query()
            ->where('stripe_session_id', $session->id)
            ->first();

        if (! $pagamento) {
            Log::error('[Stripe] Webhook checkout.session.completed: pagamento não encontrado.', [
                'session_id' => $session->id,
            ]);
            return;
        }

        // Evita reprocessamento (idempotência)
        if ($pagamento->status === 'pago') {
            return;
        }

        $pagamento->update([
            'status'                    => 'pago',
            'stripe_payment_intent_id'  => $session->payment_intent,
            'transaction_id'            => $session->payment_intent,
            'payload_original'          => array_merge(
                $pagamento->payload_original ?? [],
                ['completed_at' => now()->toIso8601String()]
            ),
        ]);

        // Registra Entrada no Financeiro
        MovimentacaoFinanceira::create([
            'tipo'              => MovimentacaoFinanceira::TIPO_ENTRADA,
            'categoria'         => 'Venda Online (Stripe)',
            'valor'             => $pagamento->valor,
            'data_movimentacao' => now(),
            'forma_pagamento'   => 'Stripe',
            'status'            => MovimentacaoFinanceira::STATUS_PAGO,
            'pedido_id'         => $pagamento->pedido_id,
            'pagamento_id'      => $pagamento->id,
            'usuario_id'        => null, // Webhook não tem usuário logado
            'descricao'         => "Pagamento confirmado via Stripe — Sessão {$session->id}",
        ]);

        $pedido = $pagamento->pedido;

        if ($pedido && $pedido->status !== 'em_producao') {
            $statusAnterior = $pedido->status;
            $pedido->update(['status' => 'em_producao']);

            HistoricoPedido::query()->create([
                'pedido_id'      => $pedido->id,
                'status_anterior' => $statusAnterior,
                'status_novo'    => 'em_producao',
                'descricao'      => 'Pagamento confirmado via Stripe (' . ucfirst($pagamento->tipo_cobranca) . ').',
                'usuario_id'     => null,
            ]);
        }

        Log::info('[Stripe] Pagamento confirmado.', [
            'pedido_id'  => $pagamento->pedido_id,
            'session_id' => $session->id,
        ]);
    }

    /**
     * Sessão de checkout expirou sem pagamento.
     */
    private function handleCheckoutExpired(Event $evento): void
    {
        /** @var Session $session */
        $session = $evento->data->object;

        Pagamento::query()
            ->where('stripe_session_id', $session->id)
            ->where('status', 'pendente')
            ->update(['status' => 'expirado']);

        Log::info('[Stripe] Checkout session expirada.', ['session_id' => $session->id]);
    }

    /**
     * Tentativa de pagamento falhou.
     */
    private function handlePagamentoFalhou(Event $evento): void
    {
        $paymentIntent = $evento->data->object;

        Pagamento::query()
            ->where('stripe_payment_intent_id', $paymentIntent->id)
            ->where('status', 'pendente')
            ->update(['status' => 'falhou']);

        Log::warning('[Stripe] Tentativa de pagamento falhou.', [
            'payment_intent_id' => $paymentIntent->id,
        ]);
    }
}
