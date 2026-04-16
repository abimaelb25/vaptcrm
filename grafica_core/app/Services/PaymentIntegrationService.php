<?php

declare(strict_types=1);

namespace App\Services;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-11 01:35 -03:00
*/

use App\Models\IntegracaoPagamento;
use Illuminate\Support\Facades\Cache;

class PaymentIntegrationService
{
    public function getActiveIntegration(?int $lojaId, string $gateway, ?int $userId = null): ?IntegracaoPagamento
    {
        $cacheKey = $this->cacheKey($lojaId, $userId, $gateway);

        return Cache::remember(
            $cacheKey,
            3600,
            function () use ($lojaId, $gateway, $userId) {
                $query = IntegracaoPagamento::query()
                    ->where('gateway', $gateway)
                    ->where('ativo', true);

                if ($lojaId) {
                    $query->where('loja_id', $lojaId);
                } elseif ($userId) {
                    $query->where('user_id', $userId);
                }

                return $query->first();
            }
        );
    }

    public function getAllByTenant(?int $lojaId, ?int $userId = null): array
    {
        $query = IntegracaoPagamento::query();

        if ($lojaId) {
            $query->where('loja_id', $lojaId);
        } elseif ($userId) {
            $query->where('user_id', $userId);
        }

        $integrations = $query->get();

        return [
            'mercado_pago' => $integrations->firstWhere('gateway', IntegracaoPagamento::GATEWAY_MERCADO_PAGO),
            'stripe' => $integrations->firstWhere('gateway', IntegracaoPagamento::GATEWAY_STRIPE),
            'paypal' => $integrations->firstWhere('gateway', IntegracaoPagamento::GATEWAY_PAYPAL),
            'asaas' => $integrations->firstWhere('gateway', IntegracaoPagamento::GATEWAY_ASAAS),
            'pix_manual' => $integrations->firstWhere('gateway', IntegracaoPagamento::GATEWAY_PIX_MANUAL),
        ];
    }

    public function saveOrUpdate(array $data, ?int $lojaId, int $userId): IntegracaoPagamento
    {
        $integration = IntegracaoPagamento::updateOrCreate(
            [
                'loja_id' => $lojaId,
                'gateway' => $data['gateway'],
            ],
            [
                'user_id' => $userId,
                'ativo' => $data['ativo'] ?? false,
                'ambiente' => $data['ambiente'] ?? 'sandbox',
                'credenciais' => $data['credenciais'] ?? null,
                'config_json' => $data['config_json'] ?? null,
            ]
        );

        $this->clearCache($lojaId, $data['gateway'], $userId);

        return $integration;
    }

    public function clearCache(?int $lojaId, string $gateway, ?int $userId = null): void
    {
        Cache::forget($this->cacheKey($lojaId, $userId, $gateway));
    }

    private function cacheKey(?int $lojaId, ?int $userId, string $gateway): string
    {
        $scope = $lojaId ? "loja:{$lojaId}" : 'user:' . ($userId ?? 'global');

        return "payment_integration:{$scope}:{$gateway}";
    }
}
