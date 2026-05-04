<?php

declare(strict_types=1);

namespace App\Notifications;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Notificação de atualização de status do pedido.
|            Envia e-mail ao cliente usando Mailable com template Blade customizado.
|            Também registra notificação interna (database) para a equipe da loja.
*/

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Mail\PedidoStatusMail;
use App\Models\Pedido;

class StatusPedidoAtualizadoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        protected Pedido $pedido
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): PedidoStatusMail
    {
        return (new PedidoStatusMail($this->pedido))
            ->to($notifiable->email);
    }
}
