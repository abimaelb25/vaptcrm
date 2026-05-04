<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AcademyQuizManagerAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly array $payload,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'tipo' => 'academy_quiz_alerta',
            'titulo' => $this->payload['titulo'] ?? 'Alerta de quiz',
            'mensagem' => $this->payload['mensagem'] ?? 'Foi registrado um evento de quiz.',
            'usuario_id' => $this->payload['usuario_id'] ?? null,
            'usuario_nome' => $this->payload['usuario_nome'] ?? null,
            'help_content_id' => $this->payload['help_content_id'] ?? null,
            'help_content_titulo' => $this->payload['help_content_titulo'] ?? null,
            'tentativa' => $this->payload['tentativa'] ?? null,
            'percentual_acerto' => $this->payload['percentual_acerto'] ?? null,
            'erros' => $this->payload['erros'] ?? null,
        ];
    }
}
