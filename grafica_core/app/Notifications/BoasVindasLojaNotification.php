<?php

declare(strict_types=1);

namespace App\Notifications;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Notificação de onboarding para novas lojas SaaS.
|            Envia e-mail de boas-vindas ao responsável usando Mailable com template Blade.
*/

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Mail\BoasVindasLojaMail;
use App\Models\Loja;
use App\Models\SaaS\Plano;

class BoasVindasLojaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        protected Loja $loja,
        protected Plano $plano
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): BoasVindasLojaMail
    {
        return (new BoasVindasLojaMail($this->loja, $this->plano))
            ->to($notifiable->email);
    }
}
