<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SaaS;

use App\Http\Controllers\Controller;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\PagamentoSaaS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class BillingWebhookController extends Controller
{
    public function stripe(Request $request)
    {
        $payload = $request->all();
        $type = (string) ($payload['type'] ?? '');
        $object = $payload['data']['object'] ?? [];

        if ($type === 'invoice.paid') {
            $subscriptionId = (string) ($object['subscription'] ?? '');
            $assinatura = Assinatura::query()->where('gateway_subscription_id', $subscriptionId)->first();
            if ($assinatura) {
                $assinatura->update([
                    'status' => Assinatura::STATUS_ACTIVE,
                    'financial_status' => 'adimplente',
                    'last_payment_at' => now(),
                    'next_billing_at' => isset($object['period_end']) ? now()->createFromTimestamp((int) $object['period_end']) : $assinatura->next_billing_at,
                ]);

                PagamentoSaaS::query()->create([
                    'loja_id' => $assinatura->loja_id,
                    'assinatura_id' => $assinatura->id,
                    'stripe_invoice_id' => (string) ($object['id'] ?? null),
                    'valor' => ((float) ($object['amount_paid'] ?? 0)) / 100,
                    'moeda' => strtoupper((string) ($object['currency'] ?? 'BRL')),
                    'status' => 'pago',
                    'periodo_inicio' => isset($object['period_start']) ? now()->createFromTimestamp((int) $object['period_start'])->toDateString() : now()->toDateString(),
                    'periodo_fim' => isset($object['period_end']) ? now()->createFromTimestamp((int) $object['period_end'])->toDateString() : now()->addMonth()->toDateString(),
                    'pago_em' => now(),
                    'vencimento_em' => now(),
                    'tentativas' => 1,
                ]);
            }
        }

        if ($type === 'invoice.payment_failed') {
            $subscriptionId = (string) ($object['subscription'] ?? '');
            $assinatura = Assinatura::query()->where('gateway_subscription_id', $subscriptionId)->first();
            if ($assinatura) {
                $assinatura->update([
                    'status' => Assinatura::STATUS_PAST_DUE,
                    'financial_status' => 'inadimplente',
                ]);
            }
        }

        if ($type === 'customer.subscription.deleted') {
            $subscriptionId = (string) ($object['id'] ?? '');
            $assinatura = Assinatura::query()->where('gateway_subscription_id', $subscriptionId)->first();
            if ($assinatura) {
                $assinatura->update([
                    'status' => Assinatura::STATUS_CANCELADA,
                    'financial_status' => 'inadimplente',
                    'canceled_at' => now(),
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    public function mercadoPago(Request $request)
    {
        $secret = (string) config('mercadopago.webhook_secret', '');
        $incoming = (string) $request->header('X-Signature', '');

        if ($secret !== '' && $incoming !== '' && ! hash_equals($secret, $incoming)) {
            return response()->json(['ok' => false, 'message' => 'invalid signature'], 401);
        }

        $status = (string) $request->input('status', '');
        $externalReference = (string) $request->input('external_reference', '');

        if ($externalReference === '') {
            return response()->json(['ok' => true]);
        }

        $assinatura = Assinatura::query()->where('gateway_subscription_id', $externalReference)->first();
        if (! $assinatura) {
            Log::warning('[SaaS][MP] assinatura não encontrada', ['external_reference' => $externalReference]);
            return response()->json(['ok' => true]);
        }

        if (in_array($status, ['approved', 'accredited'], true)) {
            $assinatura->update([
                'status' => Assinatura::STATUS_ACTIVE,
                'financial_status' => 'adimplente',
                'last_payment_at' => now(),
                'next_billing_at' => $assinatura->billing_cycle === Assinatura::BILLING_YEARLY ? now()->addYear() : now()->addMonth(),
            ]);
        } elseif (in_array($status, ['rejected', 'cancelled', 'refunded', 'charged_back'], true)) {
            $assinatura->update([
                'status' => Assinatura::STATUS_PAST_DUE,
                'financial_status' => 'inadimplente',
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
