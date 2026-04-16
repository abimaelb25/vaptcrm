{{--
    Autoria: Abimael Borges
    Site: https://abimaelborges.adv.br
    Modificado em: 2026-04-14T02:26:00-03:00
    Módulo: PDV v2.1 — Frente de Balcão
--}}
<x-layouts.pdv>
    <x-slot name="titulo">Frente de Balcão — PDV</x-slot>

    <style>
        /* ═══════════════════════════════════════════
           DESIGN SYSTEM — PDV v2.1
           Abimael Borges | 2026-04-13
        ═══════════════════════════════════════════ */

        /* ── Layout 3 colunas ── */
        .pdv-grid {
            display: grid;
            grid-template-columns: 280px 1fr 380px;
            gap: .875rem;
            height: 100%;
            overflow: hidden;
        }

        /* ── Card genérico ── */
        .pcard {
            background: #fff;
            border: 1px solid var(--pos-border);
            border-radius: 1rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
            transition: box-shadow .2s;
        }

        /* ── COLUNA ESQUERDA ── */
        .col-left { display: flex; flex-direction: column; gap: .875rem; overflow: hidden; }

        /* Card cabeçalho seção */
        .section-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: .875rem 1rem .625rem;
            border-bottom: 1px solid var(--pos-border);
        }
        .section-label {
            font-size: .625rem; font-weight: 800; letter-spacing: .12em;
            text-transform: uppercase; color: var(--pos-text-muted);
        }
        .btn-link {
            font-size: .72rem; font-weight: 700; color: var(--pos-accent);
            border: none; background: none; cursor: pointer; padding: 0;
            transition: color .2s;
        }
        .btn-link:hover { color: #1d4ed8; }

        /* Busca genérica */
        .search-wrap { position: relative; padding: .75rem 1rem; }
        .search-wrap svg.icon-search {
            position: absolute; left: 1.75rem; top: 50%; transform: translateY(-50%);
            color: #94a3b8; pointer-events: none;
        }
        .search-input {
            width: 100%; padding: .5rem .75rem .5rem 2.25rem;
            border: 1.5px solid var(--pos-border);
            border-radius: .625rem; font-size: .82rem; font-family: 'Outfit', sans-serif;
            background: #f8fafc; color: var(--pos-text); outline: none;
            transition: border-color .2s, background .2s;
        }
        .search-input:focus { border-color: var(--pos-accent); background: #fff; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }

        /* Dropdown resultados */
        .dropdown-results {
            position: absolute; left: 1rem; right: 1rem; top: calc(100% - .25rem);
            background: #fff; border: 1px solid var(--pos-border); border-radius: .75rem;
            box-shadow: 0 20px 40px -8px rgba(0,0,0,.18); z-index: 500;
            max-height: 240px; overflow-y: auto; display: none;
        }
        .dropdown-results.open { display: block; }
        .dd-item {
            padding: .625rem 1rem; cursor: pointer;
            border-bottom: 1px solid #f1f5f9; transition: background .15s;
        }
        .dd-item:hover { background: #f8fafc; }
        .dd-item:last-child { border-bottom: none; }
        .dd-name { font-size: .82rem; font-weight: 700; color: var(--pos-text); }
        .dd-info { font-size: .68rem; color: var(--pos-text-muted); margin-top: .1rem; }

        /* Cliente selecionado */
        .client-selected {
            margin: 0 .75rem .75rem;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: .75rem; padding: .75rem;
            display: none;
        }
        .client-selected.active { display: block; }
        .client-selected-name { font-weight: 800; font-size: .875rem; color: #1e40af; }
        .client-selected-info { font-size: .7rem; color: #3b82f6; margin-top: .15rem; font-weight: 500; }
        .client-remove-btn {
            background: none; border: none; cursor: pointer; color: #93c5fd;
            transition: color .2s; padding: 0;
        }
        .client-remove-btn:hover { color: #ef4444; }

        /* Atalhos rápidos */
        .quick-access { padding: .75rem; flex: 1; display: flex; flex-direction: column; gap: .5rem; overflow-y: auto; }
        .quick-btn {
            display: flex; align-items: center; gap: .625rem;
            padding: .625rem .875rem; border-radius: .625rem;
            border: 1.5px solid var(--pos-border); background: #f8fafc;
            font-size: .78rem; font-weight: 700; color: var(--pos-text-muted);
            cursor: pointer; transition: all .2s; text-align: left; width: 100%;
        }
        .quick-btn:hover { border-color: var(--brand-primary); color: var(--brand-primary); background: #fff7ed; }
        .quick-btn-icon {
            width: 28px; height: 28px; border-radius: .5rem; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: .9rem;
        }
        .quick-btn .kbd {
            margin-left: auto; font-size: .6rem; font-weight: 800; letter-spacing: .05em;
            color: #94a3b8; background: #e2e8f0; padding: 1px 5px; border-radius: 4px;
        }

        /* Rodapé sidebar */
        .sidebar-footer {
            padding: .75rem 1rem; border-top: 1px solid var(--pos-border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .shortcut-tag {
            font-size: .65rem; font-weight: 700; letter-spacing: .06em;
            color: var(--pos-text-muted); background: #f1f5f9;
            border: 1px solid var(--pos-border); padding: 2px 7px; border-radius: 5px;
        }

        /* ── COLUNA CENTRAL ── */
        .col-center { display: flex; flex-direction: column; overflow: hidden; }

        /* Pills categoria */
        .category-bar {
            display: flex; gap: .375rem; padding: .75rem 1rem;
            overflow-x: auto; border-bottom: 1px solid var(--pos-border);
            background: #fff; border-radius: 1rem 1rem 0 0; flex-shrink: 0;
            -ms-overflow-style: none; scrollbar-width: none;
        }
        .category-bar::-webkit-scrollbar { display: none; }
        .cpill {
            display: flex; align-items: center; gap: .375rem;
            padding: .4rem .875rem; border-radius: 9999px;
            border: 1.5px solid var(--pos-border); background: #f8fafc;
            font-size: .75rem; font-weight: 700; color: var(--pos-text-muted);
            cursor: pointer; white-space: nowrap; flex-shrink: 0; transition: all .2s;
        }
        .cpill:hover:not(.active) { background: #fff; border-color: #cbd5e1; color: #334155; transform: translateY(-1px); }
        .cpill.active {
            background: var(--brand-primary); color: #fff; border-color: var(--brand-primary);
            box-shadow: 0 4px 12px color-mix(in srgb, var(--brand-primary) 35%, transparent);
            transform: translateY(-1px);
        }
        .cpill-count {
            font-size: .65rem; padding: 1px 5px; border-radius: 5px;
            background: rgba(0,0,0,.12); font-weight: 800;
        }
        .cpill.active .cpill-count { background: rgba(255,255,255,.25); }

        /* Busca produtos */
        .product-search-wrap {
            padding: .75rem 1rem .5rem; flex-shrink: 0; background: #fff;
            border: 1px solid var(--pos-border); border-top: none; border-bottom: none;
        }
        .product-search-wrap > div { position: relative; }
        .product-search-wrap svg { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
        .product-search-input {
            width: 100%; padding: .5rem .75rem .5rem 2.25rem;
            border: 1.5px solid var(--pos-border); border-radius: .625rem;
            font-size: .82rem; font-family: 'Outfit', sans-serif;
            background: #f8fafc; color: var(--pos-text); outline: none;
            transition: border-color .2s, background .2s;
        }
        .product-search-input:focus { border-color: var(--brand-primary); background: #fff; box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-primary) 12%, transparent); }

        /* Grid produtos */
        .product-grid-wrap {
            flex: 1; overflow-y: auto; padding: .875rem; background: #fff;
            border-radius: 0 0 1rem 1rem; border: 1px solid var(--pos-border); border-top: none;
        }
        .product-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(148px, 1fr)); gap: .75rem;
        }

        /* Card produto */
        .prd-card {
            background: #fff; border: 1.5px solid #f1f5f9; border-radius: .875rem;
            overflow: hidden; cursor: pointer; transition: all .22s cubic-bezier(.4,0,.2,1);
            position: relative; display: flex; flex-direction: column;
        }
        .prd-card:hover {
            border-color: var(--brand-primary); transform: translateY(-3px);
            box-shadow: 0 12px 24px -6px color-mix(in srgb, var(--brand-primary) 20%, transparent);
        }
        .prd-card:active { transform: translateY(-1px) scale(.98); }
        .prd-card-img-wrap { aspect-ratio: 1; overflow: hidden; background: #f8fafc; position: relative; }
        .prd-card-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
        .prd-card:hover .prd-card-img-wrap img { transform: scale(1.06); }
        .prd-card-add-overlay {
            position: absolute; inset: 0; background: rgba(0,0,0,.45);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity .2s;
        }
        .prd-card:hover .prd-card-add-overlay { opacity: 1; }
        .prd-card-add-icon {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--brand-primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,0,0,.3);
        }
        .prd-card-body { padding: .625rem .75rem .75rem; flex: 1; }
        .prd-card-name {
            font-size: .8rem; font-weight: 700; color: var(--pos-text);
            line-height: 1.25; height: 2em; overflow: hidden;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            margin-bottom: .375rem;
        }
        .prd-card-price {
            font-size: .95rem; font-weight: 800; color: var(--brand-primary);
            font-family: 'Syne', sans-serif;
        }
        /* Badge mais vendido */
        .top-badge {
            position: absolute; top: .4rem; left: .4rem;
            background: #f59e0b; color: #fff;
            font-size: .55rem; font-weight: 800; letter-spacing: .06em;
            padding: 2px 6px; border-radius: 5px;
            box-shadow: 0 2px 6px rgba(0,0,0,.2); z-index: 2;
        }

        /* Skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.4s ease-in-out infinite; border-radius: .5rem;
        }
        @keyframes skeleton-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
        .skeleton-card { border: 1.5px solid #f1f5f9; border-radius: .875rem; overflow: hidden; }
        .skeleton-img { aspect-ratio: 1; }
        .skeleton-body { padding: .625rem .75rem .75rem; }
        .skeleton-line { height: .75rem; border-radius: 5px; margin-bottom: .4rem; }

        /* Vazio */
        .empty-state {
            grid-column: 1/-1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 3rem 1rem; color: #cbd5e1; text-align: center;
        }
        .empty-state svg { margin-bottom: .75rem; opacity: .5; }
        .empty-state p { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }

        /* ── COLUNA DIREITA — CARRINHO ── */
        .col-right { display: flex; flex-direction: column; overflow: hidden; }
        .cart-shell { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }

        .cart-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: .875rem 1.125rem; border-bottom: 1px solid var(--pos-border);
            background: #fff; border-radius: 1rem 1rem 0 0; flex-shrink: 0;
        }
        .cart-head-title { display: flex; align-items: center; gap: .5rem; font-weight: 800; font-size: .95rem; color: var(--pos-text); }
        .cart-badge {
            font-size: .65rem; font-weight: 800; letter-spacing: .06em;
            background: var(--pos-border); color: var(--pos-text-muted);
            padding: .2rem .5rem; border-radius: .375rem;
        }
        .cart-badge.has-items { background: var(--brand-primary); color: #fff; }
        .cart-clear-btn {
            font-size: .72rem; font-weight: 700; color: #ef4444;
            background: none; border: none; cursor: pointer; padding: 0; display: none;
        }
        .cart-clear-btn.visible { display: block; }

        .cart-body { flex: 1; overflow-y: auto; min-height: 0; background: #fff; }

        /* Item carrinho */
        .cart-item {
            display: grid; grid-template-columns: 44px 1fr auto auto;
            gap: .625rem; align-items: center;
            padding: .625rem 1rem; border-bottom: 1px solid #f8fafc;
            animation: slideIn .15s ease-out;
        }
        @keyframes slideIn { from{opacity:0;transform:translateX(8px)} to{opacity:1;transform:none} }
        .cart-item-img {
            width: 44px; height: 44px; border-radius: .5rem;
            object-fit: cover; background: #f1f5f9; flex-shrink: 0;
        }
        .cart-item-name {
            font-size: .8rem; font-weight: 700; color: var(--pos-text);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            cursor: text; outline: none; border-radius: .25rem;
            padding: 1px 2px; transition: background .15s;
        }
        .cart-item-name:focus { background: #f0f9ff; box-shadow: 0 0 0 2px var(--pos-accent); }
        .cart-item-price-wrap { position: relative; }
        .cart-item-price-prefix { position: absolute; left: .4rem; top: 50%; transform: translateY(-50%); font-size: .6rem; font-weight: 700; color: #94a3b8; pointer-events: none; }
        .cart-item-price {
            width: 68px; padding: .3rem .4rem .3rem 1.3rem;
            border: 1.5px solid var(--pos-border); border-radius: .5rem;
            font-size: .8rem; font-weight: 800; color: var(--brand-primary);
            font-family: 'Syne', sans-serif; outline: none; background: #fff;
            transition: border-color .2s;
        }
        .cart-item-price:focus { border-color: var(--brand-primary); }

        /* Controles quantidade */
        .qty-ctrl { display: flex; align-items: center; background: #f1f5f9; border-radius: .5rem; overflow: hidden; }
        .qty-btn {
            width: 24px; height: 28px; display: flex; align-items: center; justify-content: center;
            background: none; border: none; cursor: pointer; font-size: .9rem; font-weight: 700;
            color: var(--pos-text-muted); transition: background .15s, color .15s;
        }
        .qty-btn:hover { background: var(--pos-border); color: var(--pos-text); }
        .qty-val {
            width: 28px; text-align: center; background: none; border: none;
            font-size: .82rem; font-weight: 800; color: var(--pos-text);
            outline: none; font-family: 'Outfit', sans-serif;
        }
        .item-del-btn {
            width: 26px; height: 26px; border-radius: .5rem; display: flex;
            align-items: center; justify-content: center;
            background: none; border: 1px solid transparent; cursor: pointer; color: #cbd5e1;
            transition: all .18s;
        }
        .item-del-btn:hover { background: #fee2e2; border-color: #fca5a5; color: #ef4444; }

        /* Footer carrinho */
        .cart-foot {
            padding: 1rem 1.125rem; background: #fafafa;
            border: 1px solid var(--pos-border); border-top: none;
            border-radius: 0 0 1rem 1rem; flex-shrink: 0;
        }
        .total-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: .375rem; }
        .total-row-label { font-size: .75rem; font-weight: 600; color: var(--pos-text-muted); }
        .total-row-val { font-size: .78rem; font-weight: 700; color: var(--pos-text); }
        .adj-wrap { position: relative; }
        .adj-prefix { position: absolute; left: .45rem; top: 50%; transform: translateY(-50%); font-size: .6rem; font-weight: 700; color: #94a3b8; pointer-events: none; }
        .adj-input {
            width: 75px; padding: .28rem .4rem .28rem 1.3rem;
            border: 1.5px solid var(--pos-border); border-radius: .5rem;
            font-size: .78rem; font-weight: 700; color: var(--pos-text); outline: none;
        }
        .adj-input:focus { border-color: var(--pos-accent); }
        .total-box {
            background: linear-gradient(135deg, var(--brand-secondary) 0%, color-mix(in srgb, var(--brand-secondary) 85%, #000) 100%);
            color: #fff; border-radius: .875rem; padding: .875rem 1.125rem;
            display: flex; justify-content: space-between; align-items: center; margin: .75rem 0;
            box-shadow: 0 4px 16px color-mix(in srgb, var(--brand-secondary) 30%, transparent);
        }
        .total-label { font-size: .68rem; font-weight: 700; letter-spacing: .1em; opacity: .7; text-transform: uppercase; }
        .total-value { font-size: 1.5rem; font-weight: 800; font-family: 'Syne', sans-serif; letter-spacing: -.01em; }

        /* Pagamento */
        .pay-section-label { font-size: .625rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--pos-text-muted); margin-bottom: .5rem; }
        .pay-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .4rem; margin-bottom: .625rem; }
        .pay-btn {
            display: flex; flex-direction: column; align-items: center; gap: .25rem;
            padding: .55rem .25rem; border-radius: .625rem;
            border: 1.5px solid var(--pos-border); background: #fff;
            font-size: .65rem; font-weight: 700; color: var(--pos-text-muted);
            cursor: pointer; transition: all .18s; outline: none;
        }
        .pay-btn:hover:not(.active) { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
        .pay-btn.active { background: #eff6ff; border-color: #60a5fa; color: #1d4ed8; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
        .pay-btn svg { width: 20px; height: 20px; }

        .cash-panel { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: .75rem; padding: .75rem; margin-bottom: .5rem; display: none; }
        .cash-panel.visible { display: block; animation: fadeIn .2s ease-out; }
        .cash-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: .375rem; }
        .cash-label { font-size: .7rem; font-weight: 700; color: #166534; }
        .cash-input { width: 90px; padding: .3rem .5rem; border: 1.5px solid #86efac; border-radius: .5rem; font-size: .82rem; font-weight: 800; color: #166534; text-align: right; outline: none; background: #fff; }
        .cash-input:focus { border-color: #22c55e; }
        .cash-change-row { display: flex; align-items: center; justify-content: space-between; }
        .cash-change-label { font-size: .72rem; font-weight: 700; color: #166534; }
        .cash-change-val { font-size: 1rem; font-weight: 800; font-family: 'Syne', sans-serif; color: #16a34a; }
        .cash-change-val.negative { color: #dc2626; }

        .pix-panel { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: .75rem; padding: .75rem; text-align: center; margin-bottom: .5rem; display: none; }
        .pix-panel.visible { display: block; animation: fadeIn .2s ease-out; }
        .pix-badge { display: inline-flex; align-items: center; gap: .35rem; background: #3b82f6; color: #fff; font-size: .65rem; font-weight: 800; letter-spacing: .08em; padding: .2rem .6rem; border-radius: 9999px; margin-bottom: .5rem; }
        .pix-key { font-size: .78rem; font-weight: 800; color: #1e40af; word-break: break-all; }

        /* Botão finalizar */
        .btn-finalizar {
            width: 100%; padding: .875rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff; font-family: 'Syne', sans-serif; font-size: .9rem;
            font-weight: 800; letter-spacing: .04em; border: none; border-radius: .875rem;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .5rem;
            transition: all .22s; box-shadow: 0 4px 16px rgba(16,185,129,.25);
        }
        .btn-finalizar:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(16,185,129,.35); filter: brightness(1.05); }
        .btn-finalizar:active:not(:disabled) { transform: scale(.98); }
        .btn-finalizar:disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; box-shadow: none; }

        @keyframes fadeIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }

        /* ══════════════════════════════════════
           POPUP OVERLAY (substitui modais Bootstrap)
        ══════════════════════════════════════ */
        .popup-overlay {
            position: fixed; inset: 0;
            background: rgba(15,23,42,.55);
            backdrop-filter: blur(6px);
            z-index: 9000;
            display: none; align-items: center; justify-content: center;
            animation: fadeIn .2s ease-out;
        }
        .popup-overlay.active { display: flex; }
        .popup-box {
            background: #fff; border-radius: 1.25rem;
            box-shadow: 0 25px 60px rgba(0,0,0,.3);
            width: 100%; max-width: 400px;
            overflow: hidden;
            animation: popIn .2s cubic-bezier(.4, 0, .2, 1);
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(.92) translateY(12px); }
            to   { opacity: 1; transform: none; }
        }
        .popup-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9;
        }
        .popup-title { font-weight: 800; font-size: 1rem; color: var(--pos-text); }
        .popup-shortcut { font-size: .6rem; font-weight: 700; color: #94a3b8; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; letter-spacing: .05em; }
        .popup-close {
            width: 30px; height: 30px; border-radius: .5rem;
            display: flex; align-items: center; justify-content: center;
            background: none; border: 1px solid #e2e8f0; color: #94a3b8;
            cursor: pointer; transition: all .15s;
        }
        .popup-close:hover { background: #fee2e2; border-color: #fca5a5; color: #ef4444; }
        .popup-body { padding: 1.25rem; }
        .popup-footer { padding: 1rem 1.25rem; background: #f8fafc; border-top: 1px solid #f1f5f9; }
        .popup-label { font-size: .65rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--pos-text-muted); display: block; margin-bottom: .3rem; }
        .popup-input {
            width: 100%; padding: .5rem .75rem; border: 1.5px solid var(--pos-border);
            border-radius: .625rem; font-size: .85rem; font-family: 'Outfit', sans-serif;
            color: var(--pos-text); outline: none; transition: border-color .2s;
        }
        .popup-input:focus { border-color: var(--pos-accent); box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
        .popup-btn {
            width: 100%; padding: .7rem; border: none; border-radius: .75rem;
            font-family: 'Outfit', sans-serif; font-size: .85rem; font-weight: 800;
            cursor: pointer; transition: all .2s; display: flex; align-items: center; justify-content: center; gap: .4rem;
        }
        .popup-btn-primary { background: var(--pos-accent); color: #fff; }
        .popup-btn-primary:hover { background: #2563eb; }
        .popup-btn-success { background: #10b981; color: #fff; }
        .popup-btn-success:hover { background: #059669; }

        /* Responsividade */
        @media (max-width: 1200px) {
            .pdv-grid { grid-template-columns: 260px 1fr 340px; }
        }
        @media (max-width: 900px) {
            .pdv-grid { grid-template-columns: 1fr; grid-template-rows: auto 1fr auto; }
            .col-left, .col-right { height: auto; max-height: 45vh; }
        }
    </style>

    <div class="pdv-grid" id="pdv-app">

        {{-- ═══════════════════════════════════
             COLUNA 1 — CLIENTE + NAVEGAÇÃO
        ═══════════════════════════════════ --}}
        <div class="col-left">

            {{-- Card Cliente --}}
            <div class="pcard" style="display:flex;flex-direction:column;overflow:hidden;">
                <div class="section-head">
                    <span class="section-label">Cliente</span>
                    <button class="btn-link" onclick="abrirPopup('cliente')" type="button">+ Novo (F2)</button>
                </div>

                <div class="search-wrap" id="client-search-wrap" style="padding-bottom:.5rem;">
                    <svg class="icon-search" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" class="search-input" id="search-cliente" placeholder="Nome, WhatsApp ou CPF..." autocomplete="off">
                    <div class="dropdown-results" id="results-cliente"></div>
                </div>

                <div class="client-selected" id="cliente-selecionado">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
                        <div>
                            <div class="client-selected-name" id="sel-nome">—</div>
                            <div class="client-selected-info" id="sel-info">—</div>
                        </div>
                        <button class="client-remove-btn" onclick="removerCliente()" title="Remover cliente" type="button">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Card Atalhos Rápidos --}}
            <div class="pcard" style="flex:1;display:flex;flex-direction:column;overflow:hidden;">
                <div class="section-head">
                    <span class="section-label">Acesso Rápido</span>
                </div>
                <div class="quick-access">

                    <button class="quick-btn" onclick="abrirPopup('cliente')" type="button">
                        <span class="quick-btn-icon" style="background:#eff6ff;color:#3b82f6;">👤</span>
                        Novo Cliente
                        <span class="kbd">F2</span>
                    </button>
                    <button class="quick-btn" onclick="abrirPopup('produto')" type="button">
                        <span class="quick-btn-icon" style="background:#f0fdf4;color:#10b981;">✏️</span>
                        Novo Produto Rápido
                        <span class="kbd">F4</span>
                    </button>
                </div>
                <div class="sidebar-footer">
                    <div style="display:flex;flex-direction:column;gap:.15rem;">
                         <span style="font-size:.65rem;color:var(--pos-text-muted);font-weight:700;display:flex;align-items:center;gap:.3rem;">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $caixaAberto ? '#10b981' : '#ef4444' }};"></span>
                            {{ $caixaAberto ? 'CAIXA ABERTO' : 'CAIXA FECHADO' }}
                        </span>
                        <span style="font-size:.7rem;color:var(--pos-text);font-weight:800;text-transform:uppercase;">
                            {{ auth()->user()->nome }}
                        </span>
                    </div>
                    <div>
                        @if($caixaAberto)
                            <button class="btn-link" onclick="prepararFechamento()" style="color:#ef4444;" type="button">Encerrar (F7)</button>
                        @else
                            <button class="btn-link" onclick="abrirPopup('caixa-abertura')" type="button">Abrir Caixa</button>
                        @endif
                    </div>
                </div>
                <div class="sidebar-footer" style="padding-top:.5rem;border-top:none;">
                    <span class="shortcut-tag">F8 — Finalizar</span>
                    <span style="font-size:.65rem;color:var(--pos-text-muted);font-weight:600;">v{{ config('app.version', '2.1.0') }}</span>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════
             COLUNA 2 — CATÁLOGO DE PRODUTOS
        ═══════════════════════════════════ --}}
        <div class="col-center">
            <div class="category-bar" id="category-bar">
                <button class="cpill active" data-id="all" onclick="filterCat('all')" type="button">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    Todos
                    <span class="cpill-count" id="cnt-all">0</span>
                </button>
                @foreach($categorias as $cat)
                    <button class="cpill" data-id="{{ $cat->id }}" onclick="filterCat({{ $cat->id }})" type="button">
                        {{ $cat->nome }}
                        <span class="cpill-count" id="cnt-{{ $cat->id }}">0</span>
                    </button>
                @endforeach
            </div>

            <div class="product-search-wrap">
                <div>
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" class="product-search-input" id="search-produto" placeholder="Buscar produto (3 letras para sugestões)..." autocomplete="off">
                </div>
            </div>

            <div class="product-grid-wrap">
                <div class="product-grid" id="product-grid">
                    @for($i = 0; $i < 12; $i++)
                        <div class="skeleton-card pcard">
                            <div class="skeleton skeleton-img"></div>
                            <div class="skeleton-body">
                                <div class="skeleton skeleton-line" style="width:80%;"></div>
                                <div class="skeleton skeleton-line" style="width:50%;"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════
             COLUNA 3 — CARRINHO + PAGAMENTO
        ═══════════════════════════════════ --}}
        <div class="col-right">
            <div class="cart-shell">
                <div class="pcard cart-head">
                    <div class="cart-head-title">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Carrinho
                        <span class="cart-badge" id="cart-badge">0 ITENS</span>
                    </div>
                    @if(in_array(auth()->user()->perfil, ['administrador', 'gerente']))
                    <button class="cart-clear-btn" id="cart-clear-btn" onclick="limparCarrinho()" type="button">Limpar tudo</button>
                    @endif
                </div>

                <div class="cart-body pcard" style="border-top:none;border-bottom:none;border-radius:0;" id="cart-body">
                    <div class="empty-state" id="cart-empty">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p>Carrinho vazio</p>
                        <span style="font-size:.72rem;color:#cbd5e1;margin-top:.25rem;">Clique em um produto para adicionar</span>
                    </div>
                </div>

                <div class="cart-foot">
                    <div class="total-row">
                        <span class="total-row-label">Subtotal</span>
                        <span class="total-row-val" id="label-subtotal">R$ 0,00</span>
                    </div>
                    <div class="total-row">
                        <span class="total-row-label">Desconto (R$)</span>
                        <div class="adj-wrap">
                            <span class="adj-prefix">R$</span>
                            <input type="number" class="adj-input" id="input-desconto" value="0" min="0" step="0.01" onchange="calcularTotais()">
                        </div>
                    </div>
                    <div class="total-row">
                        <span class="total-row-label">Acrésc./Frete</span>
                        <div class="adj-wrap">
                            <span class="adj-prefix">R$</span>
                            <input type="number" class="adj-input" id="input-acrescimo" value="0" min="0" step="0.01" onchange="calcularTotais()">
                        </div>
                    </div>

                    <div class="total-box">
                        <div>
                            <div class="total-label">Total a Pagar</div>
                            <div class="total-value" id="label-total">R$ 0,00</div>
                        </div>
                        <svg width="28" height="28" fill="none" stroke="rgba(255,255,255,.35)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <div class="pay-section-label">Forma de Pagamento</div>
                    <div class="pay-grid">
                        <button class="pay-btn" id="pay-pix" onclick="setMetodo('Pix')" type="button">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 8h12m-12 4h12m-12 4h12M4 20h4"/></svg>
                            Pix
                        </button>
                        <button class="pay-btn" id="pay-dinheiro" onclick="setMetodo('Dinheiro')" type="button">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Dinheiro
                        </button>
                        <button class="pay-btn" id="pay-cartao-de-credito" onclick="setMetodo('Cartão de Crédito')" type="button">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Crédito
                        </button>
                        <button class="pay-btn" id="pay-cartao-de-debito" onclick="setMetodo('Cartão de Débito')" type="button">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Débito
                        </button>
                    </div>

                    <div class="cash-panel" id="cash-panel">
                        <div class="cash-row">
                            <span class="cash-label">Valor Recebido</span>
                            <input type="number" class="cash-input" id="cash-received" placeholder="0,00" step="0.01" oninput="calcularTroco()">
                        </div>
                        <div class="cash-change-row">
                            <span class="cash-change-label">Troco</span>
                            <span class="cash-change-val" id="cash-change">R$ 0,00</span>
                        </div>
                    </div>

                    <div class="pix-panel" id="pix-panel">
                        <div class="pix-badge">PIX DINÂMICO</div>
                        
                        <div id="pix-qr-container" style="background:#fff;padding:.75rem;border-radius:.75rem;margin:0 auto .75rem;width:fit-content;box-shadow:0 4px 12px rgba(0,0,0,.05);">
                            <div id="pix-qrcode"></div>
                        </div>

                        <div style="font-size:.65rem;color:#3b82f6;margin-bottom:.2rem;font-weight:600;">Pix Copia e Cola</div>
                        <div class="pix-key-wrap" style="position:relative;margin-bottom:.5rem;">
                            <input type="text" id="pix-copy-paste" readonly class="pix-key" style="width:100%;background:#fff;border:1px solid #bfdbfe;padding:.4rem .5rem;border-radius:.5rem;font-size:.65rem;text-align:center;cursor:pointer;" title="Clique para copiar">
                        </div>
                        
                        <div style="font-size:.6rem;color:#64748b;">Escaneie o código ou copie o texto acima para pagar R$ <span id="pix-display-total">0,00</span></div>
                    </div>

                    <button class="btn-finalizar" id="btn-finalizar" onclick="finalizarVenda()" disabled type="button">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        FINALIZAR E IMPRIMIR
                        <span style="font-size:.65rem;opacity:.7;font-family:'Outfit',sans-serif;font-weight:600;">(F8)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         POPUP — NOVO CLIENTE (F2)
    ═══════════════════════════════════════════ --}}
    <div class="popup-overlay" id="popup-cliente" role="dialog" aria-labelledby="popup-cliente-title" aria-modal="true">
        <div class="popup-box">
            <div class="popup-head">
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <span class="popup-title" id="popup-cliente-title">Novo Cliente</span>
                    <span class="popup-shortcut">F2</span>
                </div>
                <button class="popup-close" onclick="fecharPopup('cliente')" type="button" aria-label="Fechar">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="popup-body">
                <form id="form-cliente-rapido" novalidate>
                    <div style="margin-bottom:.875rem;">
                        <label class="popup-label" for="fc-nome">Nome Completo *</label>
                        <input type="text" class="popup-input" id="fc-nome" name="nome" required placeholder="João Silva" autocomplete="off">
                    </div>
                    <div style="margin-bottom:.875rem;">
                        <label class="popup-label" for="fc-wp">WhatsApp *</label>
                        <input type="text" class="popup-input" id="fc-wp" name="whatsapp" required placeholder="11999998888" autocomplete="off">
                    </div>
                    <div>
                        <label class="popup-label" for="fc-email">E-mail (Opcional)</label>
                        <input type="email" class="popup-input" id="fc-email" name="email" placeholder="cliente@email.com" autocomplete="off">
                    </div>
                </form>
            </div>
            <div class="popup-footer">
                <button type="button" class="popup-btn popup-btn-primary" onclick="salvarClienteRapido()">
                    Cadastrar e Selecionar
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         POPUP — ABERTURA DE CAIXA
    ═══════════════════════════════════════════ --}}
    <div class="popup-overlay {{ !$caixaAberto ? 'active' : '' }}" id="popup-caixa-abertura" data-force="{{ auth()->user()->perfil === 'administrador' ? 'false' : 'true' }}" role="dialog" aria-modal="true">
        <div class="popup-box">
            <div class="popup-head">
                <span class="popup-title">Abertura de Caixa</span>
                @if(auth()->user()->perfil === 'administrador')
                    <button class="popup-close" onclick="fecharPopup('caixa-abertura')" type="button" aria-label="Fechar">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>
            <div class="popup-body">
                <p style="font-size:.82rem;color:var(--pos-text-muted);margin-bottom:1rem;">
                    Inicie seu turno informando o valor inicial disponível no caixa (fundo de reserva).
                </p>
                <div style="margin-bottom:.875rem;">
                    <label class="popup-label">Funcionário / Atendente *</label>
                    <select class="popup-input" id="caixa-usuario-id" style="font-weight:700;">
                        @foreach($funcionarios as $func)
                            <option value="{{ $func->id }}" @selected($func->id == auth()->id())>{{ $func->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-bottom:.875rem;">
                    <label class="popup-label">Valor Inicial em Dinheiro (R$)</label>
                    <input type="number" step="0.01" class="popup-input" id="caixa-valor-inicial" value="0.00" style="font-size:1.25rem;font-weight:800;color:var(--pos-accent);">
                </div>
            </div>
            <div class="popup-footer">
                <button type="button" class="popup-btn popup-btn-primary" onclick="confirmarAberturaCaixa()">
                    Abrir Caixa e Iniciar Vendas
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         POPUP — FECHAMENTO DE CAIXA
    ═══════════════════════════════════════════ --}}
    <div class="popup-overlay" id="popup-caixa-fechamento" role="dialog" aria-modal="true">
        <div class="popup-box" style="max-width:450px;">
            <div class="popup-head">
                 <div style="display:flex;align-items:center;gap:.5rem;">
                    <span class="popup-title">Fechamento de Caixa</span>
                    <span class="popup-shortcut">F7</span>
                </div>
                <button class="popup-close" onclick="fecharPopup('caixa-fechamento')" type="button">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="popup-body">
                <div id="fechamento-resumo" style="background:#f8fafc;border-radius:.75rem;padding:1rem;margin-bottom:1rem;display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                    <div>
                        <span class="popup-label">Início (Fundo)</span>
                        <div style="font-weight:700;" id="f-valor-inicial">R$ 0,00</div>
                    </div>
                    <div>
                        <span class="popup-label">Total de Vendas</span>
                        <div style="font-weight:700;" id="f-total-vendas">R$ 0,00</div>
                    </div>
                    <div style="grid-column: 1/-1;border-top:1px solid #e2e8f0;padding-top:.5rem;margin-top:.25rem;">
                        <span class="popup-label">Total Esperado no Caixa</span>
                        <div style="font-weight:800;font-size:1.1rem;color:var(--pos-accent);" id="f-total-esperado">R$ 0,00</div>
                    </div>
                </div>

                <div style="margin-bottom:.875rem;">
                    <label class="popup-label">Valor Físico Conferido (R$)</label>
                    <input type="number" step="0.01" class="popup-input" id="f-valor-informado" placeholder="0,00" style="font-size:1.25rem;font-weight:800;color:#10b981;">
                </div>
                
                <div>
                    <label class="popup-label">Observações de Fechamento</label>
                    <textarea class="popup-input" id="f-obs" rows="2" placeholder="Opcional..."></textarea>
                </div>
            </div>
            <div class="popup-footer">
                <button type="button" class="popup-btn popup-btn-success" onclick="confirmarFechamentoCaixa()">
                    Encerrar Turno e Salvar Relatório
                </button>
            </div>
        </div>
    </div>

    {{-- Gerador de QR Code --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    @push('scripts')
    <script>
    // ╔══════════════════════════════════════════════════════════════╗
    // ║  PDV — Frente de Balcão v2.1                                ║
    // ║  Autoria: Abimael Borges | https://abimaelborges.adv.br     ║
    // ║  Modificado: 2026-04-13T12:31:37-03:00                      ║
    // ╚══════════════════════════════════════════════════════════════╝
    'use strict';

    // ── Estado global ──
    const state = {
        cliente: null, cart: [], metodo: null,
        subtotal: 0, total: 0, desconto: 0, acrescimo: 0,
        recebido: 0, troco: 0,
        allProducts: [], activeCategory: 'all',
        pix: {
            chave: @json($pixConfig['chave'] ?? ''),
            beneficiario: @json($pixConfig['beneficiario'] ?? 'Grafica Vapt Vupt'),
            cidade: @json($pixConfig['cidade'] ?? 'SAO PAULO'),
            qr: null
        }
    };

    /**
     * Gerador de Payload PIX (BR Code / EMV QRCPS)
     * Baseado nos padrões do Banco Central
     */
    class PixGenerator {
        static generatePayload(chave, beneficiario, cidade, valor, identificador = 'PGVAPT') {
            const pad = (id, val) => id + (val.length.toString().padStart(2, '0')) + val;
            
            // 00: Payload Format Indicator
            let payload = pad('00', '01');
            
            // 26: Merchant Account Information
            const gui = pad('00', 'br.gov.bcb.pix');
            const key = pad('01', chave);
            payload += pad('26', gui + key);
            
            // 52: Merchant Category Code (Default: 0000)
            payload += pad('52', '0000');
            
            // 53: Transaction Currency (986: BRL)
            payload += pad('53', '986');
            
            // 54: Transaction Amount
            if (valor > 0) payload += pad('54', valor.toFixed(2));
            
            // 58: Country Code
            payload += pad('58', 'BR');
            
            // 59: Merchant Name (Max 25 chars)
            payload += pad('59', beneficiario.substring(0, 25).normalize('NFD').replace(/[\u0300-\u036f]/g, ""));
            
            // 60: Merchant City (Max 15 chars)
            payload += pad('60', cidade.substring(0, 15).normalize('NFD').replace(/[\u0300-\u036f]/g, ""));
            
            // 62: Additional Data Field Template
            const txid = pad('05', identificador);
            payload += pad('62', txid);
            
            // 63: CRC16
            payload += '6304';
            payload += this.calcularCRC16(payload);
            
            return payload;
        }

        static calcularCRC16(payload) {
            let resultado = 0xFFFF;
            const bytes = new TextEncoder().encode(payload);
            for (let b of bytes) {
                resultado ^= (b << 8);
                for (let i = 0; i < 8; i++) {
                    if ((resultado & 0x8000) !== 0) resultado = (resultado << 1) ^ 0x1021;
                    else resultado <<= 1;
                }
            }
            return (resultado & 0xFFFF).toString(16).toUpperCase().padStart(4, '0');
        }
    }

    // Ranking de mais vendidos (do server)
    const topRanking = @json($topProductIds ?? []);

    // ── Inicialização ──
    document.addEventListener('DOMContentLoaded', () => {
        carregarCatalogo();
        inicializarBuscas();
    });

    // ══════════════════════════════
    //  POPUPS (CLIENTE / PRODUTO)
    // ══════════════════════════════
    function abrirPopup(tipo) {
        const popup = document.getElementById(`popup-${tipo}`);
        if (!popup) return;
        popup.classList.add('active');
        // Foco automático no primeiro campo
        setTimeout(() => {
            const first = popup.querySelector('.popup-input');
            if (first) first.focus();
        }, 100);
    }

    function fecharPopup(tipo) {
        const popup = document.getElementById(`popup-${tipo}`);
        if (popup && popup.dataset.force !== 'true') {
            popup.classList.remove('active');
        }
    }

    // Fechar com Escape ou clicar fora
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.popup-overlay.active').forEach(p => {
                if (p.dataset.force !== 'true') p.classList.remove('active');
            });
        }
    });
    document.addEventListener('click', e => {
        if (e.target.classList.contains('popup-overlay')) {
            if (e.target.dataset.force !== 'true') e.target.classList.remove('active');
        }
    });

    // Submeter popup com Enter
    document.querySelectorAll('.popup-box form').forEach(form => {
        form.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.closest('.popup-box').querySelector('.popup-footer .popup-btn').click();
            }
        });
    });

    // ══════════════════════════════
    //  ATALHOS DE TECLADO
    // ══════════════════════════════
    window.addEventListener('keydown', e => {
        // Ignorar se estiver digitando num input/textarea
        const tag = document.activeElement?.tagName;
        const isTyping = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';

        if (e.key === 'F2') { 
            e.preventDefault(); 
            if (document.activeElement.id === 'search-cliente') {
                abrirPopupCadastroComNome();
            } else {
                abrirPopup('cliente');
            }
        }

        if (e.key === 'F4') { e.preventDefault(); abrirPopup('produto'); }
        if (e.key === 'F5') { e.preventDefault(); document.getElementById('search-produto').focus(); }
        if (e.key === 'F7') { e.preventDefault(); prepararFechamento(); }
        if (e.key === 'F8' && !document.getElementById('btn-finalizar').disabled) {
            e.preventDefault(); finalizarVenda();
        }
    });

    // ══════════════════════════════
    //  CATÁLOGO
    // ══════════════════════════════
    async function carregarCatalogo() {
        try {
            const res = await fetch(`{{ route('admin.pos.produtos') }}?q=`);
            const data = await res.json();
            state.allProducts = Array.isArray(data) ? data : [];
            atualizarContadores();
            renderGrid(state.allProducts);
        } catch (e) {
            renderGrid([]);
        }
    }

    function atualizarContadores() {
        document.getElementById('cnt-all').textContent = state.allProducts.length;
        const ids = @json($categorias->pluck('id'));
        ids.forEach(id => {
            const el = document.getElementById(`cnt-${id}`);
            if (el) el.textContent = state.allProducts.filter(p => p.categoria_id == id).length;
        });
    }

    function filterCat(id) {
        state.activeCategory = id;
        document.querySelectorAll('.cpill').forEach(p => p.classList.remove('active'));
        document.querySelector(`.cpill[data-id="${id}"]`).classList.add('active');
        const list = id === 'all' ? state.allProducts : state.allProducts.filter(p => p.categoria_id == id);
        renderGrid(ordenarPorPopularidade(list));
        document.getElementById('search-produto').value = '';
    }

    // Ordena produtos por ranking de mais vendidos
    function ordenarPorPopularidade(lista) {
        return [...lista].sort((a, b) => {
            const rankA = topRanking[a.id] || (a.total_vendido || 0);
            const rankB = topRanking[b.id] || (b.total_vendido || 0);
            return rankB - rankA;
        });
    }

    function renderGrid(produtos) {
        const grid = document.getElementById('product-grid');
        if (!produtos.length) {
            grid.innerHTML = `
                <div class="empty-state">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p>Nenhum produto encontrado</p>
                </div>`;
            return;
        }
        grid.innerHTML = produtos.map(p => {
            const img = p.imagem_principal ? `/storage/${p.imagem_principal}` : '/img/placeholder_produto.png';
            const preco = parseFloat(p.preco_base).toLocaleString('pt-BR', {minimumFractionDigits:2});
            const vendas = topRanking[p.id] || (p.total_vendido || 0);
            const isTop = vendas > 0;
            const pJson = JSON.stringify(p).replace(/'/g, '&#39;');
            return `
                <div class="prd-card" onclick='adicionarAoCarrinho(${pJson})' role="button" tabindex="0"
                    aria-label="Adicionar ${esc(p.nome)}" onkeydown="if(event.key==='Enter')this.click()">
                    <div class="prd-card-img-wrap">
                        ${isTop ? `<span class="top-badge">★ TOP</span>` : ''}
                        <img src="${img}" alt="${esc(p.nome)}" loading="lazy" onerror="this.src='/img/placeholder_produto.png'">
                        <div class="prd-card-add-overlay"><div class="prd-card-add-icon">+</div></div>
                    </div>
                    <div class="prd-card-body">
                        <div class="prd-card-name">${esc(p.nome)}</div>
                        <div class="prd-card-price">R$ ${preco}</div>
                    </div>
                </div>`;
        }).join('');
    }

    // ══════════════════════════════
    //  BUSCA DINÂMICA
    // ══════════════════════════════
    function inicializarBuscas() {
        // BUSCA CLIENTE (3 letras = sugestão)
        let cTimer;
        const cInput = document.getElementById('search-cliente');
        const cResults = document.getElementById('results-cliente');

        cInput.addEventListener('input', e => {
            clearTimeout(cTimer);
            const q = e.target.value.trim();
            if (q.length < 3) { fecharDropdown(cResults); return; }
            cTimer = setTimeout(() => buscarClientes(q), 200);
        });

        document.addEventListener('click', e => {
            if (!document.getElementById('client-search-wrap').contains(e.target)) fecharDropdown(cResults);
        });

        // BUSCA PRODUTO (local + 3 letras sugestão)
        document.getElementById('search-produto').addEventListener('input', e => {
            const q = e.target.value.toLowerCase().trim();
            if (q.length < 3) {
                // Com menos de 3 letras mostra tudo da categoria
                const catList = state.activeCategory === 'all'
                    ? state.allProducts
                    : state.allProducts.filter(p => p.categoria_id == state.activeCategory);
                renderGrid(ordenarPorPopularidade(catList));
                return;
            }
            const filtered = state.allProducts.filter(p =>
                p.nome.toLowerCase().includes(q) &&
                (state.activeCategory === 'all' || p.categoria_id == state.activeCategory)
            );
            renderGrid(ordenarPorPopularidade(filtered));
        });
    }

    async function buscarClientes(q) {
        try {
            const res = await fetch(`{{ route('admin.pos.clientes') }}?q=${encodeURIComponent(q)}`);
            const clientes = await res.json();
            const box = document.getElementById('results-cliente');
            
            let html = '';
            if (clientes.length) {
                html = clientes.map(c => {
                    const cJson = JSON.stringify(c).replace(/'/g, '&#39;');
                    return `
                    <div class="dd-item" onclick='selecionarCliente(${cJson})' role="button" tabindex="0">
                        <div class="dd-name">${esc(c.nome)}</div>
                        <div class="dd-info">${esc(c.whatsapp || '')} ${c.email ? '· '+esc(c.email) : ''} ${c.cpf_cnpj ? '· '+esc(c.cpf_cnpj) : ''}</div>
                    </div>`;
                }).join('');
            }

            // Sempre adicionar a opção de cadastro rápido no final se houver termo
            html += `
                <div class="dd-item" onclick="abrirPopupCadastroComNome()" style="background: #f0f9ff; border-top: 1px dashed #bfdbfe;">
                    <div class="dd-name" style="color: #2563eb; display: flex; align-items: center; gap: .5rem;">
                        <span>👤</span>
                        ${clientes.length ? 'Não encontrou? Cadastrar novo' : 'Cliente não encontrado. Cadastrar?'}
                    </div>
                    <div class="dd-info">Pressione <strong style="color:#1d4ed8">F2</strong> para cadastrar "${esc(q)}"</div>
                </div>`;

            box.innerHTML = html;
            box.classList.add('open');
        } catch(e) { fecharDropdown(document.getElementById('results-cliente')); }
    }

    function abrirPopupCadastroComNome() {
        const q = document.getElementById('search-cliente').value.trim();
        abrirPopup('cliente');
        if (q) {
            const nomeInput = document.getElementById('fc-nome');
            // Se for número, joga pro WhatsApp, senão pro Nome
            if (/^\d+$/.test(q.replace(/\D/g, ''))) {
                document.getElementById('fc-wp').value = q;
            } else {
                nomeInput.value = q;
            }
        }
    }

    function fecharDropdown(el) { el.classList.remove('open'); }

    // ══════════════════════════════
    //  CLIENTE
    // ══════════════════════════════
    function selecionarCliente(c) {
        state.cliente = c;
        document.getElementById('search-cliente').value = '';
        fecharDropdown(document.getElementById('results-cliente'));
        document.getElementById('sel-nome').textContent = c.nome;
        document.getElementById('sel-info').textContent = [c.whatsapp, c.email, c.cpf_cnpj].filter(Boolean).join(' · ');
        document.getElementById('cliente-selecionado').classList.add('active');
        document.getElementById('client-search-wrap').style.opacity = '.4';
        document.getElementById('client-search-wrap').style.pointerEvents = 'none';
        validarFinalizacao();
    }

    function removerCliente() {
        state.cliente = null;
        document.getElementById('cliente-selecionado').classList.remove('active');
        document.getElementById('client-search-wrap').style.opacity = '';
        document.getElementById('client-search-wrap').style.pointerEvents = '';
        validarFinalizacao();
    }

    // ══════════════════════════════
    //  CARRINHO
    // ══════════════════════════════
    function adicionarAoCarrinho(p) {
        state.cart.push({
            id: Date.now() + Math.random(),
            produto_id: p.id, nome: p.nome,
            quantidade: 1,
            valor_unitario: parseFloat(p.preco_base),
            imagem: p.imagem_principal,
        });
        renderCart();
    }



    function renderCart() {
        const body   = document.getElementById('cart-body');
        const empty  = document.getElementById('cart-empty');
        const badge  = document.getElementById('cart-badge');
        const clrBtn = document.getElementById('cart-clear-btn');

        badge.textContent = `${state.cart.length} ${state.cart.length === 1 ? 'ITEM' : 'ITENS'}`;
        badge.classList.toggle('has-items', state.cart.length > 0);
        clrBtn.classList.toggle('visible', state.cart.length > 0);

        if (!state.cart.length) {
            body.innerHTML = '';
            body.appendChild(empty);
        } else {
            body.innerHTML = state.cart.map(item => {
                const img   = item.imagem ? `/storage/${item.imagem}` : '/img/placeholder_produto.png';
                const preco = item.valor_unitario.toFixed(2);
                return `
                    <div class="cart-item">
                        <img class="cart-item-img" src="${img}" alt="" onerror="this.src='/img/placeholder_produto.png'" loading="lazy">
                        <div style="min-width:0;">
                            <div class="cart-item-name" contenteditable="true" title="Editar nome"
                                onblur="atualizarNome(${item.id}, this)">${esc(item.nome)}</div>
                        </div>
                        <div>
                            <div class="qty-ctrl">
                                <button class="qty-btn" onclick="atualizarQtd(${item.id},-1,true)" type="button" aria-label="Diminuir">−</button>
                                <input class="qty-val" type="number" value="${item.quantidade}" min="1"
                                    onchange="atualizarQtd(${item.id},this.value)" aria-label="Quantidade">
                                <button class="qty-btn" onclick="atualizarQtd(${item.id},1,true)" type="button" aria-label="Aumentar">+</button>
                            </div>
                            <div class="cart-item-price-wrap" style="margin-top:.3rem;">
                                <span class="cart-item-price-prefix">R$</span>
                                <input class="cart-item-price" type="number" step="0.01" value="${preco}"
                                    onchange="atualizarPreco(${item.id},this.value)" aria-label="Preço">
                            </div>
                        </div>
                        <button class="item-del-btn" onclick="removerItem(${item.id})" type="button" title="Remover" aria-label="Remover">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>`;
            }).join('');
        }
        calcularTotais();
    }

    function atualizarQtd(id, val, rel = false) {
        const item = state.cart.find(x => x.id === id);
        if (!item) return;
        item.quantidade = rel ? Math.max(1, item.quantidade + val) : Math.max(1, parseInt(val) || 1);
        renderCart();
    }
    function atualizarPreco(id, val) {
        const item = state.cart.find(x => x.id === id);
        if (item) { item.valor_unitario = Math.max(0, parseFloat(val) || 0); calcularTotais(); }
    }
    function atualizarNome(id, el) {
        const item = state.cart.find(x => x.id === id);
        if (item) item.nome = el.innerText.trim() || item.nome;
    }
    function removerItem(id) { state.cart = state.cart.filter(x => x.id !== id); renderCart(); }
    function limparCarrinho() {
        if (!confirm('Remover todos os itens do carrinho?')) return;
        state.cart = []; renderCart();
    }

    // ══════════════════════════════
    //  FINANCEIRO
    // ══════════════════════════════
    function calcularTotais() {
        state.subtotal  = state.cart.reduce((s,i) => s + i.quantidade * i.valor_unitario, 0);
        state.desconto  = Math.max(0, parseFloat(document.getElementById('input-desconto').value) || 0);
        state.acrescimo = Math.max(0, parseFloat(document.getElementById('input-acrescimo').value) || 0);
        state.total     = Math.max(0, state.subtotal + state.acrescimo - state.desconto);

        document.getElementById('label-subtotal').textContent = fmt(state.subtotal);
        document.getElementById('label-total').textContent    = fmt(state.total);
        
        if (state.metodo === 'Pix') atualizarQRCodePix();
        
        calcularTroco(); validarFinalizacao();
    }

    function atualizarQRCodePix() {
        if (!state.pix.chave) return;
        
        const payload = PixGenerator.generatePayload(
            state.pix.chave, 
            state.pix.beneficiario, 
            state.pix.cidade, 
            state.total
        );

        document.getElementById('pix-copy-paste').value = payload;
        document.getElementById('pix-display-total').textContent = state.total.toLocaleString('pt-BR', {minimumFractionDigits:2});

        const container = document.getElementById('pix-qrcode');
        container.innerHTML = '';
        state.pix.qr = new QRCode(container, {
            text: payload,
            width: 160,
            height: 160,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    document.getElementById('pix-copy-paste')?.addEventListener('click', function() {
        this.select();
        document.execCommand('copy');
        const originalVal = this.value;
        this.value = 'COPIADO!';
        setTimeout(() => this.value = originalVal, 1500);
    });

    function calcularTroco() {
        if (state.metodo !== 'Dinheiro') return;
        state.recebido = parseFloat(document.getElementById('cash-received').value) || 0;
        state.troco    = Math.max(0, state.recebido - state.total);
        const el = document.getElementById('cash-change');
        el.textContent = fmt(state.troco);
        el.classList.toggle('negative', state.recebido > 0 && state.recebido < state.total);
    }

    function setMetodo(m) {
        state.metodo = m;
        document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('active'));
        const btnId = 'pay-' + m.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/\s+/g,'-');
        const btn = document.getElementById(btnId);
        if (btn) btn.classList.add('active');
        document.getElementById('cash-panel').classList.toggle('visible', m === 'Dinheiro');
        document.getElementById('pix-panel').classList.toggle('visible', m === 'Pix');
        
        if (m === 'Pix') atualizarQRCodePix();
        
        validarFinalizacao();
    }

    // ══════════════════════════════
    //  CADASTROS RÁPIDOS (POPUP)
    // ══════════════════════════════
    async function salvarClienteRapido() {
        const form = document.getElementById('form-cliente-rapido');
        const data = Object.fromEntries(new FormData(form));
        if (!data.nome || !data.whatsapp) { alert('Preencha Nome e WhatsApp.'); return; }
        try {
            const res = await fetch("{{ route('admin.pos.cliente-rapido') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();
            if (json.success) {
                selecionarCliente(json.cliente);
                fecharPopup('cliente');
                form.reset();
            } else alert(json.message || 'Erro ao cadastrar.');
        } catch(e) { alert('Falha na conexão.'); }
    }

    async function salvarProdutoRapido() {
        const form = document.getElementById('form-produto-rapido');
        const data = Object.fromEntries(new FormData(form));
        if (!data.nome || !data.preco) { alert('Preencha Descrição e Preço.'); return; }
        try {
            const res = await fetch("{{ route('admin.pos.produto-rapido') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();
            if (json.success) {
                adicionarAoCarrinho(json.produto);
                fecharPopup('produto');
                form.reset();
                await carregarCatalogo();
            } else alert(json.message || 'Erro ao salvar.');
        } catch(e) { alert('Falha na conexão.'); }
    }

    // ══════════════════════════════
    //  GERENCIAMENTO DE CAIXA
    // ══════════════════════════════
    async function confirmarAberturaCaixa() {
        const btn = document.querySelector('#popup-caixa-abertura .popup-btn-primary');
        const valor = parseFloat(document.getElementById('caixa-valor-inicial').value) || 0;
        const usuarioId = document.getElementById('caixa-usuario-id').value;
        
        if (btn) {
            btn.disabled = true;
            btn.dataset.oldText = btn.textContent;
            btn.textContent = 'ABRINDO...';
        }

        try {
            const res = await fetch("{{ route('admin.pos.caixa.abrir') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    valor_inicial: valor,
                    usuario_id: usuarioId
                })
            });
            const json = await res.json();
            
            if (json.success) {
                // Fechar modal antes de recarregar para feedback imediato
                const popup = document.getElementById('popup-caixa-abertura');
                if (popup) {
                    popup.dataset.force = 'false'; // Permitir fechamento
                    popup.classList.remove('active');
                }
                
                // Pequeno delay para o usuário ver que deu certo antes do reload
                setTimeout(() => window.location.reload(), 300);
            } else {
                alert(json.message || 'Erro ao abrir caixa.');
                restaurarBotaoAbertura(btn);
            }
        } catch(e) { 
            alert('Falha na conexão com o servidor.'); 
            restaurarBotaoAbertura(btn);
        }
    }

    function restaurarBotaoAbertura(btn) {
        if (btn) {
            btn.disabled = false;
            btn.textContent = btn.dataset.oldText || 'Abrir Caixa e Iniciar Vendas';
        }
    }

    let currentCaixa = @json($caixaAberto);

    function prepararFechamento() {
        if (!currentCaixa) { alert('Nenhum caixa aberto.'); return; }
        
        // Buscar status atualizado do caixa
        fetch("{{ route('admin.pos.caixa.status') }}")
            .then(res => res.json())
            .then(json => {
                if (!json.aberto) { window.location.reload(); return; }
                
                const c = json.caixa;
                // Valor inicial + vendas realizadas
                // Como não temos as vendas em tempo real no state JS (apenas as desta sessão não finalizada),
                // o servidor fará o cálculo final, mas vamos mostrar um resumo prévio se possível.
                // Na verdade, o controller já tem a lógica de soma.
                
                document.getElementById('f-valor-inicial').textContent = fmt(parseFloat(c.valor_inicial));
                document.getElementById('f-total-vendas').textContent = 'Calculando...';
                document.getElementById('f-total-esperado').textContent = '...';
                
                abrirPopup('caixa-fechamento');
                
                // Simulação visual rápida baseada nas vendas desta sessão (opcional)
                // Vamos deixar o servidor processar para ser 100% preciso com o DB.
            });
    }

    async function confirmarFechamentoCaixa() {
        const valor = parseFloat(document.getElementById('f-valor-informado').value) || 0;
        const obs = document.getElementById('f-obs').value;
        
        if (!confirm('Deseja realmente encerrar este turno de caixa?')) return;

        try {
            const res = await fetch("{{ route('admin.pos.caixa.fechar') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ valor_fechamento: valor, observacoes: obs })
            });
            const json = await res.json();
            if (json.success) {
                alert(`Caixa fechado com sucesso!\nTotal de Vendas: R$ ${json.resumo.vendas.toFixed(2)}\nDiferença: R$ ${json.resumo.diferenca.toFixed(2)}`);
                window.location.reload();
            } else {
                alert(json.message || 'Erro ao fechar caixa.');
            }
        } catch(e) { alert('Falha na conexão.'); }
    }
    function validarFinalizacao() {
        const ok = state.cart.length > 0 && state.cliente !== null && state.metodo !== null;
        document.getElementById('btn-finalizar').disabled = !ok;
    }

    async function finalizarVenda() {
        if (!confirm('Confirmar recebimento e finalizar venda?')) return;
        const btn = document.getElementById('btn-finalizar');
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg> PROCESSANDO...`;

        const payload = {
            cliente_id: state.cliente.id,
            itens: state.cart,
            pagamentos: [{ metodo: state.metodo, valor: state.total }],
            desconto: state.desconto,
            acrescimo: state.acrescimo,
            valor_recebido: state.recebido,
            troco: state.troco,
            observacao_geral: 'Venda rápida PDV',
            prazo_entrega: new Date().toISOString().split('T')[0]
        };

        try {
            const res = await fetch("{{ route('admin.pos.finalizar') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (json.success) { window.open(json.os_url, '_blank'); window.location.reload(); }
            else { alert('Erro: ' + (json.message || 'Falha.')); restaurarBtn(); }
        } catch(e) { alert('Falha na conexão.'); restaurarBtn(); }
    }

    function restaurarBtn() {
        const btn = document.getElementById('btn-finalizar');
        btn.disabled = false;
        btn.innerHTML = `<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> FINALIZAR E IMPRIMIR <span style="font-size:.65rem;opacity:.7;font-family:'Outfit',sans-serif;font-weight:600;">(F8)</span>`;
    }

    // ── Utilitários ──
    function fmt(v) { return 'R$ ' + v.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2}); }
    function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    </script>
    @endpush
</x-layouts.pdv>
