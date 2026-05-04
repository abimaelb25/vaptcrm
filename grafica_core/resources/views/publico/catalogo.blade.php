{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-22 02:00 -03:00
--}}
<x-layouts.publico titulo="{{ $configSite['empresa_nome'] ?? 'Gráfica' }} - Catálogo">
    
    {{-- Banner de Capa (Hero Image) --}}
    @if(!empty($configSite['aparencia_capa']))
        <section class="max-w-7xl mx-auto mb-6 sm:mb-10 mt-2 relative w-full h-24 sm:h-56 md:h-72 lg:h-80 rounded-2xl sm:rounded-[2rem] overflow-hidden shadow-lg border border-slate-100 group px-4 sm:px-0">
            <img src="{{ asset('storage/' . $configSite['aparencia_capa']) }}" 
                 alt="{{ $configSite['empresa_nome'] ?? 'Capa da Loja' }}" 
                 class="w-full h-full object-cover object-center transition-transform duration-1000 group-hover:scale-[1.02] rounded-2xl sm:rounded-none">
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-60"></div>
        </section>
    @endif

    {{-- Menu de Filtros Mobile (Hamburger) --}}
    <div class="lg:hidden flex items-center justify-between gap-4 mb-6 px-4">
        <button onclick="toggleFiltrosMobile()" class="flex-1 flex items-center justify-center gap-3 bg-white border border-slate-200 p-3 rounded-xl shadow-sm text-sm font-black text-slate-700 active:bg-slate-50 transition-colors">
            <x-icon name="bars-3-bottom-left" class="w-5 h-5 text-brand-primary" />
            CATEGORIAS & BUSCA
        </button>
        
        <div class="flex bg-slate-100 p-1 rounded-xl">
            <button onclick="alterarVista('grid')" id="m-btn-grid" class="p-2 rounded-lg transition-all m-vista-btn catalog-mobile-view-btn active-view">
                <x-icon name="squares-2x2" class="w-5 h-5" />
            </button>
            <button onclick="alterarVista('lista')" id="m-btn-lista" class="p-2 rounded-lg transition-all m-vista-btn catalog-mobile-view-btn">
                <x-icon name="list-bullet" class="w-5 h-5" />
            </button>
        </div>
    </div>

    {{-- Overlay / Drawer de Filtros Mobile --}}
    <div id="drawer-filtros" class="fixed inset-0 z-[100] hidden overflow-hidden lg:hidden" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="toggleFiltrosMobile()"></div>
        <div class="catalog-slide-in-left absolute inset-y-0 left-0 flex h-full w-[80%] max-w-xs flex-col bg-white shadow-2xl">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <span class="font-black text-brand-secondary text-sm uppercase tracking-widest">Opções do Catálogo</span>
                <button onclick="toggleFiltrosMobile()" class="p-2 text-slate-400">
                    <x-icon name="x-mark" class="w-6 h-6" />
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6 space-y-8">
                {{-- Busca Rápida Mobile --}}
                <div class="space-y-4">
                    <h3 class="font-black text-slate-800 text-[10px] uppercase tracking-widest flex items-center gap-2">
                        <x-icon name="magnifying-glass" class="w-4 h-4 text-brand-primary" /> O que você busca?
                    </h3>
                    <form action="{{ route('site.catalogo') }}" method="GET" class="relative">
                        <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Ex: Cartões de Visita" 
                               class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-4 pr-10 text-sm focus:ring-brand-primary focus:border-brand-primary transition-all">
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-brand-primary">
                            <x-icon name="magnifying-glass" class="w-5 h-5" />
                        </button>
                    </form>
                </div>

                {{-- Navegação de Categorias Mobile --}}
                <div class="space-y-4">
                    <h3 class="font-black text-slate-800 text-[10px] uppercase tracking-widest">Categorias</h3>
                    <nav class="space-y-1">
                        <a href="{{ \App\Support\PublicUrlHelper::catalogo() }}" class="flex items-center justify-between px-4 py-3 rounded-xl transition-all {{ !isset($categoriaAtiva) || !$categoriaAtiva ? 'bg-brand-secondary text-white' : 'text-slate-600 active:bg-slate-50 border border-transparent' }}">
                            <span class="font-bold text-sm tracking-tight">🎒 Todos os Produtos</span>
                        </a>
                        @foreach($categorias as $cat)
                            @if($cat->total_publico > 0)
                                <a href="{{ \App\Support\PublicUrlHelper::categoria($cat) }}" class="flex items-center justify-between px-4 py-3 rounded-xl transition-all {{ isset($categoriaAtiva) && $categoriaAtiva?->id === $cat->id ? 'bg-brand-secondary text-white' : 'text-slate-600 active:bg-slate-50 border border-transparent' }}">
                                    <span class="font-bold text-sm tracking-tight">{{ $cat->nome }}</span>
                                    <span class="text-[10px] font-black {{ isset($categoriaAtiva) && $categoriaAtiva?->id === $cat->id ? 'bg-white/20' : 'bg-slate-100 text-slate-400' }} rounded-full px-2 py-0.5">
                                        {{ $cat->total_publico }}
                                    </span>
                                </a>
                            @endif
                        @endforeach
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-10">

        <!-- SIDEBAR: Apenas Desktop -->
        <aside class="hidden lg:block w-72 shrink-0">
            <div class="sticky top-28 space-y-8">
                
                {{-- Busca Rápida Desktop --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
                    <h3 class="font-black text-slate-800 text-xs uppercase tracking-widest mb-4 flex items-center gap-2">
                        <x-icon name="magnifying-glass" class="w-4 h-4 text-brand-primary" /> Buscar
                    </h3>
                    <form action="{{ route('site.catalogo') }}" method="GET" class="relative">
                        <input type="text" name="busca" value="{{ request('busca') }}" placeholder="O que você precisa?" 
                               class="w-full rounded-xl border-slate-200 bg-slate-100 py-3 pl-4 pr-10 text-sm focus:ring-brand-primary focus:border-brand-primary transition-all">
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-primary">
                            <x-icon name="magnifying-glass" class="w-5 h-5" />
                        </button>
                    </form>
                </div>

                {{-- Navegação de Categorias Desktop --}}
                <div class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
                        <h3 class="font-black text-slate-800 text-xs uppercase tracking-widest">Categorias</h3>
                    </div>
                    <nav class="p-2">
                        <a href="{{ \App\Support\PublicUrlHelper::catalogo() }}" class="group flex items-center justify-between px-4 py-3 rounded-xl transition-all {{ !isset($categoriaAtiva) || !$categoriaAtiva ? 'bg-brand-secondary text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50 hover:pl-6' }}">
                            <span class="font-bold text-sm tracking-tight">🎒 Todos os Produtos</span>
                            <x-icon name="chevron-right" class="w-4 h-4 opacity-30" />
                        </a>
                        @foreach($categorias as $cat)
                            @if($cat->total_publico > 0)
                                <a href="{{ \App\Support\PublicUrlHelper::categoria($cat) }}" class="group flex items-center justify-between px-4 py-3 rounded-xl transition-all {{ isset($categoriaAtiva) && $categoriaAtiva?->id === $cat->id ? 'bg-brand-secondary text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50 hover:pl-6' }}">
                                    <span class="font-bold text-sm tracking-tight">{{ $cat->nome }}</span>
                                    <span class="text-[10px] font-black {{ isset($categoriaAtiva) && $categoriaAtiva?->id === $cat->id ? 'bg-white/20' : 'bg-slate-100 text-slate-400' }} rounded-full px-2 py-0.5">
                                        {{ $cat->total_publico }}
                                    </span>
                                </a>
                            @endif
                        @endforeach
                    </nav>
                </div>

                {{-- Trust Banner Sidebar Desktop --}}
                <div class="rounded-2xl bg-gradient-to-br from-brand-primary to-orange-600 p-6 text-white shadow-xl shadow-brand-primary/20">
                    <x-icon name="truck" class="w-10 h-10 mb-4 opacity-50" />
                    <h4 class="font-black text-md leading-tight mb-2">Entrega Nacional</h4>
                    <p class="text-[10px] text-white/80 font-bold uppercase tracking-wider">Produção Rápida</p>
                </div>
            </div>
        </aside>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="flex-1 min-w-0 px-4 sm:px-0">

            <!-- Toolbar Superior -->
            <div class="catalog-toolbar mb-5 sm:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-6">
                <div class="text-center sm:text-left">
                    <div class="flex items-center justify-center sm:justify-start gap-2 sm:gap-3 mb-0.5 sm:mb-1">
                        <span class="hidden sm:block w-8 h-1 bg-brand-primary rounded-full"></span>
                        <h1 class="text-lg sm:text-3xl font-black text-brand-secondary tracking-tighter">
                            {{ isset($categoriaAtiva) && $categoriaAtiva ? $categoriaAtiva->nome : 'Catálogo Geral' }}
                        </h1>
                    </div>
                    <p class="text-slate-400 text-[10px] sm:text-sm font-medium sm:ml-11">
                        {{ $produtos->total() }} impressões premium
                    </p>
                </div>

                <div class="flex items-center justify-center sm:justify-start gap-3">
                    {{-- Ordenação Compacta --}}
                    <div class="relative w-full sm:w-auto">
                        <select onchange="window.location.href = this.value" class="w-full appearance-none rounded-xl border-slate-200 bg-white py-2.5 pl-4 pr-10 text-xs font-black text-slate-700 shadow-sm focus:ring-brand-primary transition-all cursor-pointer">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'destaque']) }}" {{ request('sort') == 'destaque' ? 'selected' : '' }}>ORDENAR: RELEVÂNCIA</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'novidades']) }}" {{ request('sort') == 'novidades' ? 'selected' : '' }}>ORDENAR: NOVIDADES</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'preco_min']) }}" {{ request('sort') == 'preco_min' ? 'selected' : '' }}>ORDENAR: MENOR PREÇO</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'preco_max']) }}" {{ request('sort') == 'preco_max' ? 'selected' : '' }}>ORDENAR: MAIOR PREÇO</option>
                        </select>
                        <x-icon name="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-brand-primary pointer-events-none" />
                    </div>

                    {{-- Alternador Grid/Lista Desktop --}}
                    <div class="hidden sm:flex bg-slate-100 p-1 rounded-xl shrink-0">
                        <button onclick="alterarVista('grid')" id="btn-grid" class="p-2 rounded-lg transition-all vista-btn catalog-view-btn active-view">
                            <x-icon name="squares-2x2" class="w-4 h-4" />
                        </button>
                        <button onclick="alterarVista('lista')" id="btn-lista" class="p-2 rounded-lg transition-all vista-btn catalog-view-btn">
                            <x-icon name="list-bullet" class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            @if(isset($categoriaAtiva) && $categoriaAtiva && $categoriaAtiva->texto_destaque)
                <div class="mb-8 relative overflow-hidden rounded-2xl sm:rounded-3xl bg-slate-900 px-6 sm:px-8 py-6 sm:py-10 text-white">
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="bg-brand-primary w-1.5 h-1.5 rounded-full animate-pulse"></span>
                                <span class="text-brand-primary font-black uppercase tracking-[0.2em] text-[9px] block italic">Sobre esta categoria</span>
                            </div>
                            <div class="text-sm sm:text-lg font-medium leading-relaxed opacity-90 italic">
                                "{{ $categoriaAtiva->texto_destaque }}"
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Área de Listagem (Otimizada 2 Colunas Mobile) -->
            <div id="view-grid" class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-8">
                @forelse($produtos as $produto)
                    <x-product-card :produto="$produto" />
                @empty
                    <div class="col-span-full py-20 text-center">
                        <div class="bg-slate-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                            <x-icon name="magnifying-glass" class="w-10 h-10 text-slate-200" />
                        </div>
                        <h3 class="text-lg font-black text-slate-800">Nada encontrado</h3>
                        <p class="text-xs text-slate-400 mt-2">Tente outros filtros ou termos de busca.</p>
                        <a href="{{ \App\Support\PublicUrlHelper::catalogo() }}" class="mt-8 inline-block bg-brand-secondary text-white font-black px-6 py-2.5 rounded-xl text-xs uppercase tracking-widest">Ver Catálogo Completo</a>
                    </div>
                @endforelse
            </div>

            <!-- View Lista Mobile (Compacta) -->
            <div id="view-lista" class="hidden flex-col gap-3 sm:gap-4">
                @foreach($produtos as $produto)
                    <article class="group flex items-center gap-4 sm:gap-8 rounded-2xl border border-slate-100 bg-white p-3 sm:p-5 shadow-sm transition-all hover:shadow-xl hover:border-brand-primary/20">
                        <div class="h-20 w-20 sm:h-32 sm:w-32 shrink-0 overflow-hidden rounded-xl bg-slate-50 border border-slate-100">
                            @if($produto->imagem_principal)
                                <img src="{{ asset('storage/' . $produto->imagem_principal) }}" alt="{{ $produto->nome }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-2xl text-slate-100">📦</div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-[9px] font-black uppercase tracking-widest text-brand-primary block mb-0.5 opacity-60">{{ $produto->categoriaRel->nome ?? 'Produção' }}</span>
                            <h2 class="text-sm sm:text-xl font-black text-slate-800 line-clamp-1 leading-tight">{{ $produto->nome }}</h2>
                            <p class="text-[10px] sm:text-sm text-slate-400 mt-1 line-clamp-1 italic font-medium opacity-70">{{ $produto->descricao_curta }}</p>
                            <div class="flex items-center justify-between mt-2 sm:mt-4">
                                <div>
                                    <span class="block text-[8px] font-black text-slate-300 uppercase tracking-tighter">A partir de</span>
                                    <span class="text-sm sm:text-2xl font-black text-brand-secondary">R$ {{ number_format($produto->preco_base, 2, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('site.produto', $produto) }}" class="bg-brand-primary text-white p-2 sm:px-8 sm:py-3 rounded-lg sm:rounded-xl shadow-lg shadow-brand-primary/30 active:scale-95 transition-all">
                                    <x-icon name="arrow-right" class="w-4 h-4 sm:hidden" />
                                    <span class="hidden sm:inline font-black">Personalizar</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Paginação Customizada --}}
            <div class="mt-16 overflow-x-auto pb-4">
                {{ $produtos->links() }}
            </div>

        </main>
    </div>

    <script>
        const vistaKey = 'vapt_catalogo_pref';

        function toggleFiltrosMobile() {
            const drawer = document.getElementById('drawer-filtros');
            drawer.classList.toggle('hidden');
            if (!drawer.classList.contains('hidden')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        function alterarVista(tipo) {
            const grid  = document.getElementById('view-grid');
            const lista = document.getElementById('view-lista');
            const btnG  = document.getElementById('btn-grid');
            const btnL  = document.getElementById('btn-lista');
            const mBtnG = document.getElementById('m-btn-grid');
            const mBtnL = document.getElementById('m-btn-lista');

            if (tipo === 'grid') {
                grid.classList.remove('hidden');  grid.classList.add('grid');
                lista.classList.add('hidden');    lista.classList.remove('flex');
                btnG?.classList.add('active-view'); btnL?.classList.remove('active-view');
                mBtnG?.classList.add('active-view'); mBtnL?.classList.remove('active-view');
            } else {
                lista.classList.remove('hidden'); lista.classList.add('flex');
                grid.classList.add('hidden');     grid.classList.remove('grid');
                btnL?.classList.add('active-view'); btnG?.classList.remove('active-view');
                mBtnL?.classList.add('active-view'); mBtnG?.classList.remove('active-view');
            }
            localStorage.setItem(vistaKey, tipo);
        }

        const vistaSalva = localStorage.getItem(vistaKey) || '{{ $configSite["aparencia_layout_catalogo"] ?? "grid" }}';
        if (vistaSalva === 'lista') alterarVista('lista');
    </script>
</x-layouts.publico>


