<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 19:58 -03:00
*/

use App\Http\Controllers\Controller;
use App\Models\HistoricoPedido;
use App\Models\Pagamento;
use App\Models\Pedido;
use App\Services\Pix\AsaasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookAsaasController extends Controller
{
    public function __invoke(Request $request, AsaasService $asaasService): JsonResponse
    {
        $assinatura = $request->header('asaas-access-token');

        if (! $asaasService->validarAssinatura($assinatura)) {
            Log::warning('Webhook Asaas rejeitado por assinatura inválida.', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['mensagem' => 'assinatura inválida'], 401);
        }

        $dados = $request->validate([
            'event' => ['required', 'string'],
            'payment.id' => ['required', 'string'],
            'payment.status' => ['required', 'string'],
        ]);

        $pagamento = Pagamento::query()
            ->where('transaction_id', $dados['payment']['id'])
            ->first();

        if ($pagamento === null) {
            Log::warning('Webhook Asaas sem cobrança correspondente.', [
                'transaction_id' => $dados['payment']['id'],
            ]);

            return response()->json(['mensagem' => 'cobrança não encontrada'], 404);
        }

        $pagamento->update([
            'status' => strtolower($dados['payment']['status']),
            'assinatura_gateway' => $assinatura,
            'payload_original' => $request->all(),
        ]);

        if (strtoupper($dados['payment']['status']) === 'RECEIVED') {
            $pedido = Pedido::query()->find($pagamento->pedido_id);

            if ($pedido !== null && $pedido->status !== 'pagamento_aprovado') {
                $statusAnterior = $pedido->status;
                $pedido->update(['status' => 'pagamento_aprovado']);

                HistoricoPedido::query()->create([
                    'pedido_id' => $pedido->id,
                    'status_anterior' => $statusAnterior,
                    'status_novo' => 'pagamento_aprovado',
                    'descricao' => 'Status atualizado automaticamente por webhook Asaas.',
                    'usuario_id' => $pedido->responsavel_id,
                ]);
            }
        }

        Log::info('Webhook Asaas processado com sucesso.', [
            'pagamento_id' => $pagamento->id,
            'status' => $pagamento->status,
        ]);

        return response()->json(['mensagem' => 'ok']);
    }
}
