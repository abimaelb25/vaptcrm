<?php

declare(strict_types=1);

namespace App\Notifications;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
*/

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Pedido;

class InternaPedidoCriadoNotification extends Notification
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
            'tipo'      => 'novo_pedido',
            'pedido_id' => $this->pedido->id,
            'numero'    => $this->pedido->numero,
            'cliente'   => $this->pedido->cliente ? $this->pedido->cliente->nome : 'Cliente Desconhecido',
            'valor'     => $this->pedido->total,
            'mensagem'  => "Um novo pedido (#{$this->pedido->numero}) foi criado.",
        ];
    }
}
