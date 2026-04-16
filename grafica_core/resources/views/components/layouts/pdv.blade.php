{{--
    Autoria: Abimael Borges
    Site: https://abimaelborges.adv.br
    Modificado em: 2026-04-13T14:45:00-03:00
--}}
@php
    $cs = $configSite ?? [];
    $corPrimaria   = $cs['aparencia_cor_primaria'] ?? '#FF7A00';
    $corSecundaria = $cs['aparencia_cor_secundaria'] ?? '#1E293B';
    $nomeEmpresa   = $cs['empresa_nome'] ?? ($cs['loja_nome'] ?? 'Gráfica');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'PDV - ' . $nomeEmpresa }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(!empty($cs['aparencia_favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $cs['aparencia_favicon']) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-primary:    {{ $corPrimaria }};
            --brand-secondary:  {{ $corSecundaria }};
            --color-brand-primary:   {{ $corPrimaria }};
            --color-brand-secondary: {{ $corSecundaria }};

            /* PDV tokens */
            --pos-bg:           #f1f5f9;
            --pos-card:         #ffffff;
            --pos-border:       #e2e8f0;
            --pos-text:         #1e293b;
            --pos-text-muted:   #64748b;
            --pos-accent:       #3b82f6;
            --pos-success:      #10b981;
            --pos-danger:       #ef4444;
            --pos-warning:      #f59e0b;

            /* Header */
            --header-h:         64px;

            /* Scrollbar */
            --scroll-w:         5px;
            --scroll-thumb:     #cbd5e1;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--pos-bg);
            color: var(--pos-text);
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            overflow: hidden;
            height: 100dvh;
        }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: var(--scroll-w); height: var(--scroll-w); }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--scroll-thumb); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── HEADER ── */
        .pdv-header {
            height: var(--header-h);
            background: var(--brand-secondary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: relative;
            z-index: 200;
            box-shadow: 0 4px 24px -4px rgba(0,0,0,0.35);
        }

        .pdv-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--brand-primary), transparent);
            opacity: .6;
        }

        .pdv-logo img { height: 34px; }

        .pdv-header-divider {
            width: 1px; height: 32px;
            background: rgba(255,255,255,.15);
        }

        .pdv-header-title {
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: rgba(255,255,255,.9);
        }

        /* Badge status header */
        .pdv-status-badge {
            display: flex; align-items: center; gap: .375rem;
            background: rgba(16,185,129,.18);
            border: 1px solid rgba(16,185,129,.35);
            color: #6ee7b7;
            font-size: .7rem; font-weight: 700; letter-spacing: .06em;
            padding: .25rem .625rem;
            border-radius: 9999px;
        }
        .pdv-status-badge::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 6px #10b981;
            animation: pulse-dot 2s ease-in-out infinite;
        }
        @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* Relógio */
        .pdv-clock {
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem; font-weight: 700;
            letter-spacing: .04em;
            color: #fff;
        }
        .pdv-clock-date { font-size: .65rem; font-weight: 500; color: rgba(255,255,255,.5); letter-spacing: .06em; }

        /* Operador */
        .pdv-operator { text-align: right; }
        .pdv-operator-label { font-size: .625rem; color: rgba(255,255,255,.45); font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
        .pdv-operator-name { font-size: .875rem; font-weight: 700; color: #fff; }

        /* Botões header */
        .pdv-header-btn {
            display: flex; align-items: center; justify-content: center;
            width: 38px; height: 38px;
            border-radius: .625rem;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            color: rgba(255,255,255,.8);
            transition: all .2s;
            cursor: pointer;
        }
        .pdv-header-btn:hover { background: rgba(255,255,255,.18); color: #fff; }
        .pdv-header-btn.accent {
            background: var(--brand-primary);
            border-color: var(--brand-primary);
            color: white;
            font-size: .8rem; font-weight: 700;
            padding: 0 1rem; width: auto; gap: .4rem;
            letter-spacing: .03em;
        }
        .pdv-header-btn.accent:hover { filter: brightness(1.1); box-shadow: 0 4px 16px rgba(255,122,0,.35); }

        /* ── CONTENT AREA ── */
        .pdv-content {
            height: calc(100dvh - var(--header-h));
            padding: 1rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
    </style>
</head>
<body class="antialiased">

    <header class="pdv-header">
        {{-- Lado Esquerdo: Logo + Título --}}
        <div style="display:flex;align-items:center;gap:1rem;">
            <div class="pdv-logo">
                @if(!empty($cs['aparencia_logo']))
                    <img src="{{ asset('storage/' . $cs['aparencia_logo']) }}" alt="{{ $nomeEmpresa }}">
                @else
                    <img src="{{ asset('img/logo_horizontal.png') }}" alt="{{ $nomeEmpresa }}" class="brightness-0 invert">
                @endif
            </div>
            <div class="pdv-header-divider"></div>
            <div>
                <div class="pdv-header-title">PDV / Frente de Balcão</div>
                <div class="pdv-status-badge" style="margin-top:.25rem;">EM OPERAÇÃO</div>
            </div>
        </div>

        {{-- Centro: Relógio --}}
        <div style="text-align:center;position:absolute;left:50%;transform:translateX(-50%);">
            <div class="pdv-clock" id="pdv-clock">--:--:--</div>
            <div class="pdv-clock-date" id="pdv-date">Carregando...</div>
        </div>

        {{-- Lado Direito: Operador + Ações --}}
        <div style="display:flex;align-items:center;gap:.875rem;">
            <div class="pdv-operator">
                <div class="pdv-operator-label">Atendente</div>
                <div class="pdv-operator-name">{{ auth()->user()->nome }}</div>
            </div>
            <div class="pdv-header-divider"></div>
            <button onclick="window.location.reload()" class="pdv-header-btn" title="Novo Atendimento">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
            <a href="{{ route('admin.dashboard') }}" onclick="event.preventDefault(); window.open(this.href, '_blank', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' + screen.width + ',height=' + screen.height)" class="pdv-header-btn accent">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Painel
            </a>
        </div>
    </header>

    <main class="pdv-content">
        {{ $slot }}
    </main>

    <script>
        // Relógio em tempo real
        (function() {
            const clock = document.getElementById('pdv-clock');
            const dateEl = document.getElementById('pdv-date');
            const dias = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
            const meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
            function tick() {
                const n = new Date();
                const h = String(n.getHours()).padStart(2,'0');
                const m = String(n.getMinutes()).padStart(2,'0');
                const s = String(n.getSeconds()).padStart(2,'0');
                clock.textContent = `${h}:${m}:${s}`;
                dateEl.textContent = `${dias[n.getDay()]}, ${n.getDate()} ${meses[n.getMonth()]} ${n.getFullYear()}`;
            }
            tick(); setInterval(tick, 1000);
        })();

        /**
         * Abre uma URL em uma janela limpa, sem barras de navegador (Modo PDV/Kiosk)
         */
        function openCleanWindow(url, name = '_blank') {
            const w = screen.width;
            const h = screen.height;
            const features = `toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,top=0,left=0,width=${w},height=${h}`;
            return window.open(url, name, features);
        }
    </script>

    @stack('scripts')
</body>
</html>
