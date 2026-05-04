{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 17/04/2026
Descrição: Template de e-mail para atualização de status do pedido.
Variáveis: $pedido, $nomeLoja, $emailLoja, $statusLabel, $urlAcompanhamento
--}}
@extends('emails.layout')

@section('content')
    <h2>Olá, {{ $pedido->cliente->nome ?? 'Cliente' }}!</h2>

    <p>Gostaríamos de informar que o seu pedido foi atualizado.</p>

    {{-- Info do Pedido --}}
    <div class="highlight-box">
        <p class="label">Pedido</p>
        <p class="value">#{{ $pedido->numero }}</p>
    </div>

    <div class="highlight-box" style="margin-top: 12px;">
        <p class="label">Novo Status</p>
        <p class="value">
            <span class="status-badge status-{{ $pedido->status }}">{{ $statusLabel }}</span>
        </p>
    </div>

    {{-- Resumo dos itens --}}
    @if($pedido->itens && $pedido->itens->count() > 0)
        <h2 style="margin-top: 32px;">Resumo do Pedido</h2>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align: center;">Qtd</th>
                    <th style="text-align: right;">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->itens as $item)
                    <tr>
                        <td>{{ $item->descricao_item }}</td>
                        <td style="text-align: center;">{{ $item->quantidade }}</td>
                        <td style="text-align: right;">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if($pedido->valor_frete > 0)
                    <tr>
                        <td colspan="2">Frete</td>
                        <td style="text-align: right;">R$ {{ number_format($pedido->valor_frete, 2, ',', '.') }}</td>
                    </tr>
                @endif
                @if($pedido->desconto > 0)
                    <tr>
                        <td colspan="2">Desconto</td>
                        <td style="text-align: right; color: #16a34a;">-R$ {{ number_format($pedido->desconto, 2, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td colspan="2">Total</td>
                    <td style="text-align: right;">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- CTA --}}
    <div style="text-align: center; margin-top: 32px;">
        <a href="{{ $urlAcompanhamento }}" class="btn-primary">Acompanhar Pedido</a>
    </div>

    <p style="margin-top: 32px; color: #64748b; font-size: 13px;">
        Qualquer dúvida, basta responder este e-mail para falar diretamente com o nosso atendimento.
    </p>
@endsection
