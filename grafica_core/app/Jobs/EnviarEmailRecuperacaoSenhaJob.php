<?php

declare(strict_types=1);

namespace App\Jobs;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Job assíncrono para envio de e-mail de recuperação de senha.
*/

use App\Mail\RecuperacaoSenhaMail;
use App\Models\EmailLog;
use App\Models\Usuario;
use App\Services\System\TenantMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarEmailRecuperacaoSenhaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        protected Usuario $usuario,
        protected string $token
    ) {}

    public function handle(TenantMailService $tenantMail): void
    {
        $loja = $this->usuario->loja;

        if (!$this->usuario->email) {
            Log::warning("EnviarEmailRecuperacaoSenha: Usuário sem e-mail - ID {$this->usuario->id}");
            return;
        }

        try {
            if ($loja) {
                $tenantMail->configurarSmtpParaLoja($loja);
            }

            Mail::to($this->usuario->email)
                ->send(new RecuperacaoSenhaMail($this->usuario, $this->token));

            $nomeLoja = $loja?->nome_fantasia ?? 'VaptCRM';

            EmailLog::create([
                'loja_id'             => $loja?->id ?? 0,
                'tipo'                => 'recuperacao_senha',
                'destinatario_email'  => $this->usuario->email,
                'destinatario_nome'   => $this->usuario->nome,
                'assunto'             => "[{$nomeLoja}] - Recuperação de senha",
                'status'              => 'enviado',
                'referencia_type'     => Usuario::class,
                'referencia_id'       => $this->usuario->id,
            ]);

            Log::info("E-mail de recuperação de senha enviado para {$this->usuario->email}");
        } catch (\Throwable $e) {
            EmailLog::create([
                'loja_id'             => $loja?->id ?? 0,
                'tipo'                => 'recuperacao_senha',
                'destinatario_email'  => $this->usuario->email,
                'destinatario_nome'   => $this->usuario->nome,
                'assunto'             => 'Recuperação de Senha',
                'status'              => 'falhou',
                'referencia_type'     => Usuario::class,
                'referencia_id'       => $this->usuario->id,
                'erro'                => $e->getMessage(),
            ]);

            Log::error("Falha ao enviar e-mail de recuperação de senha para ID {$this->usuario->id}: {$e->getMessage()}");

            throw $e;
        } finally {
            if ($loja) {
                $tenantMail->restaurarSmtpPadrao();
            }
        }
    }
}
