<x-layouts.publico titulo="Crie sua conta - vaptCRM" :fullWidth="true" :hideNav="true">

    {{-- 
        Autoria: Abimael Borges
        https://abimaelborges.adv.br
        Data: 14/04/2026 00:40
    --}}

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --vapt-dark: #050505;
            --vapt-accent: #FF7A00;
            --vapt-blue: #0ea5e9;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --font-display: 'Syne', sans-serif;
            --font-body: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--vapt-dark);
            color: #fff;
            font-family: var(--font-body);
            overflow-x: hidden;
        }

        .bg-mesh {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: 
                radial-gradient(circle at 0% 0%, rgba(255, 122, 0, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(14, 165, 233, 0.15) 0%, transparent 50%);
            opacity: 0.6;
        }

        .container-onboarding {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .onboarding-card {
            width: 100%;
            max-width: 1000px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 2rem;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .plan-info {
            padding: 3rem;
            background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, transparent 100%);
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-section {
            padding: 3rem;
        }

        .plan-badge {
            background: var(--vapt-accent);
            color: #000;
            padding: 0.5rem 1rem;
            border-radius: 100px;
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
            width: fit-content;
            margin-bottom: 1.5rem;
        }

        .plan-title {
            font-family: var(--font-display);
            font-size: 2.5rem;
            line-height: 1;
            margin-bottom: 1rem;
        }

        .plan-price {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 2rem;
            color: var(--vapt-blue);
        }

        .plan-price span {
            font-size: 1rem;
            font-weight: 400;
            color: rgba(255,255,255,0.5);
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            color: rgba(255,255,255,0.7);
        }

        .feature-item svg {
            color: var(--vapt-accent);
            flex-shrink: 0;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group label {
            display: block;
            font-size: 0.875rem;
            color: rgba(255,255,255,0.6);
            margin-bottom: 0.5rem;
        }

        .input-control {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            color: #fff;
            font-family: var(--font-body);
            transition: all 0.3s ease;
        }

        .input-control:focus {
            outline: none;
            border-color: var(--vapt-blue);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
        }

        .btn-register {
            width: 100%;
            background: var(--vapt-accent);
            color: #000;
            border: none;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 800;
            font-size: 1.125rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px var(--vapt-accent);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 900px) {
            .onboarding-card {
                grid-template-columns: 1fr;
            }
            .plan-info {
                border-right: none;
                border-bottom: 1px solid var(--glass-border);
            }
        }
    </style>

    <div class="bg-mesh"></div>

    <div class="container-onboarding">
        <div class="onboarding-card">
            <div class="plan-info">
                <div class="plan-badge">Plano Selecionado</div>
                <h1 class="plan-title">Plano {{ $plano->nome }}</h1>
                <div class="plan-price">R$ {{ number_format($plano->preco_mensal, 2, ',', '.') }} <span>/mês</span></div>
                
                <ul class="feature-list">
                    @foreach($plano->beneficios ?? [] as $beneficio)
                    <li class="feature-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        {{ $beneficio }}
                    </li>
                    @endforeach
                    @if(empty($plano->beneficios))
                        <li class="feature-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Gestão completa de pedidos
                        </li>
                        <li class="feature-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Link de catálogo exclusivo
                        </li>
                        <li class="feature-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Pagamentos via PIX e Stripe
                        </li>
                    @endif
                </ul>

                <div style="margin-top: 3rem; color: rgba(255,255,255,0.4); font-size: 0.875rem;">
                    Experimente grátis por <strong style="color: #FF7A00;">15 dias</strong>. Sem compromisso durante o trial.
                </div>
            </div>

            <div class="form-section">
                <h2 style="font-family: var(--font-display); font-size: 1.5rem; margin-bottom: 2rem;">Comece seus 15 dias grátis</h2>

                @if ($errors->any())
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 1rem; border-radius: 1rem; margin-bottom: 2rem; color: #f87171; font-size: 0.875rem;">
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('onboarding.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plano_id" value="{{ $plano->id }}">

                    <div class="input-group">
                        <label>Nome Fantasia da sua Gráfica</label>
                        <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia') }}" class="input-control" placeholder="Ex: Gráfica Rápida Vapt" required>
                    </div>

                    <div class="input-group">
                        <label>Nome do Responsável</label>
                        <input type="text" name="responsavel_nome" value="{{ old('responsavel_nome') }}" class="input-control" placeholder="Seu nome completo" required>
                    </div>

                    <div class="grid-2">
                        <div class="input-group">
                            <label>E-mail de Acesso</label>
                            <input type="email" name="responsavel_email" value="{{ old('responsavel_email') }}" class="input-control" placeholder="seu@email.com" required>
                        </div>
                        <div class="input-group">
                            <label>WhatsApp (Opcional)</label>
                            <input type="text" name="responsavel_whatsapp" value="{{ old('responsavel_whatsapp') }}" class="input-control" placeholder="(00) 00000-0000">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="input-group">
                            <label>Crie sua Senha</label>
                            <input type="password" name="senha" class="input-control" required>
                        </div>
                        <div class="input-group">
                            <label>Confirme a Senha</label>
                            <input type="password" name="senha_confirmation" class="input-control" required>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem; display: flex; gap: 0.75rem; align-items: flex-start;">
                        <input type="checkbox" name="termos" value="1" id="termos" style="margin-top: 0.25rem;" required>
                        <label for="termos" style="font-size: 0.75rem; color: rgba(255,255,255,0.5); cursor: pointer;">
                            Ao me cadastrar, concordo com os <a href="{{ route('site.pagina', 'termos-de-uso') }}" target="_blank" style="color:#FF7A00;">Termos de Uso</a> e a <a href="{{ route('site.pagina', 'politica-de-privacidade') }}" target="_blank" style="color:#FF7A00;">Política de Privacidade</a>.
                            Após os 15 dias grátis, a assinatura mensal será renovada automaticamente.
                        </label>
                    </div>

                    <button type="submit" class="btn-register">
                        Finalizar e Entrar no Painel
                    </button>
                    
                    <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: rgba(255,255,255,0.4);">
                        Já tem uma conta? <a href="{{ route('login') }}" style="color: var(--vapt-blue); text-decoration: none;">Faça login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

</x-layouts.publico>
