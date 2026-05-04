<?php

declare(strict_types=1);

namespace App\Services\System;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Orquestrador central de notificações e comunicações (E-mail + Banco).
|            Toda lógica de envio passa por aqui — controllers NUNCA enviam diretamente.
|
| Fluxo: Controller → Service → NotificationService → Job (queue) → Mail/Notification
*/

use App\Jobs\EnviarEmailBoasVindasJob;
use App\Jobs\EnviarEmailPedidoStatusJob;
use App\Jobs\EnviarEmailRecuperacaoSenhaJob;
use App\Models\Loja;
use App\Models\Pagamento;
use App\Models\Pedido;
use App\Models\SaaS\Plano;
use App\Models\Usuario;
use App\Notifications\InternaPagamentoRecebidoNotification;
use App\Notifications\InternaPedidoCriadoNotification;
use App\Notifications\InternaTicketCriadoNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Notifica o cliente sobre mudança de status do pedido (E-MAIL via Job).
     * Dispara job assíncrono que configura SMTP da loja e registra log.
     */
    public function notifyStatusUpdate(Pedido $pedido): void
    {
        if (!$pedido->cliente || !$pedido->cliente->email) {
            Log::warning("NotificationService::notifyStatusUpdate - Cliente sem e-mail para pedido #{$pedido->numero}");
            return;
        }

        EnviarEmailPedidoStatusJob::dispatch($pedido);
    }

    /**
     * Notifica o lojista sobre a criação da loja - Onboarding (E-MAIL via Job).
     */
    public function notifyOnboarding(Loja $loja, Usuario $admin, Plano $plano): void
    {
        if (!$admin->email) {
            Log::warning("NotificationService::notifyOnboarding - Admin sem e-mail para loja #{$loja->id}");
            return;
        }

        EnviarEmailBoasVindasJob::dispatch($loja, $admin, $plano);
    }

    /**
     * Envia e-mail de recuperação de senha (E-MAIL via Job).
     */
    public function notifyPasswordReset(Usuario $usuario, string $token): void
    {
        if (!$usuario->email) {
            Log::warning("NotificationService::notifyPasswordReset - Usuário sem e-mail ID #{$usuario->id}");
            return;
        }

        EnviarEmailRecuperacaoSenhaJob::dispatch($usuario, $token);
    }

    /**
     * Notifica a equipe interna da loja sobre um novo pedido (BANCO - sino).
     */
    public function notifyNewOrderInternally(Pedido $pedido): void
    {
        $destinatarios = $this->getDestinatariosInternos($pedido->loja_id);

        if ($destinatarios->isNotEmpty()) {
            Notification::send($destinatarios, new InternaPedidoCriadoNotification($pedido));
        }
    }

    /**
     * Notifica a equipe interna sobre pagamento recebido (BANCO - sino).
     */
    public function notifyPaymentReceived(Pagamento $pagamento): void
    {
        $lojaId = $pagamento->pedido?->loja_id;
        if (!$lojaId) {
            return;
        }

        $destinatarios = $this->getDestinatariosInternos($lojaId, ['administrador', 'gerente', 'financeiro']);

        if ($destinatarios->isNotEmpty()) {
            Notification::send($destinatarios, new InternaPagamentoRecebidoNotification($pagamento));
        }
    }

    /**
     * Notifica a equipe interna sobre ticket de suporte criado (BANCO - sino).
     */
    public function notifyTicketCreated(int $ticketId, string $assunto, string $nomeAutor, int $lojaId): void
    {
        $destinatarios = $this->getDestinatariosInternos($lojaId);

        if ($destinatarios->isNotEmpty()) {
            Notification::send(
                $destinatarios,
                new InternaTicketCriadoNotification($ticketId, $assunto, $nomeAutor, $lojaId)
            );
        }
    }

    /**
     * Retorna usuários internos elegíveis para notificações de uma loja.
     */
    private function getDestinatariosInternos(int $lojaId, array $perfis = ['administrador', 'gerente']): \Illuminate\Database\Eloquent\Collection
    {
        return Usuario::where('loja_id', $lojaId)
            ->whereIn('perfil', $perfis)
            ->where('ativo', true)
            ->get();
    }
}
