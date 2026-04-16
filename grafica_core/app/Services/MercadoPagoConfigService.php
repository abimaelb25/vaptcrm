<?php

declare(strict_types=1);

namespace App\Services;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-11 01:38 -03:00
*/

use App\Models\IntegracaoPagamento;

class MercadoPagoConfigService
{
    public function __construct(
        protected PaymentIntegrationService $integrationService
    ) {}

    public function getConfig(?int $lojaId, ?int $userId = null): ?array
    {
        $integration = $this->integrationService->getActiveIntegration(
            $lojaId,
            IntegracaoPagamento::GATEWAY_MERCADO_PAGO,
            $userId
        );

        if (! $integration) {
            return null;
        }

        $credenciais = $integration->credenciais ?? [];
        $config = $integration->config_json ?? [];

        return [
            'ativo' => $integration->ativo,
            'ambiente' => $integration->ambiente,
            'public_key' => $credenciais['public_key'] ?? null,
            'access_token' => $credenciais['access_token'] ?? null,
            'meios_habilitados' => [
                'pix' => $config['pix'] ?? true,
                'cartao' => $config['cartao'] ?? true,
                'boleto' => $config['boleto'] ?? true,
            ],
        ];
    }

    public function testConnection(?int $lojaId, ?int $userId = null): array
    {
        $config = $this->getConfig($lojaId, $userId);

        if (! $config) {
            return [
                'sucesso' => false,
                'mensagem' => 'Integração Mercado Pago não configurada.',
            ];
        }

        if (empty($config['access_token'])) {
            return [
                'sucesso' => false,
                'mensagem' => 'Access Token não configurado.',
            ];
        }

        return [
            'sucesso' => true,
            'mensagem' => 'Credenciais configuradas. Integração real será implementada futuramente.',
            'ambiente' => $config['ambiente'],
        ];
    }

    public function prepareCheckout(?int $lojaId, array $pedidoData, ?int $userId = null): array
    {
        $config = $this->getConfig($lojaId, $userId);

        if (! $config || ! $config['ativo']) {
            return [
                'sucesso' => false,
                'mensagem' => 'Pagamento online não disponível no momento.',
            ];
        }

        return [
            'sucesso' => true,
            'mensagem' => 'Estrutura preparada para integração futura com Mercado Pago.',
            'config' => [
                'public_key' => $config['public_key'],
                'meios' => $config['meios_habilitados'],
            ],
        ];
    }
}
