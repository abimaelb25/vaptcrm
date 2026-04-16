{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-10
--}}
<x-layouts.publico titulo="{{ $configSite['empresa_nome'] ?? 'Gráfica' }} - Catálogo">
    
    {{-- Banner de Capa (Hero Image) --}}
    @if(!empty($configSite['aparencia_capa']))
        <section class="max-w-7xl mx-auto mb-10 mt-2 relative w-full h-32 sm:h-56 md:h-72 lg:h-80 rounded-[2rem] overflow-hidden shadow-lg border border-slate-100 group">
            <img src="{{ asset('storage/' . $configSite['aparencia_capa']) }}" 
                 alt="{{ $configSite['empresa_nome'] ?? 'Capa da Loja' }}" 
                 class="w-full h-full object-cover object-center transition-transform duration-1000 group-hover:scale-[1.02]">
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-60"></div>
        </section>
    @endif

    <div class="flex flex-col lg:flex-row gap-10">

        <!-- SIDEBAR: Filtros e Categorias -->
        <aside class="w-full lg:w-72 shrink-0">
            <div class="sticky top-28 space-y-8">
                
                {{-- Busca Rápida --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
                    <h3 class="font-black text-slate-800 text-xs uppercase tracking-widest mb-4 flex items-center gap-2">
                        <x-icon name="magnifying-glass" class="w-4 h-4 text-brand-primary" /> Buscar
                    </h3>
                    <form action="{{ route('site.catalogo') }}" method="GET" class="relative">
                        <input type="text" name="busca" value="{{ request('busca') }}" placeholder="O que você precisa?" 
                               class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-4 pr-10 text-sm focus:ring-brand-primary focus:border-brand-primary transition-all">
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-primary">
                            <x-icon name="magnifying-glass" class="w-5 h-5" />
                        </button>
                    </form>
                </div>

                {{-- Navegação de Categorias --}}
                <div class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
                        <h3 class="font-black text-slate-800 text-xs uppercase tracking-widest">Categorias</h3>
                    </div>
                    <nav class="p-2">
                        <a href="{{ route('site.catalogo') }}" class="group flex items-center justify-between px-4 py-3 rounded-xl transition-all {{ !isset($categoriaAtiva) || !$categoriaAtiva ? 'bg-brand-secondary text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50 hover:pl-6' }}">
                            <span class="font-bold text-sm tracking-tight">🎒 Todos os Produtos</span>
                            <x-icon name="chevron-right" class="w-4 h-4 opacity-30" />
                        </a>
                        @foreach($categorias as $cat)
                            @if($cat->total_publico > 0)
                                <a href="{{ route('site.categoria', $cat->slug) }}" class="group flex items-center justify-between px-4 py-3 rounded-xl transition-all {{ isset($categoriaAtiva) && $categoriaAtiva?->id === $cat->id ? 'bg-brand-secondary text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50 hover:pl-6' }}">
                                    <span class="font-bold text-sm tracking-tight">{{ $cat->nome }}</span>
                                    <span class="text-[10px] font-black {{ isset($categoriaAtiva) && $categoriaAtiva?->id === $cat->id ? 'bg-white/20' : 'bg-slate-100 text-slate-400' }} rounded-full px-2 py-0.5">
                                        {{ $cat->total_publico }}
                                    </span>
                                </a>
                            @endif
                        @endforeach
                    </nav>
                </div>

                {{-- Trust Banner Sidebar --}}
                <div class="rounded-2xl bg-gradient-to-br from-brand-primary to-orange-600 p-6 text-white shadow-xl shadow-brand-primary/20">
                    <x-icon name="truck" class="w-10 h-10 mb-4 opacity-50" />
                    <h4 class="font-black text-lg leading-tight mb-2">Entrega em todo o Brasil</h4>
                    <p class="text-xs text-white/80 leading-relaxed">Produção ágil com logística otimizada para sua região.</p>
                </div>
            </div>
        </aside>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="flex-1 min-w-0">

            <!-- Toolbar Superior -->
            <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="w-8 h-1 bg-brand-primary rounded-full"></span>
                        <h1 class="text-3xl font-black text-brand-secondary tracking-tighter">
                            {{ isset($categoriaAtiva) && $categoriaAtiva ? $categoriaAtiva->nome : 'Nossa Gráfica' }}
                        </h1>
                    </div>
                    <p class="text-slate-400 text-sm font-medium ml-11">
                        Mostrando {{ $produtos->count() }} de {{ $produtos->total() }} produtos premium
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    {{-- Ordenação --}}
                    <div class="relative">
                        <select onchange="window.location.href = this.value" class="appearance-none rounded-xl border-slate-200 bg-white py-3 pl-5 pr-12 text-sm font-bold text-slate-700 shadow-sm focus:ring-brand-primary transition-all cursor-pointer">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'destaque']) }}" {{ request('sort') == 'destaque' ? 'selected' : '' }}>Relevância</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'novidades']) }}" {{ request('sort') == 'novidades' ? 'selected' : '' }}>Novidades</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'preco_min']) }}" {{ request('sort') == 'preco_min' ? 'selected' : '' }}>Menor Preço</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'preco_max']) }}" {{ request('sort') == 'preco_max' ? 'selected' : '' }}>Maior Preço</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'nome']) }}"      {{ request('sort') == 'nome' ? 'selected' : '' }}>Nome (A-Z)</option>
                        </select>
                        <x-icon name="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                    </div>

                    {{-- Alternador Grid/Lista --}}
                    <div class="hidden sm:flex bg-slate-100 p-1 rounded-xl">
                        <button onclick="alterarVista('grid')" id="btn-grid" class="p-2 rounded-lg transition-all vista-btn active-view">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                        </button>
                        <button onclick="alterarVista('lista')" id="btn-lista" class="p-2 rounded-lg transition-all vista-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            @if(isset($categoriaAtiva) && $categoriaAtiva && $categoriaAtiva->texto_destaque)
                <div class="mb-10 relative overflow-hidden rounded-3xl bg-slate-900 px-8 py-10 text-white">
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-8">
                        <div class="flex-1">
                            <span class="text-brand-primary font-black uppercase tracking-[0.2em] text-[10px] mb-4 block">Sobre esta categoria</span>
                            <div class="text-lg md:text-xl font-medium leading-relaxed opacity-90">
                                {{ $categoriaAtiva->texto_destaque }}
                            </div>
                        </div>
                        <div class="hidden lg:block w-32 h-32 shrink-0 bg-white/5 rounded-full blur-2xl"></div>
                    </div>
                    <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-brand-primary/10 rounded-full blur-3xl"></div>
                </div>
            @endif

            <!-- Área de Listagem -->
            <div id="view-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($produtos as $produto)
                    <x-product-card :produto="$produto" />
                @empty
                    <div class="col-span-full py-32 text-center">
                        <div class="bg-slate-50 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                            <x-icon name="magnifying-glass" class="w-12 h-12 text-slate-200" />
                        </div>
                        <h3 class="text-xl font-black text-slate-800">Ops! Nada por aqui.</h3>
                        <p class="text-slate-400 mt-2">Não encontramos produtos com os critérios selecionados.</p>
                        <a href="{{ route('site.catalogo') }}" class="mt-8 inline-block bg-brand-secondary text-white font-black px-8 py-3 rounded-xl hover:bg-brand-primary transition-all">Ver tudo</a>
                    </div>
                @endforelse
            </div>

            <!-- View Lista (Otimizada) -->
            <div id="view-lista" class="hidden flex-col gap-4">
                @foreach($produtos as $produto)
                    <article class="group flex flex-col sm:flex-row items-center gap-8 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm transition-all hover:shadow-xl hover:border-brand-primary/20">
                        <div class="h-32 w-full sm:w-32 shrink-0 overflow-hidden rounded-2xl bg-slate-50">
                            @if($produto->imagem_principal)
                                <img src="{{ asset('storage/' . $produto->imagem_principal) }}" alt="{{ $produto->nome }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-4xl text-slate-100">📦</div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0 text-center sm:text-left">
                            <span class="text-[10px] font-black uppercase tracking-widest text-brand-primary">{{ $produto->categoriaRel->nome ?? 'Impressos' }}</span>
                            <h2 class="text-xl font-bold text-slate-800 mt-1">{{ $produto->nome }}</h2>
                            <p class="text-sm text-slate-400 mt-1 line-clamp-1 italic">{{ $produto->descricao_curta }}</p>
                        </div>
                        <div class="shrink-0 flex flex-col items-center sm:items-end gap-3">
                            <div class="text-right">
                                <span class="block text-[9px] font-bold text-slate-400 uppercase text-center sm:text-right">A partir de</span>
                                <span class="text-2xl font-black text-brand-secondary">R$ {{ number_format($produto->preco_base, 2, ',', '.') }}</span>
                            </div>
                            <a href="{{ route('site.produto', $produto) }}" class="bg-brand-primary text-white font-black px-8 py-3 rounded-xl shadow-lg shadow-brand-primary/30 hover:scale-105 hover:bg-orange-600 transition-all">
                                Personalizar
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Paginação Customizada --}}
            <div class="mt-16">
                {{ $produtos->links() }}
            </div>

            {{-- Seção de Depoimentos da Loja --}}
            @if($depoimentos->count() > 0)
                <section class="mt-24 mb-16 pt-16 border-t border-slate-100">
                    <div class="flex flex-col md:flex-row items-center justify-between mb-12">
                        <div class="text-center md:text-left">
                            <h2 class="text-3xl font-black text-brand-secondary tracking-tighter">O que dizem nossos clientes</h2>
                            <p class="text-slate-400 font-medium">Histórias reais de quem confia na qualidade de nossa impressão.</p>
                        </div>
                        <div class="hidden md:flex gap-2">
                            <span class="text-xs font-black text-slate-300 uppercase tracking-widest">Prova Social Verificada</span>
                            <x-icon name="check-badge" class="w-4 h-4 text-emerald-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($depoimentos as $dep)
                            <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all relative group h-full flex flex-col">
                                {{-- Aspas decorativas --}}
                                <div class="absolute top-6 right-8 text-slate-50 text-6xl font-serif pointer-events-none group-hover:text-brand-primary/10 transition-colors">”</div>
                                
                                <div class="flex items-center gap-1 mb-6">
                                    @for($i=1; $i<=5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= ($dep->nota ?? 5) ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>

                                <blockquote class="text-slate-600 leading-relaxed font-medium mb-8 flex-1">
                                    "{{ $dep->depoimento_texto }}"
                                </blockquote>

                                <div class="flex items-center gap-4 pt-6 border-t border-slate-50 mt-auto">
                                    <div class="shrink-0">
                                        @if($dep->avatar_path)
                                            <img src="{{ asset('storage/' . $dep->avatar_path) }}" alt="{{ $dep->nome_autor }}" class="w-12 h-12 rounded-2xl object-cover shadow-sm ring-2 ring-white">
                                        @else
                                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-xs">
                                                {{ substr($dep->nome_autor, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-800 text-sm tracking-tight leading-none mb-1">{{ $dep->nome_autor }}</p>
                                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">
                                            {{ $dep->empresa_autor ?: ($dep->cargo_autor ?: 'Cliente Satisfeito') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </main>
    </div>

    <style>
        .vista-btn.active-view { @apply bg-brand-secondary text-white shadow-lg shadow-brand-secondary/20; }
        .vista-btn:not(.active-view) { @apply text-slate-400 hover:bg-white hover:text-brand-secondary; }
    </style>

    <script>
        const vistaKey = 'vapt_catalogo_pref';

        function alterarVista(tipo) {
            const grid  = document.getElementById('view-grid');
            const lista = document.getElementById('view-lista');
            const btnG  = document.getElementById('btn-grid');
            const btnL  = document.getElementById('btn-lista');

            if (tipo === 'grid') {
                grid.classList.remove('hidden');  grid.classList.add('grid');
                lista.classList.add('hidden');    lista.classList.remove('flex');
                btnG.classList.add('active-view'); btnL.classList.remove('active-view');
            } else {
                lista.classList.remove('hidden'); lista.classList.add('flex');
                grid.classList.add('hidden');     grid.classList.remove('grid');
                btnL.classList.add('active-view'); btnG.classList.remove('active-view');
            }
            localStorage.setItem(vistaKey, tipo);
        }

        const vistaSalva = localStorage.getItem(vistaKey) || '{{ $configSite["aparencia_layout_catalogo"] ?? "grid" }}';
        if (vistaSalva === 'lista') alterarVista('lista');
    </script>
</x-layouts.publico>

