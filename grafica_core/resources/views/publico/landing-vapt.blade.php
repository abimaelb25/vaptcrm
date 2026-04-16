<x-layouts.publico titulo="vaptCRM - Gestão Inteligente para sua Gráfica" :fullWidth="true" :hideNav="true">

    {{-- 
        Autoria: Abimael Borges
        https://abimaelborges.adv.br
        Data: 14/04/2026 02:00
    --}}

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --vapt-dark: #050505;
            --vapt-accent: #FF7A00;
            --vapt-blue: #0ea5e9;
            --font-display: 'Syne', sans-serif;
            --font-body: 'Outfit', sans-serif;
        }

        .vapt-landing {
            font-family: var(--font-body);
            background: var(--vapt-dark);
            color: #fff;
            overflow-x: hidden;
        }

        .font-display { font-family: var(--font-display); }

        /* Full Width Section Utils */
        .section-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Glassmorphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .glass-card:hover {
            border-color: rgba(255, 122, 0, 0.4);
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.05);
        }

        /* Text Gradient */
        .text-gradient {
            background: linear-gradient(135deg, #fff 0%, #aaa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Hero Text Responsive */
        .hero-title {
            font-size: clamp(2.5rem, 8vw, 6rem);
            line-height: 1.1;
        }

        /* CTA Button */
        .btn-vapt {
            background: var(--vapt-accent);
            color: #fff;
            padding: 1.25rem 2.5rem;
            border-radius: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 25px rgba(255, 122, 0, 0.3);
        }

        .btn-vapt:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 35px rgba(255, 122, 0, 0.5);
            background: #ff8c20;
        }

        /* Animations */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        .animate-up { opacity: 0; animation: fadeInUp 0.8s ease-out forwards; }
        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }

        /* Mockup Grid */
        .mockup-grid {
            display: grid;
            gap: 2rem;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        /* Academy Section Specifics */
        .academy-banner {
            background: linear-gradient(135deg, rgba(255, 122, 0, 0.1) 0%, rgba(14, 165, 233, 0.1) 100%);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 40px;
            padding: 4rem;
            text-align: left;
            position: relative;
            overflow: hidden;
        }

        /* Plans Grid */
        .plan-card {
            position: relative;
            overflow: hidden;
        }

        .plan-card.featured {
            border: 2px solid var(--vapt-accent);
            box-shadow: 0 0 30px rgba(255, 122, 0, 0.2);
        }

        .plan-card.featured::after {
            content: 'MAIS POPULAR';
            position: absolute;
            top: 20px;
            right: -30px;
            background: var(--vapt-accent);
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            padding: 4px 35px;
            transform: rotate(45deg);
        }

        .badge-trial {
            background: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 1rem;
            display: inline-block;
        }
    </style>

    <div class="vapt-landing">
        
        {{-- Hero Section (Full Width) --}}
        <section class="relative pt-32 pb-48 overflow-hidden">
            {{-- Background Elements --}}
            <div class="absolute top-0 right-0 w-[50vw] h-[50vw] bg-brand-primary/10 rounded-full blur-[120px] -translate-y-1/2"></div>
            <div class="absolute bottom-0 left-0 w-[40vw] h-[40vw] bg-blue-500/5 rounded-full blur-[120px] translate-y-1/2"></div>

            <div class="section-container text-center relative z-10">
                <div class="inline-block bg-white/5 border border-white/10 rounded-full px-6 py-2 text-xs font-bold tracking-[0.2em] mb-12 animate-up">
                    TESTE GRÁTIS POR 15 DIAS
                </div>
                <h1 class="font-display hero-title font-black mb-10 animate-up delay-1">
                    O Sistema que sua <br>
                    <span class="text-gradient">Gráfica merece.</span>
                </h1>
                <p class="text-xl md:text-2xl text-slate-400 max-w-3xl mx-auto mb-16 animate-up delay-2 leading-relaxed">
                    Controle de produção, financeiro completo, PDV ágil e integração com WhatsApp em uma única plataforma feita por quem entende de gráfica.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 animate-up delay-3">
                    <a href="#planos" class="btn-vapt">Começar Teste Grátis</a>
                    <p class="text-xs text-slate-500 font-bold max-w-[200px] text-left">Requer cartão de crédito para renovação automática após os 15 dias grátis.</p>
                </div>
            </div>
        </section>

        {{-- Features Section (Restored) --}}
        <section class="py-32 px-6 relative z-10">
            <div class="section-container">
                <div class="text-center mb-20">
                    <h2 class="font-display text-3xl md:text-5xl font-black mb-4">Tudo o que sua gráfica precisa.</h2>
                    <p class="text-slate-400">Funcionalidades pensadas na realidade de quem produz.</p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="glass-card p-8">
                        <div class="w-12 h-12 bg-orange-500/10 rounded-xl flex items-center justify-center mb-6 text-orange-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path></svg>
                        </div>
                        <h4 class="font-bold mb-2 text-white">Gestão de Clientes</h4>
                        <p class="text-xs text-slate-500">Histórico completo, CRM de vendas e retenção automatizada.</p>
                    </div>

                    <div class="glass-card p-8">
                        <div class="w-12 h-12 bg-blue-500/10 rounded-xl flex items-center justify-center mb-6 text-blue-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <h4 class="font-bold mb-2 text-white">Pedidos & Orçamentos</h4>
                        <p class="text-xs text-slate-500">Envio de orçamentos via WhatsApp e acompanhamento de produção.</p>
                    </div>

                    <div class="glass-card p-8">
                        <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center mb-6 text-purple-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h4 class="font-bold mb-2 text-white">Equipe & Funcionários</h4>
                        <p class="text-xs text-slate-500">Gestão de acessos, comissões e produtividade individual.</p>
                    </div>

                    <div class="glass-card p-8">
                        <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center mb-6 text-emerald-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <h4 class="font-bold mb-2 text-white">Fluxo de Caixa</h4>
                        <p class="text-xs text-slate-500">Registro detalhado de entradas, saídas e previsibilidade financeira.</p>
                    </div>

                    <div class="glass-card p-8">
                        <div class="w-12 h-12 bg-red-500/10 rounded-xl flex items-center justify-center mb-6 text-red-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path></svg>
                        </div>
                        <h4 class="font-bold mb-2 text-white">Relatórios & BI</h4>
                        <p class="text-xs text-slate-500">Métricas cruciais para tomada de decisão baseada em dados reais.</p>
                    </div>

                    <div class="glass-card p-8">
                        <div class="w-12 h-12 bg-indigo-500/10 rounded-xl flex items-center justify-center mb-6 text-indigo-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <h4 class="font-bold mb-2 text-white">Pagamentos Online</h4>
                        <p class="text-xs text-slate-500">Checkout integrado com Stripe e PIX com baixa automática.</p>
                    </div>

                    <div class="glass-card p-8 border-brand-primary/20">
                        <div class="w-12 h-12 bg-brand-primary/10 rounded-xl flex items-center justify-center mb-6 text-brand-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <h4 class="font-bold mb-2 text-white">PDV (Frente de Caixa)</h4>
                        <p class="text-xs text-slate-500">Vendas rápidas no balcão com geração de recibos e controle de estoque.</p>
                    </div>

                    <div class="glass-card p-8">
                        <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center mb-6 text-amber-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <h4 class="font-bold mb-2 text-white">Mobility First</h4>
                        <p class="text-xs text-slate-500">Acesse e gerencie sua gráfica de qualquer lugar, direto do celular.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Seção de Mockups (Galeria) --}}
        <section class="py-32 bg-white/[0.02]">
            <div class="section-container">
                <div class="text-center mb-24">
                    <span class="text-brand-primary font-black uppercase tracking-widest text-xs">Visibilidade Total</span>
                    <h2 class="font-display text-4xl md:text-5xl font-black mt-4 text-white">Interface de Alta Performance.</h2>
                </div>

                <div class="mockup-grid">
                    <div class="glass-card overflow-hidden group">
                        <img src="{{ asset('images/landing/mockup-vapt.png') }}" class="w-full transition-transform duration-700 group-hover:scale-105" alt="Dashboard Principal">
                        <div class="p-8">
                            <h4 class="font-bold text-xl mb-2 text-white">Dashboard Central</h4>
                            <p class="text-sm text-slate-500">Métricas de vendas, produção e financeiro em tempo real.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Pricing Section (Restored & Updated) --}}
        <section id="planos" class="py-32 bg-white/[0.02] border-y border-white/5">
            <div class="section-container">
                <div class="text-center mb-24">
                    <h2 class="font-display text-4xl md:text-6xl font-black mb-6 text-white">Investimento Inteligente</h2>
                    <p class="text-xl text-slate-400">Comece hoje mesmo com <strong>15 dias de teste grátis</strong> em qualquer plano.</p>
                    <p class="text-sm text-slate-500 mt-4">Necessário vincular cartão de crédito para ativação segura da conta.</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($planos as $plano)
                        <div class="glass-card plan-card p-8 flex flex-col {{ $plano->slug === 'prata' ? 'featured' : '' }}">
                            <div class="mb-10 text-center">
                                <div class="badge-trial">15 dias Grátis</div>
                                <h4 class="font-display text-amber-500 uppercase tracking-widest text-sm font-bold mb-4">{{ $plano->nome }}</h4>
                                <div class="flex items-end justify-center gap-1">
                                    <span class="text-3xl font-bold mb-1 text-white">R$</span>
                                    <span class="text-6xl font-black tracking-tighter text-white">{{ number_format($plano->preco_mensal, 2, ',', '.') }}</span>
                                    <span class="text-slate-500 mb-2">/mês</span>
                                </div>
                            </div>

                            <ul class="space-y-4 mb-10 flex-1">
                                <li class="flex items-center gap-3 text-sm text-slate-300">
                                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @if($plano->limite_produtos) Até {{ $plano->limite_produtos }} Produtos @else Produtos Ilimitados @endif
                                </li>
                                <li class="flex items-center gap-3 text-sm text-slate-300">
                                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @if($plano->limite_funcionarios) Até {{ $plano->limite_funcionarios }} Usuários @else Usuários Ilimitados @endif
                                </li>
                                @foreach($plano->recursos_premium ?? [] as $key => $valor)
                                    @if($valor)
                                    <li class="flex items-center gap-3 text-sm text-slate-300">
                                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        {{ str_replace('_', ' ', ucfirst($key)) }}
                                    </li>
                                    @endif
                                @endforeach
                            </ul>

                            <a href="{{ route('onboarding.start', $plano->slug) }}" class="w-full py-4 glass-card text-center font-bold hover:bg-brand-primary hover:text-white hover:border-brand-primary transition-all text-white">
                                Experimentar Grátis
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Treinamento para Usuários (Vapt Academy) --}}
        <section class="py-32 relative">
            <div class="section-container">
                <div class="academy-banner">
                    <div class="grid lg:grid-cols-2 gap-16 items-center">
                        <div>
                            <span class="bg-brand-primary text-white font-black text-[10px] px-3 py-1 rounded-sm uppercase mb-6 inline-block">Bônus Exclusivo</span>
                            <h2 class="font-display text-4xl md:text-5xl font-black mb-8 text-white">Treinamento para <br>usuários do sistema.</h2>
                            <p class="text-lg text-slate-300 mb-10 leading-relaxed">
                                Não apenas entregamos o software. Ensinamos você e sua equipe a dominar cada funcionalidade através de <strong>aulas gravadas completas</strong>.
                            </p>
                            <ul class="space-y-4 mb-12">
                                <li class="flex items-center gap-4 text-sm font-bold text-slate-300">
                                    <div class="w-2 h-2 rounded-full bg-brand-primary"></div>
                                    Como cadastrar produtos e clientes de forma otimizada
                                </li>
                                <li class="flex items-center gap-4 text-sm font-bold text-slate-300">
                                    <div class="w-2 h-2 rounded-full bg-brand-primary"></div>
                                    Configuração completa da loja e catálogo online
                                </li>
                                <li class="flex items-center gap-4 text-sm font-bold text-slate-300">
                                    <div class="w-2 h-2 rounded-full bg-brand-primary"></div>
                                    Integrações de pagamento (Stripe, Mercado Pago e PIX)
                                </li>
                                <li class="flex items-center gap-4 text-sm font-bold text-slate-300">
                                    <div class="w-2 h-2 rounded-full bg-brand-primary"></div>
                                    Suporte humanizado para tirar dúvidas
                                </li>
                            </ul>
                            <div class="p-6 bg-white/5 rounded-2xl border border-white/5">
                                <p class="text-xs font-bold text-emerald-400">DISPONÍVEL GRATUITAMENTE PARA ASSINANTES</p>
                            </div>
                        </div>
                        <div class="relative">
                            <img src="{{ asset('images/landing/vapt-academy.png') }}" class="w-full rounded-2xl shadow-2xl relative z-10" alt="Treinamento vaptCRM">
                            <div class="absolute -inset-4 bg-brand-primary/20 blur-2xl rounded-full z-0"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Depoimentos B2B Dinâmicos --}}
        @if($depoimentos->count() > 0)
            <section class="py-32">
                <div class="section-container">
                    <div class="text-center mb-24">
                        <span class="text-vapt-accent font-black uppercase tracking-widest text-[10px] mb-4 block">Prova Social Real</span>
                        <h2 class="font-display text-4xl font-black text-white px-6">Quem domina o mercado usa vaptCRM.</h2>
                    </div>
                    
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($depoimentos as $dep)
                            <div class="glass-card p-10 flex flex-col {{ $dep->destaque ? 'border-vapt-accent/30 bg-vapt-accent/[0.02]' : '' }}">
                                <div class="text-xl mb-6 flex gap-1">
                                    @for($i=1; $i<=5; $i++)
                                        <span class="{{ $i <= ($dep->nota ?? 5) ? 'text-vapt-accent' : 'text-slate-700' }}">★</span>
                                    @endfor
                                </div>
                                <blockquote class="text-slate-300 italic mb-8 flex-1 leading-relaxed">
                                    "{{ $dep->depoimento_texto }}"
                                </blockquote>
                                <div class="flex items-center gap-4 pt-6 border-t border-white/5 mt-auto">
                                    <div class="shrink-0">
                                        @if($dep->avatar_path)
                                            <img src="{{ asset('storage/' . $dep->avatar_path) }}" alt="{{ $dep->nome_autor }}" class="w-12 h-12 rounded-xl object-cover border border-white/10 shadow-lg">
                                        @else
                                            <div class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center font-black text-xs text-vapt-accent">
                                                {{ substr($dep->nome_autor, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-white text-sm leading-none mb-1">{{ $dep->nome_autor }}</h5>
                                        <p class="text-[10px] text-vapt-accent font-black uppercase tracking-widest">
                                            {{ $dep->cargo_autor ?? ($dep->empresa_autor ?: 'Usuário vaptCRM') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Final CTA --}}
        <section class="py-48 text-center relative overflow-hidden">
            <div class="section-container relative z-10">
                <h2 class="font-display text-5xl md:text-7xl font-black mb-10 text-white">Escalabilidade ao seu alcance.</h2>
                <p class="text-xl text-slate-400 mb-16 max-w-2xl mx-auto">Junte-se a centenas de gráficas que transformaram sua gestão com o vaptCRM.</p>
                <a href="#planos" class="btn-vapt">Iniciar Teste Grátis</a>
                <p class="text-xs text-slate-500 mt-8 font-bold">Renovação automática após 15 dias &bull; Requer cartão</p>
            </div>
        </section>

        {{-- Footer Credit --}}
        <div class="py-10 text-center text-[10px] text-slate-600 font-bold uppercase tracking-[0.2em] border-t border-white/5">
            Powered by Abimael Borges &bull; vaptCRM &copy; {{ date('Y') }}
        </div>
    </div>

</x-layouts.publico>
