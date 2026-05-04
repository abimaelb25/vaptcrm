<?php

declare(strict_types=1);

namespace App\Notifications;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Notificação de recuperação de senha usando Mailable com template Blade customizado.
*/

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Mail\RecuperacaoSenhaMail;

class RecuperacaoSenhaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public string $token
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): RecuperacaoSenhaMail
    {
        return (new RecuperacaoSenhaMail($notifiable, $this->token))
            ->to($notifiable->email);
    }
}
