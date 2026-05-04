<?php

declare(strict_types=1);

namespace App\Services\System;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Serviço para configuração dinâmica de SMTP por loja (multi-tenant).
|            Permite que cada loja envie e-mails com seu próprio remetente e servidor SMTP.
|            Utiliza fallback seguro para o SMTP padrão do sistema caso a loja não tenha config.
*/

use App\Models\Loja;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;

class TenantMailService
{
    /**
     * Configura o mailer padrão para enviar e-mails com as credenciais SMTP da loja.
     * Se a loja não possuir SMTP próprio, utiliza o mailer padrão do sistema.
     *
     * Retorna true se configurou SMTP customizado, false se usou o padrão.
     */
    public function configurarSmtpParaLoja(Loja $loja): bool
    {
        if (!$loja->possuiSmtpProprio()) {
            // Fallback: Usa config padrão, mas seta from com dados da loja
            Config::set('mail.from.address', $loja->getFromAddress());
            Config::set('mail.from.name', $loja->getFromName());
            return false;
        }

        // Configurar mailer dinâmico com SMTP da loja
        $smtpConfig = $loja->getSmtpConfig();

        Config::set('mail.mailers.tenant', $smtpConfig);
        Config::set('mail.default', 'tenant');
        Config::set('mail.from.address', $loja->getFromAddress());
        Config::set('mail.from.name', $loja->getFromName());

        return true;
    }

    /**
     * Restaura a configuração de mail para o mailer padrão do sistema.
     * Deve ser chamado após o envio para evitar contaminação entre tenants.
     */
    public function restaurarSmtpPadrao(): void
    {
        Config::set('mail.default', config('mail.default_backup', env('MAIL_MAILER', 'smtp')));
        Config::set('mail.from.address', env('MAIL_FROM_ADDRESS', 'hello@example.com'));
        Config::set('mail.from.name', env('MAIL_FROM_NAME', config('app.name')));
    }
}
