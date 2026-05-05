<x-layouts.publico titulo="VaptCRM — Sistema de Gestão para Gráficas e Papelarias" :fullWidth="true" :hideNav="true" :hideFooter="true" :showSaasHeader="true">

    {{-- 
        Autoria: Abimael Borges
        https://abimaelborges.adv.br
        Refatorado em: 05/2026
    --}}

    <style>
        /* ---- VaptCRM Landing Page — Tema Claro ---- */
        .lp { overflow-x: hidden; }

        .lp-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Hero gradient background */
        .lp-hero-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #0f172a 100%);
        }

        /* Feature cards */
        .lp-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.75rem;
            transition: all 0.25s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .lp-card:hover {
            border-color: #FF7A00;
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(255,122,0,0.12);
        }

        /* Plan card */
        .lp-plan {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .lp-plan.popular {
            border-color: #FF7A00;
            box-shadow: 0 0 0 4px rgba(255,122,0,0.08), 0 8px 32px rgba(255,122,0,0.15);
            position: relative;
        }
        .lp-plan.popular::before {
            content: 'MAIS POPULAR';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #FF7A00;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.1em;
            padding: 3px 14px;
            border-radius: 20px;
        }

        /* FAQ Accordion */
        .faq-item details > summary {
            list-style: none;
            cursor: pointer;
        }
        .faq-item details > summary::-webkit-details-marker { display: none; }
        .faq-item details[open] .faq-icon { transform: rotate(45deg); }
        .faq-icon { transition: transform 0.2s ease; }

        /* Animate on scroll (simple) */
        @keyframes lp-fade-up {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .lp-animate { animation: lp-fade-up 0.7s ease-out forwards; }
        .lp-delay-1 { animation-delay: 0.1s; }
        .lp-delay-2 { animation-delay: 0.25s; }
        .lp-delay-3 { animation-delay: 0.4s; }
    </style>

    <div class="lp">

        {{-- ============================================================ --}}
        {{-- HERO SECTION                                                  --}}
        {{-- ============================================================ --}}
        <section class="lp-hero-bg text-white relative overflow-hidden">
            {{-- decorative blobs --}}
            <div class="absolute top-0 right-0 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl -translate-y-1/3 translate-x-1/3 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-400/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3 pointer-events-none"></div>

            <div class="lp-container relative z-10 text-center py-24 md:py-36">
                <span class="inline-block bg-orange-500/20 border border-orange-400/30 text-orange-300 text-xs font-bold tracking-widest uppercase rounded-full px-5 py-2 mb-8 lp-animate">
                    ⚡ 15 dias grátis para começar
                </span>

                <h1 class="text-4xl md:text-6xl font-black leading-tight mb-6 lp-animate lp-delay-1 text-white">
                    Sistema de Gestão para<br>
                    <span class="text-orange-400">Gráficas e Papelarias</span>
                </h1>

                <p class="text-lg md:text-xl text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed lp-animate lp-delay-2">
                    Centralize pedidos, produção, financeiro, catálogo online e comunicação com seus clientes em uma única plataforma feita para quem trabalha com impressão e comunicação visual.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 lp-animate lp-delay-3">
                    <a href="#planos"
                       class="w-full sm:w-auto bg-orange-500 hover:bg-orange-600 text-white font-bold text-lg px-8 py-4 rounded-xl shadow-lg hover:shadow-orange-500/30 transition-all">
                        Começar agora
                    </a>
                    <a href="https://app.graficavaptvupt.com.br/entrar"
                       class="w-full sm:w-auto bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-lg px-8 py-4 rounded-xl transition-all">
                        Já tenho conta →
                    </a>
                </div>

                <p class="text-xs text-slate-400 mt-6">Sem fidelidade &bull; Cancele quando quiser &bull; Sem instalar nada</p>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- TRUST STRIP                                                   --}}
        {{-- ============================================================ --}}
        <section class="bg-white border-b border-slate-100 py-5">
            <div class="lp-container">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 text-center text-xs font-semibold text-slate-500">
                    <div class="flex flex-col items-center gap-1.5">
                        <span class="text-lg">🏪</span><span>Multi-loja</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5">
                        <span class="text-lg">📱</span><span>100% online</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5">
                        <span class="text-lg">💬</span><span>WhatsApp nativo</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5">
                        <span class="text-lg">📦</span><span>Kanban de produção</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5">
                        <span class="text-lg">💰</span><span>Financeiro integrado</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5">
                        <span class="text-lg">🛒</span><span>Catálogo + PDV</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- FUNCIONALIDADES / FEATURE CARDS                              --}}
        {{-- ============================================================ --}}
        <section class="py-20 bg-slate-50">
            <div class="lp-container">
                <div class="text-center mb-14">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-3">
                        Tudo o que sua gráfica precisa
                    </h2>
                    <p class="text-slate-500 text-lg max-w-2xl mx-auto">
                        Para vender, produzir e atender melhor — em um só lugar.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">

                    {{-- Card 1: Pedidos --}}
                    <div class="lp-card">
                        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-1.5">Pedidos e Orçamentos</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Cadastre, gerencie e acompanhe todos os pedidos com status em tempo real. Envie orçamentos direto pelo WhatsApp.</p>
                    </div>

                    {{-- Card 2: Kanban --}}
                    <div class="lp-card">
                        <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-1.5">Produção com Kanban</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Controle o chão de fábrica com quadro visual por etapas. Saiba exatamente onde está cada trabalho em produção.</p>
                    </div>

                    {{-- Card 3: Catálogo --}}
                    <div class="lp-card">
                        <div class="w-11 h-11 bg-emerald-50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-1.5">Catálogo Online</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Publique seus produtos com link próprio. Clientes fazem pedidos pelo celular, sem necessidade de ligação.</p>
                    </div>

                    {{-- Card 4: PDV --}}
                    <div class="lp-card">
                        <div class="w-11 h-11 bg-orange-50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-1.5">PDV — Frente de Caixa</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Registre vendas rápidas no balcão com geração de recibo, controle de estoque e baixa financeira automática.</p>
                    </div>

                    {{-- Card 5: Financeiro --}}
                    <div class="lp-card">
                        <div class="w-11 h-11 bg-teal-50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-1.5">Financeiro Completo</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Controle contas a pagar e receber, fluxo de caixa e saúde financeira do negócio com relatórios simples.</p>
                    </div>

                    {{-- Card 6: Estoque --}}
                    <div class="lp-card">
                        <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-1.5">Estoque e Insumos</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Monitore o estoque de insumos, papéis e materiais. Receba alertas de estoque baixo e evite paradas na produção.</p>
                    </div>

                    {{-- Card 7: WhatsApp --}}
                    <div class="lp-card border-emerald-200 bg-emerald-50/30">
                        <div class="w-11 h-11 bg-emerald-500/15 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-1.5">WhatsApp Integrado <span class="text-xs font-bold text-emerald-600 bg-emerald-100 px-1.5 py-0.5 rounded ml-1">Diferencial</span></h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Envie orçamentos, confirmações e atualizações de pedido pelo WhatsApp. Suporte a mensagens manuais e API oficial.</p>
                    </div>

                    {{-- Card 8: Multi-loja --}}
                    <div class="lp-card">
                        <div class="w-11 h-11 bg-indigo-50 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-1.5">Multi-loja e Planos SaaS</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Cada loja tem seu próprio painel, catálogo, dados e link. Escalável para redes, franquias e operações maiores.</p>
                    </div>

                </div>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- BENEFÍCIOS / DIFERENCIAIS                                    --}}
        {{-- ============================================================ --}}
        <section class="py-20 bg-white">
            <div class="lp-container">
                <div class="grid md:grid-cols-2 gap-14 items-center">
                    {{-- Texto --}}
                    <div>
                        <span class="text-orange-500 font-bold text-xs uppercase tracking-widest">Por que escolher o VaptCRM?</span>
                        <h2 class="text-3xl md:text-4xl font-black text-slate-900 mt-3 mb-8">
                            Mais organização,<br>menos retrabalho.
                        </h2>
                        <ul class="space-y-5">
                            <li class="flex items-start gap-3">
                                <span class="mt-1 w-5 h-5 rounded-full bg-orange-500 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <div>
                                    <strong class="text-slate-800">Menos retrabalho no atendimento</strong>
                                    <p class="text-sm text-slate-500 mt-0.5">Histórico de pedidos, cliente e arquivos centralizados em um único lugar.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 w-5 h-5 rounded-full bg-orange-500 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <div>
                                    <strong class="text-slate-800">Produção mais organizada</strong>
                                    <p class="text-sm text-slate-500 mt-0.5">Kanban visual para cada etapa, da arte-final à entrega ao cliente.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 w-5 h-5 rounded-full bg-orange-500 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <div>
                                    <strong class="text-slate-800">Comunicação mais rápida</strong>
                                    <p class="text-sm text-slate-500 mt-0.5">Orçamentos e confirmações enviados pelo WhatsApp em segundos.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 w-5 h-5 rounded-full bg-orange-500 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <div>
                                    <strong class="text-slate-800">Mais controle financeiro</strong>
                                    <p class="text-sm text-slate-500 mt-0.5">Saiba exatamente quanto entrou, quanto saiu e quanto há para receber.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 w-5 h-5 rounded-full bg-orange-500 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <div>
                                    <strong class="text-slate-800">Implantação simples e rápida</strong>
                                    <p class="text-sm text-slate-500 mt-0.5">Sem instalação. Acesse pelo navegador, configure e comece a usar em horas.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 w-5 h-5 rounded-full bg-orange-500 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <div>
                                    <strong class="text-slate-800">Mais profissionalismo na gestão</strong>
                                    <p class="text-sm text-slate-500 mt-0.5">Apresente orçamentos, catálogo e pedidos com a identidade visual da sua marca.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    {{-- Visual --}}
                    <div class="bg-slate-50 rounded-3xl p-10 text-center border border-slate-100">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 text-left">
                                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">Pedidos hoje</p>
                                <p class="text-3xl font-black text-slate-900">48</p>
                                <p class="text-xs text-emerald-500 font-bold mt-1">↑ 12% vs ontem</p>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 text-left">
                                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">Em produção</p>
                                <p class="text-3xl font-black text-slate-900">17</p>
                                <p class="text-xs text-blue-500 font-bold mt-1">3 prontos hoje</p>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 text-left">
                                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">A receber</p>
                                <p class="text-2xl font-black text-slate-900">R$ 4.280</p>
                                <p class="text-xs text-slate-400 font-semibold mt-1">este mês</p>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 text-left">
                                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1">Clientes ativos</p>
                                <p class="text-3xl font-black text-slate-900">213</p>
                                <p class="text-xs text-orange-500 font-bold mt-1">+8 esta semana</p>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-5">Dados fictícios para ilustração</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- PLANOS                                                        --}}
        {{-- ============================================================ --}}
        <section id="planos" class="py-20 bg-slate-50">
            <div class="lp-container">
                <div class="text-center mb-14">
                    <span class="text-orange-500 font-bold text-xs uppercase tracking-widest">Planos e Preços</span>
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mt-3 mb-3">
                        Comece com 15 dias grátis
                    </h2>
                    <p class="text-slate-500 max-w-xl mx-auto">
                        Escolha o plano ideal para o tamanho do seu negócio. Cancele quando quiser, sem multa.
                    </p>
                </div>

                @php
                $recursoLabels = [
                    'central_pedidos'           => 'Central de pedidos',
                    'gestao_clientes'           => 'Gestão de clientes',
                    'bi_basico'                 => 'Relatórios básicos',
                    'bi_avancado'               => 'BI avançado',
                    'suporte_basico'            => 'Suporte básico',
                    'suporte_prioritario'       => 'Suporte prioritário',
                    'multiempresa_opcional'     => 'Multi-empresa',
                    'whatsapp_api_oficial_beta' => 'WhatsApp API oficial (beta)',
                    'whatsapp_api_oficial'      => 'WhatsApp API oficial',
                ];
                @endphp
                <div class="grid sm:grid-cols-2 @if($planos->count() >= 4) lg:grid-cols-4 @elseif($planos->count() === 3) lg:grid-cols-3 @else lg:grid-cols-2 @endif gap-6">
                    @foreach($planos as $plano)
                    @php
                        $isPopular  = $plano->slug === 'prata' || ($loop->iteration === 2 && $planos->count() >= 3);
                        $isDiamante = $plano->slug === 'diamante';
                    @endphp
                    <div class="lp-plan {{ $isPopular ? 'popular' : '' }} {{ $isDiamante ? 'relative' : '' }}">
                        <div class="mb-6">
                            @if($isDiamante)
                                <span class="inline-block bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-widest rounded-full px-3 py-1 mb-4">
                                    ✦ Topo da oferta
                                </span>
                            @else
                                <span class="inline-block bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest rounded-full px-3 py-1 mb-4">
                                    {{ $plano->trial_days ?? 15 }} dias grátis
                                </span>
                            @endif
                            <h3 class="text-xl font-black text-slate-900 mb-1">{{ $plano->nome }}</h3>
                            <div class="flex items-end gap-1 mt-3">
                                <span class="text-sm font-bold text-slate-400 mb-1.5">R$</span>
                                <span class="text-4xl font-black {{ $isDiamante ? 'text-amber-600' : 'text-slate-900' }}">{{ number_format($plano->preco_mensal, 0, ',', '.') }}</span>
                                <span class="text-slate-400 text-sm mb-1.5">/mês</span>
                            </div>
                        </div>

                        <ul class="space-y-3 mb-8 flex-1 text-sm">
                            <li class="flex items-center gap-2 text-slate-600">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @if($plano->limite_produtos) Até {{ $plano->limite_produtos }} produtos @else Produtos ilimitados @endif
                            </li>
                            <li class="flex items-center gap-2 text-slate-600">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @if($plano->limite_funcionarios) Até {{ $plano->limite_funcionarios }} usuários @else Usuários ilimitados @endif
                            </li>
                            @foreach($plano->recursos_premium ?? [] as $key => $valor)
                                @if($valor)
                                <li class="flex items-center gap-2 {{ ($isDiamante && $key === 'whatsapp_api_oficial_beta') ? 'text-amber-700 font-semibold' : 'text-slate-600' }}">
                                    @if($isDiamante && $key === 'whatsapp_api_oficial_beta')
                                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                    {{ $recursoLabels[$key] ?? ucfirst(str_replace('_', ' ', $key)) }}
                                </li>
                                @endif
                            @endforeach
                        </ul>

                        <a href="{{ route('onboarding.start', $plano->slug) }}"
                           class="block w-full text-center py-3 rounded-xl font-bold text-sm transition-all
                                  {{ $isDiamante
                                     ? 'bg-amber-500 hover:bg-amber-600 text-white shadow-md hover:shadow-amber-500/30'
                                     : ($isPopular
                                        ? 'bg-orange-500 hover:bg-orange-600 text-white shadow-md hover:shadow-orange-500/30'
                                        : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200') }}">
                            Começar grátis
                        </a>
                    </div>
                    @endforeach
                </div>

                <p class="text-center text-xs text-slate-400 mt-8">
                    Necessário vincular cartão de crédito para renovação automática após o período trial.
                    Cancele a qualquer momento.
                </p>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- FAQ                                                           --}}
        {{-- ============================================================ --}}
        <section class="py-20 bg-white">
            <div class="lp-container max-w-3xl">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-3">Dúvidas Frequentes</h2>
                    <p class="text-slate-500">Respostas rápidas para as perguntas mais comuns.</p>
                </div>

                <div class="space-y-3 faq-item">

                    <details class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden group">
                        <summary class="flex items-center justify-between px-6 py-5 font-semibold text-slate-800 cursor-pointer select-none">
                            Como funciona a contratação?
                            <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </summary>
                        <div class="px-6 pb-5 text-slate-600 text-sm leading-relaxed">
                            Você escolhe o plano, cria sua conta e já tem acesso completo ao sistema durante o período de teste. Após os dias gratuitos, a cobrança é feita automaticamente pelo cartão cadastrado.
                        </div>
                    </details>

                    <details class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                        <summary class="flex items-center justify-between px-6 py-5 font-semibold text-slate-800 cursor-pointer select-none">
                            Existe teste grátis?
                            <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </summary>
                        <div class="px-6 pb-5 text-slate-600 text-sm leading-relaxed">
                            Sim. Todo plano inclui um período de teste gratuito. Você pode explorar todas as funcionalidades antes de ser cobrado. Basta cadastrar um cartão para ativar a conta.
                        </div>
                    </details>

                    <details class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                        <summary class="flex items-center justify-between px-6 py-5 font-semibold text-slate-800 cursor-pointer select-none">
                            Posso cancelar quando quiser?
                            <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </summary>
                        <div class="px-6 pb-5 text-slate-600 text-sm leading-relaxed">
                            Sim. Não há fidelidade. Você pode cancelar a qualquer momento diretamente pelo painel do sistema, sem burocracia.
                        </div>
                    </details>

                    <details class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                        <summary class="flex items-center justify-between px-6 py-5 font-semibold text-slate-800 cursor-pointer select-none">
                            O sistema funciona para gráfica rápida e papelaria?
                            <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </summary>
                        <div class="px-6 pb-5 text-slate-600 text-sm leading-relaxed">
                            Sim. O VaptCRM foi construído especificamente para gráficas rápidas, serviços de comunicação visual e papelarias. Pedidos, catálogo, PDV, produção e financeiro são funcionalidades nativas.
                        </div>
                    </details>

                    <details class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                        <summary class="flex items-center justify-between px-6 py-5 font-semibold text-slate-800 cursor-pointer select-none">
                            Posso personalizar com minha logo e cores?
                            <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </summary>
                        <div class="px-6 pb-5 text-slate-600 text-sm leading-relaxed">
                            Sim. Você pode configurar a identidade visual da sua loja: logo, cores, nome da empresa e imagem de capa. O catálogo público reflete a identidade da sua marca.
                        </div>
                    </details>

                    <details class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                        <summary class="flex items-center justify-between px-6 py-5 font-semibold text-slate-800 cursor-pointer select-none">
                            O sistema funciona no celular?
                            <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </summary>
                        <div class="px-6 pb-5 text-slate-600 text-sm leading-relaxed">
                            Sim. O VaptCRM é totalmente responsivo e funciona bem tanto no computador quanto no celular, sem necessidade de instalar nenhum aplicativo.
                        </div>
                    </details>

                    <details class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                        <summary class="flex items-center justify-between px-6 py-5 font-semibold text-slate-800 cursor-pointer select-none">
                            O sistema possui integração com WhatsApp?
                            <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </summary>
                        <div class="px-6 pb-5 text-slate-600 text-sm leading-relaxed">
                            Sim. O VaptCRM suporta envio de orçamentos, confirmações e atualizações de pedido via WhatsApp. Disponível em modo manual (link direto) e via API oficial (conforme plano).
                        </div>
                    </details>

                    <details class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                        <summary class="flex items-center justify-between px-6 py-5 font-semibold text-slate-800 cursor-pointer select-none">
                            Preciso instalar alguma coisa?
                            <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </summary>
                        <div class="px-6 pb-5 text-slate-600 text-sm leading-relaxed">
                            Não. O VaptCRM é 100% na nuvem. Basta acessar pelo navegador — em qualquer dispositivo, de qualquer lugar.
                        </div>
                    </details>

                    <details class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                        <summary class="flex items-center justify-between px-6 py-5 font-semibold text-slate-800 cursor-pointer select-none">
                            O VaptCRM tem suporte ou treinamento?
                            <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </summary>
                        <div class="px-6 pb-5 text-slate-600 text-sm leading-relaxed">
                            Sim. Todo assinante tem acesso a materiais de treinamento e suporte para implantação. A equipe está disponível para ajudar na configuração inicial.
                        </div>
                    </details>

                    <details class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                        <summary class="flex items-center justify-between px-6 py-5 font-semibold text-slate-800 cursor-pointer select-none">
                            Como funciona a assinatura?
                            <svg class="faq-icon w-5 h-5 text-slate-400 shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </summary>
                        <div class="px-6 pb-5 text-slate-600 text-sm leading-relaxed">
                            A assinatura é mensal e recorrente, cobrada automaticamente via cartão de crédito. Você pode mudar de plano ou cancelar a qualquer momento pelo painel do sistema.
                        </div>
                    </details>

                </div>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- CTA FINAL                                                     --}}
        {{-- ============================================================ --}}
        <section class="py-20 bg-slate-900 text-white text-center">
            <div class="lp-container">
                <h2 class="text-3xl md:text-5xl font-black mb-4 text-white">
                    Pronto para organizar sua gráfica?
                </h2>
                <p class="text-slate-300 text-lg max-w-xl mx-auto mb-10">
                    Comece hoje mesmo com 15 dias grátis. Sem compromisso, sem instalação.
                </p>
                <a href="#planos"
                   class="inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold text-lg px-10 py-4 rounded-xl shadow-lg hover:shadow-orange-500/30 transition-all">
                    Começar agora gratuitamente
                </a>
                <p class="text-slate-500 text-xs mt-6">Cancele quando quiser &bull; Sem fidelidade</p>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- RODAPÉ INSTITUCIONAL                                          --}}
        {{-- ============================================================ --}}
        <footer class="bg-slate-950 text-white py-14">
            <div class="lp-container text-center">
                <img src="{{ asset('img/logo_horizontal.png') }}" alt="VaptCRM" class="h-9 w-auto mx-auto mb-5 opacity-90">

                <p class="text-slate-300 font-semibold mb-1">
                    Sistema de Gestão para Gráficas e Papelarias
                </p>
                <p class="text-slate-500 text-sm max-w-md mx-auto">
                    Plataforma SaaS para vendas, produção, financeiro e comunicação com o cliente.
                </p>

                <div class="flex items-center justify-center gap-6 mt-8 text-sm text-slate-400">
                    <a href="{{ route('onboarding.start') }}" class="hover:text-white transition-colors">Criar conta</a>
                    <a href="https://app.graficavaptvupt.com.br/entrar" class="hover:text-white transition-colors">Entrar</a>
                </div>

                <div class="border-t border-slate-800 mt-10 pt-8 text-xs text-slate-600">
                    VaptCRM &copy; {{ date('Y') }} &bull; Versão {{ config('app.version', '1.0') }}
                </div>
            </div>
        </footer>

    </div>{{-- /.lp --}}

</x-layouts.publico>