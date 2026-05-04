<?php

declare(strict_types=1);

namespace App\Jobs;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Job agendado para verificar pedidos atrasados e notificar a equipe.
|            Deve ser registrado no Scheduler (Kernel ou console.php).
*/

use App\Models\Pedido;
use App\Models\Usuario;
use App\Notifications\InternaPedidoAtrasadoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class VerificarPedidosAtrasadosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $pedidosAtrasados = Pedido::whereNotNull('prazo_entrega')
            ->where('prazo_entrega', '<', now())
            ->whereNotIn('status', [
                Pedido::STATUS_ENTREGUE,
                Pedido::STATUS_CANCELADO,
            ])
            ->with(['cliente', 'loja'])
            ->get();

        foreach ($pedidosAtrasados as $pedido) {
            $destinatarios = Usuario::where('loja_id', $pedido->loja_id)
                ->whereIn('perfil', ['administrador', 'gerente'])
                ->where('ativo', true)
                ->get();

            if ($destinatarios->isNotEmpty()) {
                Notification::send($destinatarios, new InternaPedidoAtrasadoNotification($pedido));
            }
        }

        Log::info("VerificarPedidosAtrasados: {$pedidosAtrasados->count()} pedidos atrasados notificados.");
    }
}
