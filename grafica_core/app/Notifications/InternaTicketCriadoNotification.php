<?php

declare(strict_types=1);

namespace App\Notifications;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Notificação interna (database) para ticket de suporte criado.
|            Enviada para administradores/gerentes da loja via CRM interno (sino).
*/

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InternaTicketCriadoNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected int $ticketId,
        protected string $assunto,
        protected string $nomeAutor,
        protected int $lojaId
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'tipo'      => 'ticket_suporte',
            'ticket_id' => $this->ticketId,
            'assunto'   => $this->assunto,
            'autor'     => $this->nomeAutor,
            'mensagem'  => "Novo ticket de suporte: \"{$this->assunto}\" aberto por {$this->nomeAutor}.",
        ];
    }
}
