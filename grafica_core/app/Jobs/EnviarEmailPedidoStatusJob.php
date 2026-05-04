<?php

declare(strict_types=1);

namespace App\Jobs;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Job assíncrono para envio de e-mail de status do pedido.
|            Configura SMTP da loja antes do envio e registra log.
*/

use App\Mail\PedidoStatusMail;
use App\Models\EmailLog;
use App\Models\Pedido;
use App\Services\System\TenantMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarEmailPedidoStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        protected Pedido $pedido
    ) {}

    public function handle(TenantMailService $tenantMail): void
    {
        $pedido = $this->pedido->load(['cliente', 'loja', 'itens']);
        $loja = $pedido->loja;
        $cliente = $pedido->cliente;

        if (!$cliente || !$cliente->email) {
            Log::warning("EnviarEmailPedidoStatus: Cliente sem e-mail para pedido #{$pedido->numero}");
            return;
        }

        try {
            $tenantMail->configurarSmtpParaLoja($loja);

            Mail::to($cliente->email)
                ->send(new PedidoStatusMail($pedido));

            EmailLog::create([
                'loja_id'             => $loja->id,
                'tipo'                => 'pedido_status',
                'destinatario_email'  => $cliente->email,
                'destinatario_nome'   => $cliente->nome,
                'assunto'             => "[{$loja->nome_fantasia}] - Pedido #{$pedido->numero} atualizado para " . PedidoStatusMail::statusParaLabel($pedido->status),
                'status'              => 'enviado',
                'referencia_type'     => Pedido::class,
                'referencia_id'       => $pedido->id,
                'metadata'            => [
                    'pedido_numero' => $pedido->numero,
                    'status'        => $pedido->status,
                ],
            ]);

            Log::info("E-mail de status enviado para {$cliente->email} - Pedido #{$pedido->numero}");
        } catch (\Throwable $e) {
            EmailLog::create([
                'loja_id'             => $loja->id,
                'tipo'                => 'pedido_status',
                'destinatario_email'  => $cliente->email,
                'destinatario_nome'   => $cliente->nome,
                'assunto'             => "[{$loja->nome_fantasia}] - Pedido #{$pedido->numero}",
                'status'              => 'falhou',
                'referencia_type'     => Pedido::class,
                'referencia_id'       => $pedido->id,
                'erro'                => $e->getMessage(),
            ]);

            Log::error("Falha ao enviar e-mail de status do pedido #{$pedido->numero}: {$e->getMessage()}");

            throw $e; // Re-throw para retry automático
        } finally {
            $tenantMail->restaurarSmtpPadrao();
        }
    }
}
