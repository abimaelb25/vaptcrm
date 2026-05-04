<?php

declare(strict_types=1);

namespace App\Mail;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Mailable de recuperação de senha com template customizado.
*/

use App\Models\Usuario;
use App\Models\Loja;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecuperacaoSenhaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Usuario $usuario,
        public string $token
    ) {}

    public function envelope(): Envelope
    {
        $loja = $this->usuario->loja;
        $nomeLoja = $loja ? $loja->nome_fantasia : 'VaptCRM';

        return new Envelope(
            subject: "[{$nomeLoja}] - Recuperação de senha",
            replyTo: [$loja?->responsavel_email ?? config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        $loja = $this->usuario->loja;
        $nomeLoja = $loja ? $loja->nome_fantasia : 'VaptCRM';

        $urlBase = rtrim(config('app.url'), '/');
        if ($loja && $loja->subdominio) {
            $host = parse_url(config('app.url'), PHP_URL_HOST);
            $urlBase = "http://{$loja->subdominio}.{$host}";
        }

        $urlRecuperacao = $urlBase . route('password.reset', [
            'token' => $this->token,
            'email' => $this->usuario->email,
        ], false);

        $expiraEmMinutos = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return new Content(
            view: 'emails.recuperacao-senha',
            with: [
                'usuario'          => $this->usuario,
                'nomeLoja'         => $nomeLoja,
                'emailLoja'        => $loja?->responsavel_email ?? config('mail.from.address'),
                'urlRecuperacao'   => $urlRecuperacao,
                'expiraEmMinutos'  => $expiraEmMinutos,
            ],
        );
    }
}
