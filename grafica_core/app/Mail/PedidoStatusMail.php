<?php

declare(strict_types=1);

namespace App\Mail;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Mailable de atualização de status do pedido.
|            Usa template Blade customizado com layout profissional.
*/

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pedido $pedido
    ) {}

    public function envelope(): Envelope
    {
        $loja = $this->pedido->loja;
        $nomeLoja = $loja->nome_fantasia ?? 'Gráfica';
        $statusLabel = self::statusParaLabel($this->pedido->status);
        $codigoPedido = $this->pedido->codigo_pedido ?? $this->pedido->numero;

        return new Envelope(
            subject: "[{$nomeLoja}] - Pedido {$codigoPedido} atualizado para {$statusLabel}",
            replyTo: [$loja->responsavel_email ?? config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        $loja = $this->pedido->loja;
        $urlBase = rtrim(config('app.url'), '/');
        $codigoPedido = $this->pedido->codigo_pedido ?? $this->pedido->numero;

        return new Content(
            view: 'emails.pedido-status',
            with: [
                'pedido'             => $this->pedido->load('itens', 'cliente'),
                'nomeLoja'           => $loja->nome_fantasia ?? 'Gráfica',
                'emailLoja'          => $loja->responsavel_email ?? config('mail.from.address'),
                'statusLabel'        => self::statusParaLabel($this->pedido->status),
                'codigoPedido'       => $codigoPedido,
                'urlAcompanhamento'  => $urlBase . '/acompanhar-pedido/' . $codigoPedido,
            ],
        );
    }

    /**
     * Traduz o status técnico em label legível para o cliente.
     */
    public static function statusParaLabel(string $status): string
    {
        return match ($status) {
            'rascunho'              => 'Rascunho',
            'aguardando_aprovacao'  => 'Aguardando Aprovação',
            'aprovado'              => 'Aprovado',
            'em_producao'           => 'Em Produção',
            'pronto'                => 'Pronto para Retirada',
            'em_transporte'         => 'Em Transporte',
            'entregue'              => 'Entregue',
            'cancelado'             => 'Cancelado',
            'aguardando_pagamento'  => 'Aguardando Pagamento',
            'pagamento_aprovado'    => 'Pagamento Aprovado',
            default                 => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
