<?php

declare(strict_types=1);

namespace App\Services\Pix;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 19:58 -03:00
*/

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class AsaasService
{
    public function __construct(
        private readonly string $urlBase,
        private readonly string $chaveApi,
        private readonly string $tokenWebhook
    ) {
    }

    public function clienteHttp(): PendingRequest
    {
        return Http::baseUrl($this->urlBase)
            ->acceptJson()
            ->withHeaders([
                'access_token' => $this->chaveApi,
                'content-type' => 'application/json',
            ]);
    }

    public function criarCobrancaPix(array $dados): array
    {
        $resposta = $this->clienteHttp()->post('/v3/payments', [
            'customer' => $dados['customer'],
            'billingType' => 'PIX',
            'value' => $dados['value'],
            'dueDate' => $dados['dueDate'],
            'description' => $dados['description'] ?? 'Pedido gráfica',
            'externalReference' => $dados['externalReference'] ?? null,
        ])->throw()->json();

        $pix = $this->clienteHttp()
            ->get('/v3/payments/' . $resposta['id'] . '/pixQrCode')
            ->throw()
            ->json();

        return [
            'id' => $resposta['id'],
            'status' => $resposta['status'],
            'codigo' => $pix['payload'] ?? null,
            'qr_code' => $pix['encodedImage'] ?? null,
        ];
    }

    public function validarAssinatura(?string $assinaturaRecebida): bool
    {
        return hash_equals($this->tokenWebhook, (string) $assinaturaRecebida);
    }
}
