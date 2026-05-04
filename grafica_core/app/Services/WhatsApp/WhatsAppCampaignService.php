<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Models\Cliente;
use App\Models\Loja;
use App\Models\Pedido;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppOptIn;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Resolves recipient segments for WhatsApp campaigns.
 *
 * Segment types:
 *  - opt_in_all           → all clients with active opt-in for this loja
 *  - pending_quote        → clients with orders in "orçamento" status without response for X days
 *  - repeat_product       → clients who purchased a specific product
 *  - inactive_days        → clients who haven't purchased in X days
 *
 * This service ONLY resolves recipients — it does NOT send messages.
 * Sending is handled by WhatsAppSettingsService (manual) or WhatsAppMessageService (API).
 *
 * Compliance:
 *  - Only clients with hasOptedIn() are included
 *  - No automated dispatch through non-official channels
 */
class WhatsAppCampaignService
{
    public function __construct(
        private WhatsAppSettingsService $settingsService,
        private WhatsAppAccountService $accountService,
    ) {}

    // -------------------------------------------------------------------------
    // Segment descriptors
    // -------------------------------------------------------------------------

    /**
     * @return array<string, string>
     */
    public function segmentOptions(): array
    {
        return [
            'opt_in_all'      => 'Todos os clientes com autorização de recebimento',
            'pending_quote'   => 'Clientes com orçamento sem resposta (parado)',
            'repeat_product'  => 'Clientes que compraram um produto específico',
            'inactive_days'   => 'Clientes sem compra há X dias',
        ];
    }

    /**
     * @return array<string, array>
     */
    public function segmentParamDefinitions(): array
    {
        return [
            'opt_in_all'     => [],
            'pending_quote'  => ['days_without_response' => ['type' => 'integer', 'default' => 3, 'label' => 'Dias sem resposta']],
            'repeat_product' => ['product_id' => ['type' => 'integer', 'label' => 'Produto']],
            'inactive_days'  => ['days' => ['type' => 'integer', 'default' => 60, 'label' => 'Dias sem compra']],
        ];
    }

    // -------------------------------------------------------------------------
    // Recipient resolution
    // -------------------------------------------------------------------------

    /**
     * Resolve recipients for a given segment type + params.
     * Returns array of ['cliente_id', 'phone', 'nome'] with opt-in validated.
     *
     * @param  array<string, mixed> $params
     * @return array<int, array{cliente_id: int, phone: string, nome: string}>
     */
    public function resolveRecipients(int $lojaId, string $segmentType, array $params = []): array
    {
        $optedInPhones = WhatsAppOptIn::where('loja_id', $lojaId)
            ->where('status', WhatsAppOptIn::STATUS_OPTED_IN)
            ->pluck('phone')
            ->flip()
            ->all();

        if (empty($optedInPhones)) {
            return [];
        }

        $clients = match ($segmentType) {
            'opt_in_all'     => $this->segmentOptInAll($lojaId),
            'pending_quote'  => $this->segmentPendingQuote($lojaId, (int) ($params['days_without_response'] ?? 3)),
            'repeat_product' => $this->segmentRepeatProduct($lojaId, (int) ($params['product_id'] ?? 0)),
            'inactive_days'  => $this->segmentInactiveDays($lojaId, (int) ($params['days'] ?? 60)),
            default          => collect(),
        };

        $recipients = [];
        foreach ($clients as $cliente) {
            $phone = $this->normalisePhone($cliente->whatsapp ?? $cliente->telefone ?? '');
            if (empty($phone)) {
                continue;
            }
            // Verify opt-in (digits only for comparison)
            $digits = preg_replace('/\D+/', '', $phone);
            $found  = false;
            foreach (array_keys($optedInPhones) as $optPhone) {
                if (str_contains($optPhone, $digits) || str_contains($digits, preg_replace('/\D+/', '', $optPhone))) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                continue;
            }
            $recipients[] = [
                'cliente_id' => $cliente->id,
                'phone'      => $phone,
                'nome'       => $cliente->nome ?? 'Cliente',
            ];
        }

        return $recipients;
    }

    /**
     * Count recipients without loading them all.
     */
    public function countRecipients(int $lojaId, string $segmentType, array $params = []): int
    {
        return count($this->resolveRecipients($lojaId, $segmentType, $params));
    }

    /**
     * Build wa.me links for all recipients of a campaign (manual mode).
     *
     * @return array<int, array{cliente_id: int, phone: string, nome: string, wa_me_link: string}>
     */
    public function buildManualLinks(Loja $loja, string $segmentType, array $params, string $message): array
    {
        $recipients = $this->resolveRecipients($loja->id, $segmentType, $params);
        $result     = [];

        foreach ($recipients as $recipient) {
            $digits = preg_replace('/\D+/', '', $recipient['phone']);
            if (empty($digits)) {
                continue;
            }

            $personalised = str_replace(
                ['{{nome_cliente}}', '{{nome do cliente}}'],
                $recipient['nome'],
                $message
            );

            $result[] = array_merge($recipient, [
                'wa_me_link' => 'https://wa.me/' . $digits . '?text=' . rawurlencode($personalised),
            ]);
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Segments
    // -------------------------------------------------------------------------

    private function segmentOptInAll(int $lojaId): Collection
    {
        return Cliente::where('loja_id', $lojaId)
            ->where(function ($q): void {
                $q->whereNotNull('whatsapp')->orWhereNotNull('telefone');
            })
            ->get(['id', 'nome', 'whatsapp', 'telefone']);
    }

    private function segmentPendingQuote(int $lojaId, int $daysWithoutResponse): Collection
    {
        $since = now()->subDays($daysWithoutResponse)->toDateTimeString();

        $pendingStatusValues = ['orcamento', 'aguardando', 'aguardando_aprovacao'];

        return Cliente::where('clientes.loja_id', $lojaId)
            ->join('pedidos', 'pedidos.cliente_id', '=', 'clientes.id')
            ->whereIn('pedidos.status', $pendingStatusValues)
            ->where('pedidos.updated_at', '<=', $since)
            ->where('pedidos.loja_id', $lojaId)
            ->where(function ($q): void {
                $q->whereNotNull('clientes.whatsapp')->orWhereNotNull('clientes.telefone');
            })
            ->distinct('clientes.id')
            ->get(['clientes.id', 'clientes.nome', 'clientes.whatsapp', 'clientes.telefone']);
    }

    private function segmentRepeatProduct(int $lojaId, int $produtoId): Collection
    {
        if ($produtoId === 0) {
            return collect();
        }

        return Cliente::where('clientes.loja_id', $lojaId)
            ->join('pedidos', 'pedidos.cliente_id', '=', 'clientes.id')
            ->join('itens_pedido', 'itens_pedido.pedido_id', '=', 'pedidos.id')
            ->where('itens_pedido.produto_id', $produtoId)
            ->where('pedidos.loja_id', $lojaId)
            ->where(function ($q): void {
                $q->whereNotNull('clientes.whatsapp')->orWhereNotNull('clientes.telefone');
            })
            ->distinct('clientes.id')
            ->get(['clientes.id', 'clientes.nome', 'clientes.whatsapp', 'clientes.telefone']);
    }

    private function segmentInactiveDays(int $lojaId, int $days): Collection
    {
        $since = now()->subDays($days)->toDateTimeString();

        // Sub-query: clients who have placed an order after the cutoff
        $activeClientIds = DB::table('pedidos')
            ->where('loja_id', $lojaId)
            ->where('created_at', '>=', $since)
            ->distinct()
            ->pluck('cliente_id');

        return Cliente::where('loja_id', $lojaId)
            ->whereNotIn('id', $activeClientIds)
            ->whereNotNull('id')
            ->where(function ($q): void {
                $q->whereNotNull('whatsapp')->orWhereNotNull('telefone');
            })
            ->get(['id', 'nome', 'whatsapp', 'telefone']);
    }

    // -------------------------------------------------------------------------
    // Phone helpers
    // -------------------------------------------------------------------------

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (empty($digits)) {
            return '';
        }
        if (strlen($digits) === 11 || strlen($digits) === 10) {
            return '+55' . $digits;
        }
        if (strlen($digits) >= 12) {
            return '+' . $digits;
        }
        return '+' . $digits;
    }
}
