{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 17/04/2026
Descrição: Template de e-mail para recuperação de senha.
Variáveis: $usuario, $nomeLoja, $emailLoja, $urlRecuperacao, $expiraEmMinutos
--}}
@extends('emails.layout')

@section('content')
    <h2>Olá, {{ $usuario->nome ?? 'Usuário' }}!</h2>

    <p>Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.</p>

    {{-- CTA --}}
    <div style="text-align: center; margin: 32px 0;">
        <a href="{{ $urlRecuperacao }}" class="btn-primary">Redefinir Minha Senha</a>
    </div>

    <div class="highlight-box">
        <p class="label">Validade do Link</p>
        <p class="value">{{ $expiraEmMinutos }} minutos</p>
    </div>

    <p style="color: #64748b; font-size: 13px;">
        Se você não solicitou a redefinição de senha, nenhuma ação adicional será necessária.
        O link expira automaticamente.
    </p>

    <p style="margin-top: 24px; color: #64748b; font-size: 13px;">
        Caso o botão acima não funcione, copie e cole o seguinte link no seu navegador:
    </p>
    <p style="word-break: break-all; font-size: 12px; color: #94a3b8; background-color: #f8fafc; padding: 12px; border-radius: 8px;">
        {{ $urlRecuperacao }}
    </p>
@endsection
