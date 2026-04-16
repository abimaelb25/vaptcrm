{{--
    Autoria: Abimael Borges
    Site: https://abimaelborges.adv.br
    Modificado em: 2026-04-13T04:32:16-03:00
    Módulo: PDV — Impressão de Ordem de Serviço
--}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OS #{{ $pedido->numero ?? 'N/A' }} — {{ $config['nome_fantasia'] ?? 'Gráfica' }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand: #FF7A00;
            --dark:  #1E293B;
            --line:  #e2e8f0;
            --muted: #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Outfit', 'Helvetica Neue', Arial, sans-serif;
            font-size: 12.5px; color: #1e293b;
            background: #f1f5f9;
            padding: 1.5rem;
        }

        /* ── Barra de ação (não imprime) ── */
        .action-bar {
            display: flex; align-items: center; gap: .75rem;
            background: #1e293b; color: #fff;
            border-radius: .875rem; padding: .875rem 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 24px rgba(0,0,0,.18);
        }
        .action-bar-title { flex: 1; font-weight: 700; font-size: .85rem; }
        .action-bar-title small { display: block; font-size: .7rem; opacity: .55; font-weight: 400; margin-top: .1rem; }
        .btn-print {
            display: flex; align-items: center; gap: .4rem;
            background: var(--brand); color: #fff;
            border: none; border-radius: .625rem; padding: .625rem 1.125rem;
            font-family: 'Outfit', sans-serif; font-size: .82rem; font-weight: 700;
            cursor: pointer; transition: all .2s;
        }
        .btn-print:hover { filter: brightness(1.1); box-shadow: 0 4px 12px rgba(255,122,0,.35); }
        .btn-back {
            display: flex; align-items: center; gap: .35rem;
            background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
            color: rgba(255,255,255,.75); border-radius: .625rem; padding: .5rem .875rem;
            font-family: 'Outfit', sans-serif; font-size: .75rem; font-weight: 600;
            cursor: pointer; transition: all .2s; text-decoration: none;
        }
        .btn-back:hover { background: rgba(255,255,255,.18); color: #fff; }

        /* ── Vias ── */
        .os-vias { display: flex; flex-direction: column; gap: 0; }
        .via-separator { border: none; border-top: 2px dashed #cbd5e1; margin: 1.25rem 0; }

        /* ── Folha OS ── */
        .os-sheet {
            background: #fff;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,.07);
            position: relative;
            page-break-after: avoid;
        }

        /* Faixa colorida topo */
        .os-topbar {
            background: var(--dark);
            padding: .875rem 1.25rem;
            display: flex; align-items: center; justify-content: space-between;
        }
        .os-empresa { color: #fff; font-weight: 800; font-size: 1rem; letter-spacing: -.01em; }
        .os-via-tag {
            font-size: .65rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
            border: 1px solid rgba(255,255,255,.3); color: rgba(255,255,255,.7);
            padding: .2rem .6rem; border-radius: .375rem;
        }

        /* Linha laranja decorativa */
        .os-topbar-accent { height: 3px; background: linear-gradient(90deg, var(--brand) 0%, transparent 100%); }

        /* Bloco número OS */
        .os-number-block {
            background: var(--brand); color: #fff;
            padding: .625rem 1.25rem;
            display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
        }
        .os-number-label { font-size: .65rem; font-weight: 700; letter-spacing: .1em; opacity: .75; text-transform: uppercase; }
        .os-number-val { font-size: 1.1rem; font-weight: 800; font-family: 'Outfit', sans-serif; }
        .os-track-label { font-size: .65rem; opacity: .75; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; }
        .os-track-val { font-size: .85rem; font-weight: 800; letter-spacing: .04em; }
        .os-date { margin-left: auto; font-size: .72rem; opacity: .8; text-align: right; }

        /* Corpo OS */
        .os-body { padding: 1rem 1.25rem; }

        /* Seção */
        .os-section { margin-bottom: 1rem; padding-bottom: .875rem; border-bottom: 1px solid var(--line); }
        .os-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .os-section-title {
            font-size: .6rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase;
            color: var(--muted); margin-bottom: .625rem;
            display: flex; align-items: center; gap: .375rem;
        }
        .os-section-title::after { content: ''; flex: 1; height: 1px; background: var(--line); }

        /* Grid dados cliente */
        .os-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .375rem .75rem; }
        .os-info-item-label { font-size: .65rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; }
        .os-info-item-val { font-size: .82rem; font-weight: 700; color: var(--dark); }

        /* Tabela itens */
        .os-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
        .os-table thead tr { background: #f8fafc; }
        .os-table th {
            text-align: left; padding: .4rem .5rem;
            font-size: .6rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--muted);
            border-bottom: 2px solid var(--line);
        }
        .os-table th:not(:first-child) { text-align: right; }
        .os-table td { padding: .45rem .5rem; border-bottom: 1px solid #f8fafc; vertical-align: top; }
        .os-table td:not(:first-child) { text-align: right; }
        .os-table tr:last-child td { border-bottom: none; }
        .os-table tbody tr:hover { background: #f8fafc; }
        .os-item-nome { font-weight: 700; color: var(--dark); }
        .os-item-obs { font-size: .7rem; color: var(--muted); margin-top: .15rem; font-style: italic; }

        /* Totais */
        .os-totals { display: grid; grid-template-columns: 1fr auto; gap: .25rem .875rem; margin-top: .625rem; }
        .os-total-label { font-size: .72rem; color: var(--muted); font-weight: 600; text-align: right; }
        .os-total-val   { font-size: .72rem; font-weight: 700; color: var(--dark); text-align: right; }
        .os-total-grand { font-size: 1rem; font-weight: 800; color: var(--dark); text-align: right; font-family: 'Outfit', sans-serif; }
        .os-total-grand-label { font-size: .72rem; font-weight: 800; color: var(--dark); text-align: right; text-transform: uppercase; letter-spacing: .06em; }

        /* Pagamento + assinatura */
        .os-bottom-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: .875rem; }
        .os-pay-method {
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: .625rem;
            padding: .625rem .875rem;
        }
        .os-pay-method-label { font-size: .6rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #166534; margin-bottom: .25rem; }
        .os-pay-method-val { font-size: .875rem; font-weight: 800; color: #15803d; }
        .os-sign-box {
            border: 1px solid var(--line); border-radius: .625rem;
            padding: .625rem .875rem; min-height: 70px; position: relative;
        }
        .os-sign-label { font-size: .6rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
        .os-sign-line {
            position: absolute; bottom: .875rem; left: .875rem; right: .875rem;
            border-top: 1px solid #e2e8f0;
        }

        /* Rodapé */
        .os-footer {
            background: #f8fafc; border-top: 1px solid var(--line);
            padding: .625rem 1.25rem; text-align: center;
            font-size: .65rem; color: var(--muted); line-height: 1.5;
        }
        .os-footer strong { color: var(--dark); }

        /* ═══════════════════════════════════
           IMPRESSÃO
        ═══════════════════════════════════ */
        @media print {
            body { background: #fff; padding: 0; font-size: 11px; }
            .action-bar { display: none !important; }
            .os-sheet { box-shadow: none; border-radius: 0; }
            .os-topbar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .os-number-block { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .os-pay-method { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .via-separator { margin: .75rem 0; }
            @page { margin: 1cm; size: A4; }
        }
    </style>
</head>
<body>

    {{-- Barra de ação (não imprime) --}}
    <div class="action-bar no-print-hidden">
        <div class="action-bar-title">
            Ordem de Serviço #{{ $pedido->numero ?? 'N/A' }}
            <small>{{ $config['nome_fantasia'] ?? 'Gráfica' }} · Emitida em {{ now()->format('d/m/Y \à\s H:i') }}</small>
        </div>
        <a href="javascript:history.back()" class="btn-back">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>
        <button class="btn-print" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimir
        </button>
    </div>

    <div class="os-vias">
        @foreach(['VIA DA PRODUÇÃO', 'VIA DO CLIENTE'] as $idx => $via)

            @if($idx > 0)
                <hr class="via-separator">
            @endif

            <div class="os-sheet">

                {{-- Topbar escuro --}}
                <div class="os-topbar">
                    <div class="os-empresa">{{ $config['nome_fantasia'] ?? 'Gráfica' }}</div>
                    <div class="os-via-tag">{{ $via }}</div>
                </div>
                <div class="os-topbar-accent"></div>

                {{-- Faixa laranja com nº OS --}}
                <div class="os-number-block">
                    <div>
                        <div class="os-number-label">Ordem de Serviço</div>
                        <div class="os-number-val">#{{ $pedido->numero ?? 'N/A' }}</div>
                    </div>
                    <div style="width:1px;height:32px;background:rgba(255,255,255,.25);"></div>
                    <div>
                        <div class="os-track-label">Código de Acompanhamento</div>
                        <div class="os-track-val">{{ $pedido->numero_acompanhamento ?? '—' }}</div>
                    </div>
                    <div class="os-date">
                        <div style="font-size:.65rem;opacity:.7;font-weight:600;">Emissão</div>
                        <div style="font-weight:700;">{{ $pedido->created_at->format('d/m/Y') }}</div>
                        <div style="font-size:.7rem;opacity:.8;">{{ $pedido->created_at->format('H:i') }}</div>
                    </div>
                </div>

                <div class="os-body">

                    {{-- CLIENTE --}}
                    <div class="os-section">
                        <div class="os-section-title">Dados do Cliente</div>
                        <div class="os-info-grid">
                            <div>
                                <div class="os-info-item-label">Nome</div>
                                <div class="os-info-item-val">{{ $pedido->cliente->nome }}</div>
                            </div>
                            <div>
                                <div class="os-info-item-label">WhatsApp</div>
                                <div class="os-info-item-val">{{ $pedido->cliente->whatsapp ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="os-info-item-label">Atendente</div>
                                <div class="os-info-item-val">{{ $pedido->atendente->nome ?? 'Sistema' }}</div>
                            </div>
                            <div>
                                <div class="os-info-item-label">Prazo de Entrega</div>
                                <div class="os-info-item-val">
                                    {{ $pedido->prazo_entrega ? \Carbon\Carbon::parse($pedido->prazo_entrega)->format('d/m/Y') : '—' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ITENS --}}
                    <div class="os-section">
                        <div class="os-section-title">Itens do Pedido</div>
                        <table class="os-table">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Qtd</th>
                                    <th>Unit.</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedido->itens as $item)
                                <tr>
                                    <td>
                                        <div class="os-item-nome">{{ $item->descricao_item }}</div>
                                        @if($item->produto && $item->produto->prazo_estimado)
                                            <div class="os-item-obs">Prazo estimado: {{ $item->produto->prazo_estimado }} dias úteis</div>
                                        @endif
                                    </td>
                                    <td>{{ $item->quantidade }}</td>
                                    <td>R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                    <td><strong>R$ {{ number_format($item->valor_total, 2, ',', '.') }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Totais --}}
                        <div class="os-totals">
                            @if($pedido->desconto > 0)
                                <span class="os-total-label">Subtotal</span>
                                <span class="os-total-val">R$ {{ number_format($pedido->subtotal, 2, ',', '.') }}</span>
                                <span class="os-total-label">Desconto</span>
                                <span class="os-total-val" style="color:#dc2626;">- R$ {{ number_format($pedido->desconto, 2, ',', '.') }}</span>
                            @endif
                            @if(isset($pedido->acrescimo) && $pedido->acrescimo > 0)
                                <span class="os-total-label">Acrésc./Frete</span>
                                <span class="os-total-val">+ R$ {{ number_format($pedido->acrescimo, 2, ',', '.') }}</span>
                            @endif
                            <span class="os-total-grand-label" style="margin-top:.375rem;">Total</span>
                            <span class="os-total-grand" style="margin-top:.375rem;">R$ {{ number_format($pedido->total, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- PAGAMENTO + ASSINATURA --}}
                    <div class="os-bottom-row">
                        <div class="os-pay-method">
                            <div class="os-pay-method-label">Forma de Pagamento</div>
                            <div class="os-pay-method-val">{{ $pedido->pagamentos->pluck('metodo')->join(', ') }}</div>
                            @if($pedido->troco > 0)
                                <div style="font-size:.72rem;color:#15803d;margin-top:.25rem;">Troco: R$ {{ number_format($pedido->troco, 2, ',', '.') }}</div>
                            @endif
                        </div>
                        <div class="os-sign-box">
                            <div class="os-sign-label">Assinatura / Conferência</div>
                            <div class="os-sign-line"></div>
                        </div>
                    </div>

                    {{-- OBSERVAÇÕES --}}
                    @if($pedido->observacoes_cliente)
                        <div class="os-section" style="margin-top:.875rem;">
                            <div class="os-section-title">Observações</div>
                            <p style="font-size:.8rem;line-height:1.55;color:#475569;">{{ $pedido->observacoes_cliente }}</p>
                        </div>
                    @endif

                </div>

                {{-- Rodapé --}}
                <div class="os-footer">
                    Acompanhe seu pedido em: <strong>{{ config('app.url') }}/acompanhar-pedido</strong>
                    · {{ $config['whatsapp'] ?? '' }}
                    · Obrigado pela preferência!
                </div>

            </div>
        @endforeach
    </div>

    <script>
        // Impressão automática ao carregar (opcional — descomente se desejar)
        // window.addEventListener('load', () => setTimeout(() => window.print(), 800));
    </script>
</body>
</html>
