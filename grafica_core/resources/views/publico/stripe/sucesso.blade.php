<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Confirmado — CRM Gráfica</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-wrap {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 0 40px rgba(16,185,129,0.35);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 40px rgba(16,185,129,0.35); }
            50% { box-shadow: 0 0 60px rgba(16,185,129,0.6); }
        }

        .icon-wrap svg {
            width: 44px;
            height: 44px;
            color: white;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 0.75rem;
        }

        .subtitle {
            font-size: 1rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .info-box {
            background: rgba(16,185,129,0.08);
            border: 1px solid rgba(16,185,129,0.2);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.4rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .info-row:last-child { border-bottom: none; }

        .info-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            font-size: 0.9rem;
            color: #e2e8f0;
            font-weight: 600;
        }

        .badge-pago {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(16,185,129,0.15);
            color: #10b981;
            border: 1px solid rgba(16,185,129,0.3);
            border-radius: 50px;
            padding: 0.3rem 0.9rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 2rem;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            box-shadow: 0 4px 15px rgba(99,102,241,0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99,102,241,0.45);
        }

        .btn-ghost {
            background: rgba(255,255,255,0.06);
            color: #94a3b8;
            border: 1px solid rgba(255,255,255,0.1);
            margin-left: 0.5rem;
        }

        .btn-ghost:hover {
            background: rgba(255,255,255,0.1);
            color: #e2e8f0;
        }

        .actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }

        .logo { margin-bottom: 1.5rem; }
        .logo span {
            font-size: 1.1rem;
            font-weight: 700;
            color: #6366f1;
            letter-spacing: -0.5px;
        }

        @media (max-width: 480px) {
            .card { padding: 2rem 1.5rem; }
            h1 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <span>CRM Gráfica</span>
        </div>

        <div class="icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1>Pagamento Confirmado!</h1>
        <p class="subtitle">
            Recebemos seu pagamento com sucesso. Nossa equipe já foi notificada e seu pedido entrará em produção em breve.
        </p>

        @if($pedido)
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Pedido</span>
                    <span class="info-value">#{{ $pedido->numero }}</span>
                </div>
                @if($pedido->total)
                <div class="info-row">
                    <span class="info-label">Valor</span>
                    <span class="info-value">R$ {{ number_format($pedido->total, 2, ',', '.') }}</span>
                </div>
                @endif
                @if($pedido->cliente)
                <div class="info-row">
                    <span class="info-label">Cliente</span>
                    <span class="info-value">{{ $pedido->cliente->nome }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="badge-pago">
                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="4"/>
                        </svg>
                        Pago
                    </span>
                </div>
            </div>
        @endif

        <div class="actions">
            <a href="{{ route('site.pedido.acompanhar') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Acompanhar Pedido
            </a>
            <a href="{{ route('site.inicio') }}" class="btn btn-ghost">
                Início
            </a>
        </div>
    </div>
</body>
</html>

