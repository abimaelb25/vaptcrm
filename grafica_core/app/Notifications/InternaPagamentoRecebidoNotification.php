<?php

declare(strict_types=1);

namespace App\Notifications;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Notificação interna (database) para pagamento recebido.
|            Enviada para administradores/gerentes/financeiro da loja via CRM interno (sino).
*/

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Pagamento;

class InternaPagamentoRecebidoNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Pagamento $pagamento
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $pedido = $this->pagamento->pedido;

        return [
            'tipo'         => 'pagamento',
            'pagamento_id' => $this->pagamento->id,
            'pedido_id'    => $pedido?->id,
            'numero'       => $pedido?->numero ?? '-',
            'valor'        => $this->pagamento->valor,
            'metodo'       => $this->pagamento->metodo,
            'mensagem'     => "Pagamento de R$ " . number_format($this->pagamento->valor, 2, ',', '.') . " recebido para o pedido #{$pedido?->numero}.",
        ];
    }
}
