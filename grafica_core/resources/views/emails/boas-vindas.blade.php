{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 17/04/2026
Descrição: Template de e-mail de boas-vindas (onboarding) para nova loja SaaS.
Variáveis: $loja, $plano, $nomeLoja, $emailLoja, $urlAssinatura
--}}
@extends('emails.layout')

@section('content')
    <h2>Bem-vindo(a), {{ $loja->responsavel_nome ?? 'Empreendedor(a)' }}!</h2>

    <p>Sua loja <strong>{{ $loja->nome_fantasia }}</strong> foi criada com sucesso e já está pronta para receber seus primeiros pedidos.</p>

    {{-- Info do Plano --}}
    <div class="highlight-box">
        <p class="label">Plano Escolhido</p>
        <p class="value">{{ $plano->nome ?? 'Básico' }}</p>
    </div>

    <div class="highlight-box" style="margin-top: 12px;">
        <p class="label">Trial Gratuito até</p>
        <p class="value">{{ $loja->trial_ends_at ? $loja->trial_ends_at->format('d/m/Y \à\s H:i') : 'Não aplicável' }}</p>
    </div>

    <p style="margin-top: 24px;">
        Durante o período de trial, você tem acesso completo a todas as funcionalidades da plataforma.
        Recomendamos que ative sua assinatura antes do vencimento para evitar interrupções.
    </p>

    {{-- Próximos passos --}}
    <h2 style="margin-top: 32px;">Próximos Passos</h2>
    <table style="width: 100%; margin: 16px 0;">
        <tr>
            <td style="padding: 8px 0; vertical-align: top; width: 30px;">
                <span style="display: inline-block; width: 24px; height: 24px; background-color: #f97316; color: #fff; border-radius: 50%; text-align: center; line-height: 24px; font-size: 12px; font-weight: 700;">1</span>
            </td>
            <td style="padding: 8px 0; font-size: 14px; color: #334155;">
                <strong>Configure sua loja</strong> — Personalize cores, logo e informações de contato.
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; vertical-align: top;">
                <span style="display: inline-block; width: 24px; height: 24px; background-color: #f97316; color: #fff; border-radius: 50%; text-align: center; line-height: 24px; font-size: 12px; font-weight: 700;">2</span>
            </td>
            <td style="padding: 8px 0; font-size: 14px; color: #334155;">
                <strong>Cadastre seus produtos</strong> — Inclua catálogo, preços e descrições.
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; vertical-align: top;">
                <span style="display: inline-block; width: 24px; height: 24px; background-color: #f97316; color: #fff; border-radius: 50%; text-align: center; line-height: 24px; font-size: 12px; font-weight: 700;">3</span>
            </td>
            <td style="padding: 8px 0; font-size: 14px; color: #334155;">
                <strong>Ative a assinatura</strong> — Garanta acesso contínuo após o trial.
            </td>
        </tr>
    </table>

    {{-- CTA --}}
    <div style="text-align: center; margin-top: 32px;">
        <a href="{{ $urlAssinatura }}" class="btn-primary">Ativar Minha Assinatura</a>
    </div>

    <p style="margin-top: 32px; color: #64748b; font-size: 13px;">
        Muito sucesso nas vendas! Estamos aqui para ajudá-lo em cada etapa.
    </p>
@endsection
