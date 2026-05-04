<?php

declare(strict_types=1);

namespace App\Notifications;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Notificação interna (database) para pedidos atrasados.
|            Enviada para administradores/gerentes da loja via CRM interno (sino).
*/

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Pedido;

class InternaPedidoAtrasadoNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Pedido $pedido
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'tipo'      => 'atraso',
            'pedido_id' => $this->pedido->id,
            'numero'    => $this->pedido->numero,
            'cliente'   => $this->pedido->cliente?->nome ?? 'Cliente Desconhecido',
            'prazo'     => $this->pedido->prazo_entrega,
            'status'    => $this->pedido->status,
            'mensagem'  => "O pedido #{$this->pedido->numero} está atrasado! Prazo era: {$this->pedido->prazo_entrega}.",
        ];
    }
}
