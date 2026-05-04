<?php

declare(strict_types=1);

namespace App\Mail;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Mailable de boas-vindas (onboarding) para novas lojas SaaS.
*/

use App\Models\Loja;
use App\Models\SaaS\Plano;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoasVindasLojaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Loja $loja,
        public Plano $plano
    ) {}

    public function envelope(): Envelope
    {
        $nomeLoja = $this->loja->nome_fantasia ?? 'Gráfica';

        return new Envelope(
            subject: "[{$nomeLoja}] - Bem-vindo(a)! Ative sua assinatura",
        );
    }

    public function content(): Content
    {
        $urlBase = rtrim(config('app.url'), '/');
        $subdominio = $this->loja->subdominio;

        if ($subdominio) {
            $host = parse_url(config('app.url'), PHP_URL_HOST);
            $urlBase = "http://{$subdominio}.{$host}";
        }

        return new Content(
            view: 'emails.boas-vindas',
            with: [
                'loja'           => $this->loja,
                'plano'          => $this->plano,
                'nomeLoja'       => $this->loja->nome_fantasia ?? 'Gráfica',
                'emailLoja'      => $this->loja->responsavel_email ?? config('mail.from.address'),
                'urlAssinatura'  => $urlBase . '/painel/assinatura',
            ],
        );
    }
}
