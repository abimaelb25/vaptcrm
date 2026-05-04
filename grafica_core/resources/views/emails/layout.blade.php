{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 17/04/2026
Descrição: Layout base reutilizável para todos os e-mails transacionais do sistema.
Variáveis: $nomeLoja, $emailLoja (opcionais — possuem fallback seguro)
--}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $assunto ?? 'Notificação' }}</title>
    <style>
        /* Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f1f5f9; }

        /* Styles */
        .email-wrapper { width: 100%; background-color: #f1f5f9; padding: 40px 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .email-header { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 32px 40px; text-align: center; }
        .email-header h1 { color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 22px; font-weight: 800; margin: 0; letter-spacing: -0.5px; }
        .email-header .accent-bar { width: 60px; height: 4px; background-color: #f97316; margin: 12px auto 0; border-radius: 2px; }
        .email-body { padding: 40px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #334155; font-size: 15px; line-height: 1.7; }
        .email-body h2 { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 16px; }
        .email-body p { margin: 0 0 16px; }
        .email-body .highlight-box { background-color: #f8fafc; border-left: 4px solid #f97316; border-radius: 8px; padding: 20px 24px; margin: 24px 0; }
        .email-body .highlight-box .label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin: 0 0 4px; }
        .email-body .highlight-box .value { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; }
        .email-body .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-novo, .status-aguardando_aprovacao { background-color: #dbeafe; color: #1d4ed8; }
        .status-aprovado, .status-pagamento_aprovado { background-color: #dcfce7; color: #15803d; }
        .status-em_producao { background-color: #fef3c7; color: #b45309; }
        .status-pronto { background-color: #d1fae5; color: #047857; }
        .status-em_transporte { background-color: #e0e7ff; color: #4338ca; }
        .status-entregue { background-color: #bbf7d0; color: #166534; }
        .status-cancelado { background-color: #fee2e2; color: #b91c1c; }
        .btn-primary { display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #ffffff !important; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 700; letter-spacing: 0.3px; margin: 8px 0; }
        .btn-primary:hover { opacity: 0.9; }
        .items-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .items-table th { background-color: #f1f5f9; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        .items-table td { padding: 10px 12px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9; }
        .items-table .total-row td { font-weight: 700; font-size: 15px; border-top: 2px solid #e2e8f0; color: #1e293b; }
        .email-footer { background-color: #f8fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #e2e8f0; }
        .email-footer p { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 12px; color: #94a3b8; margin: 0 0 4px; }
        .email-footer a { color: #f97316; text-decoration: none; }

        @media only screen and (max-width: 620px) {
            .email-container { margin: 0 12px; }
            .email-header, .email-body, .email-footer { padding-left: 24px; padding-right: 24px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td align="center">
                    <div class="email-container">
                        {{-- Header --}}
                        <div class="email-header">
                            <h1>{{ $nomeLoja ?? 'Gráfica' }}</h1>
                            <div class="accent-bar"></div>
                        </div>

                        {{-- Body --}}
                        <div class="email-body">
                            @yield('content')
                        </div>

                        {{-- Footer --}}
                        <div class="email-footer">
                            <p>&copy; {{ date('Y') }} {{ $nomeLoja ?? 'Gráfica' }}. Todos os direitos reservados.</p>
                            @if(!empty($emailLoja))
                                <p>Dúvidas? Responda este e-mail ou entre em contato: <a href="mailto:{{ $emailLoja }}">{{ $emailLoja }}</a></p>
                            @endif
                            <p style="margin-top: 8px; font-size: 11px; color: #cbd5e1;">Este é um e-mail automático. Caso não tenha solicitado, ignore-o.</p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
