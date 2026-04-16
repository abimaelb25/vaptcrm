<x-layouts.publico titulo="{{ $configSite['empresa_nome'] ?? 'Gráfica' }} - {{ $configSite['loja_subtitulo'] ?? 'Catálogo' }}">

    <!-- HERO SECTION / BANNERS -->
    @if($banners->count() > 0)
        <!-- Simple CSS-based Slider (or just show the first active banner if no JS for slider) -->
        <section class="relative mb-12 overflow-hidden rounded-3xl bg-slate-900 aspect-[21/9] md:aspect-[21/7] shadow-xl group">
            @foreach($banners as $index => $banner)
                <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out {{ $index === 0 ? 'opacity-100 relative z-10' : 'opacity-0 z-0 hidden' }}" id="banner-{{ $index }}">
                    <img src="{{ asset('storage/' . $banner->imagem) }}" alt="{{ $banner->titulo }}" class="w-full h-full object-cover opacity-60 mix-blend-overlay">
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-900/90 to-transparent"></div>
                    <div class="absolute inset-0 flex items-center p-8 md:p-16">
                        <div class="max-w-xl text-white">
                            <h2 class="text-4xl md:text-5xl font-black tracking-tight mb-4 leading-tight">{{ $banner->titulo }}</h2>
                            @if($banner->subtitulo)
                                <p class="text-lg md:text-xl text-slate-300 mb-8 font-medium">{{ $banner->subtitulo }}</p>
                            @endif
                            @if($banner->link)
                                <a href="{{ $banner->link }}" class="inline-block bg-brand-primary text-white font-bold py-3 px-8 rounded-full shadow-lg hover:shadow-brand-primary/50 transition-all hover:-translate-y-1">Aproveitar Oferta</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    @else
        <!-- Fallback: imagem de capa ou gradiente -->
        <section class="mb-12 rounded-3xl bg-linear-to-br from-brand-secondary to-slate-900 p-12 text-white shadow-xl text-center relative overflow-hidden">
            @if(!empty($configSite['aparencia_capa']))
                <img src="{{ asset('storage/' . $configSite['aparencia_capa']) }}" alt="Capa" class="absolute inset-0 w-full h-full object-cover opacity-30">
            @endif
            <div class="relative z-10">
                <h1 class="text-4xl md:text-5xl font-black tracking-tight mb-4">{{ $configSite['loja_subtitulo'] ?? 'Soluções gráficas de alta performance' }}</h1>
                <p class="mt-4 text-xl text-slate-300 max-w-2xl mx-auto">{{ $configSite['aparencia_rodape_texto'] ?? 'Sua marca com a apresentação perfeita. Prazos imbatíveis e qualidade garantida.' }}</p>
                <a href="{{ route('site.catalogo') }}" class="mt-8 inline-block bg-brand-primary text-white font-bold py-3 px-8 rounded-full shadow-lg hover:bg-white hover:text-brand-primary transition-colors">Solicitar Orçamento</a>
            </div>
        </section>
    @endif

    <!-- TRUST BADGES -->
    <section class="mb-16 border-y border-slate-200 bg-white shadow-sm py-8 -mx-4 px-4 sm:mx-0 sm:rounded-2xl">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-slate-100">
            <div class="px-4">
                <div class="text-4xl mb-2">⚡</div>
                <h4 class="font-bold text-slate-800 text-sm">Produção Express</h4>
                <p class="text-xs text-slate-500 mt-1">Materiais prontos em até 24h</p>
            </div>
            <div class="px-4">
                <div class="text-4xl mb-2">💎</div>
                <h4 class="font-bold text-slate-800 text-sm">Qualidade Premium</h4>
                <p class="text-xs text-slate-500 mt-1">Acabamentos rigorosos e duradouros</p>
            </div>
            <div class="px-4">
                <div class="text-4xl mb-2">🔒</div>
                <h4 class="font-bold text-slate-800 text-sm">Compra Segura</h4>
                <p class="text-xs text-slate-500 mt-1">Pagamento via PIX Integrado</p>
            </div>
            <div class="px-4">
                <div class="text-4xl mb-2">🎯</div>
                <h4 class="font-bold text-slate-800 text-sm">Entrega Garantida</h4>
                <p class="text-xs text-slate-500 mt-1">Acompanhamento de pedido em tempo real</p>
            </div>
        </div>
    </section>

    <!-- PRODUTOS EM DESTAQUE -->
    <section class="mb-20">
        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-brand-secondary tracking-tight">Produtos em Destaque</h2>
                <p class="text-slate-500 mt-1">Nossos materiais mais vendidos com preço especial.</p>
            </div>
            <a href="{{ route('site.catalogo') }}" class="font-bold text-brand-primary hover:text-brand-secondary transition-colors underline decoration-2 underline-offset-4">Ver todo o catálogo &plus;</a>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($destaques as $produto)
                <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white hover:border-brand-primary/30 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="aspect-square relative overflow-hidden bg-slate-50">
                        @if($produto->imagem_principal)
                            <img src="{{ asset('storage/' . $produto->imagem_principal) }}" alt="{{ $produto->nome }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-4xl text-slate-300">📦</div>
                        @endif
                        <div class="absolute top-3 right-3 bg-brand-primary text-white text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full shadow-md backdrop-blur-sm z-10">
                            Mais Vendido
                        </div>
                    </div>
                    <div class="p-5 flex-1 flex flex-col">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ $produto->categoria }}</p>
                        <h3 class="text-lg font-bold text-slate-800 leading-snug">{{ $produto->nome }}</h3>
                        <p class="mt-2 text-sm text-slate-500 line-clamp-2 flex-1">{{ $produto->descricao_curta }}</p>
                        
                        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                            @if($produto->preco_base)
                                <span class="text-lg font-black text-brand-primary">R$ {{ number_format((float)$produto->preco_base, 2, ',', '.') }}</span>
                            @else
                                <span class="text-sm font-bold text-slate-500">Sob Consulta</span>
                            @endif
                            <a href="{{ route('site.produto', $produto) }}" class="h-10 w-10 bg-slate-100 text-slate-700 rounded-xl flex items-center justify-center hover:bg-brand-primary hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-slate-500 col-span-full">Nenhum produto listado como destaque no momento.</p>
            @endforelse
        </div>
    </section>

    <!-- DEPOIMENTOS -->
    @if($depoimentos->count() > 0)
    <section class="mb-20 bg-brand-secondary -mx-4 sm:rounded-3xl px-6 py-16 text-white md:px-16 overflow-hidden relative shadow-2xl">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
        
        <div class="relative z-10">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-black tracking-tight mb-4">O que nossos clientes dizem</h2>
                <p class="text-slate-400 max-w-2xl mx-auto">Construímos parcerias de longo prazo entregando resultados surpreendentes. Veja quem confia no Vapt Vupt.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($depoimentos as $dep)
                    <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-8 hover:bg-white/20 transition-colors">
                        <div class="text-brand-primary mb-4">
                            <svg class="h-8 w-8 opacity-70" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4v10h-10z"/></svg>
                        </div>
                        <p class="text-slate-200 text-sm leading-relaxed mb-6 italic">"{{ $dep->texto }}"</p>
                        <div class="flex items-center gap-4">
                            @if($dep->avatar)
                                <img src="{{ asset('storage/' . $dep->avatar) }}" class="w-12 h-12 rounded-full border-2 border-brand-primary object-cover" alt="Foto de {{ $dep->cliente_nome }}">
                            @else
                                <div class="w-12 h-12 rounded-full border-2 border-slate-500 bg-slate-800 flex items-center justify-center font-black text-slate-300">
                                    {{ substr($dep->cliente_nome, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h5 class="font-black text-white">{{ $dep->cliente_nome }}</h5>
                                <p class="text-xs text-brand-primary font-bold">{{ $dep->cliente_empresa ?? 'Cliente Verificado' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

</x-layouts.app>

