<?php

declare(strict_types=1);

namespace App\Jobs;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Job assíncrono para envio de e-mail de boas-vindas (onboarding) à nova loja.
*/

use App\Mail\BoasVindasLojaMail;
use App\Models\EmailLog;
use App\Models\Loja;
use App\Models\SaaS\Plano;
use App\Models\Usuario;
use App\Services\System\TenantMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarEmailBoasVindasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        protected Loja $loja,
        protected Usuario $admin,
        protected Plano $plano
    ) {}

    public function handle(TenantMailService $tenantMail): void
    {
        if (!$this->admin->email) {
            Log::warning("EnviarEmailBoasVindas: Admin sem e-mail para loja #{$this->loja->id}");
            return;
        }

        try {
            // Boas-vindas sai do e-mail da plataforma (não da loja, pois é nova)
            Mail::to($this->admin->email)
                ->send(new BoasVindasLojaMail($this->loja, $this->plano));

            EmailLog::create([
                'loja_id'             => $this->loja->id,
                'tipo'                => 'boas_vindas',
                'destinatario_email'  => $this->admin->email,
                'destinatario_nome'   => $this->admin->nome,
                'assunto'             => "[{$this->loja->nome_fantasia}] - Bem-vindo(a)! Ative sua assinatura",
                'status'              => 'enviado',
                'referencia_type'     => Loja::class,
                'referencia_id'       => $this->loja->id,
                'metadata'            => [
                    'plano_nome'     => $this->plano->nome,
                    'trial_ends_at'  => $this->loja->trial_ends_at?->toISOString(),
                ],
            ]);

            Log::info("E-mail de boas-vindas enviado para {$this->admin->email} - Loja: {$this->loja->nome_fantasia}");
        } catch (\Throwable $e) {
            EmailLog::create([
                'loja_id'             => $this->loja->id,
                'tipo'                => 'boas_vindas',
                'destinatario_email'  => $this->admin->email,
                'destinatario_nome'   => $this->admin->nome,
                'assunto'             => "[{$this->loja->nome_fantasia}] - Boas-Vindas",
                'status'              => 'falhou',
                'referencia_type'     => Loja::class,
                'referencia_id'       => $this->loja->id,
                'erro'                => $e->getMessage(),
            ]);

            Log::error("Falha ao enviar e-mail de boas-vindas para loja #{$this->loja->id}: {$e->getMessage()}");

            throw $e;
        }
    }
}
