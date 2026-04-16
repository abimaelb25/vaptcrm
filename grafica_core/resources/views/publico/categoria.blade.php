{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-10
--}}
<x-layouts.publico>
    {{-- Breadcrumbs --}}
    <nav class="flex mb-4 text-xs font-bold uppercase tracking-widest text-slate-400 gap-2 items-center">
        <a href="{{ route('site.inicio') }}" class="hover:text-brand-primary transition-colors">Início</a>
        <span>/</span>
        <a href="{{ route('site.catalogo') }}" class="hover:text-brand-primary transition-colors">Catálogo</a>
        <span>/</span>
        <span class="text-brand-primary">{{ $categoria->nome }}</span>
    </nav>

    {{-- Banner da categoria --}}
    @if($categoria->banner)
        <div class="relative w-full h-52 sm:h-64 mb-8 rounded-3xl overflow-hidden shadow-xl">
            <img src="{{ asset('storage/' . $categoria->banner) }}" alt="{{ $categoria->nome }}" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-secondary/80 via-brand-secondary/30 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6">
                <p class="text-orange-300 font-black uppercase tracking-widest text-xs mb-1">Categoria</p>
                <h1 class="text-4xl font-black text-white">{{ $categoria->nome }}</h1>
                @if($categoria->descricao)
                    <p class="text-white/80 mt-1 font-medium">{{ $categoria->descricao }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="mb-8">
            <p class="text-brand-accent font-black uppercase tracking-widest text-xs">Categoria</p>
            <h1 class="text-3xl font-black text-brand-secondary mt-1">{{ $categoria->nome }}</h1>
            @if($categoria->descricao)
                <p class="text-slate-500 text-sm mt-1 font-medium">{{ $categoria->descricao }}</p>
            @endif
        </div>
    @endif

    @if($categoria->texto_destaque)
        <div class="mb-8 rounded-2xl border border-brand-primary/15 bg-gradient-to-r from-orange-50 to-amber-50/30 p-5 text-slate-700 font-medium leading-relaxed shadow-sm">
            {{ $categoria->texto_destaque }}
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- SIDEBAR -->
        <aside class="w-full lg:w-56 shrink-0">
            <div class="rounded-2xl border border-slate-100 bg-white shadow-md overflow-hidden sticky top-6">
                <div class="bg-gradient-to-r from-brand-secondary to-slate-700 px-5 py-3">
                    <p class="text-white font-black text-xs uppercase tracking-widest">Outras Categorias</p>
                </div>
                <nav class="p-3 space-y-1">
                    <a href="{{ route('site.catalogo') }}" class="flex items-center px-4 py-2.5 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-50 transition">🛍️ Ver Tudo</a>
                    @foreach($categorias as $cat)
                        @if($cat->total_publico > 0)
                            <a href="{{ route('site.categoria', $cat->slug) }}" class="flex items-center justify-between px-4 py-2.5 rounded-xl font-bold text-sm transition {{ $cat->id === $categoria->id ? 'bg-brand-primary text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                                <span>{{ $cat->nome }}</span>
                                <span class="text-xs ml-2 {{ $cat->id === $categoria->id ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }} rounded-full px-2 py-0.5 font-black">{{ $cat->total_publico }}</span>
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>
        </aside>

        <!-- PRODUTOS -->
        <div class="flex-1 min-w-0">
            <div class="mb-5 flex items-center justify-between">
                <p class="text-slate-500 text-sm font-medium">{{ $produtos->total() }} produto(s)</p>
                <!-- Alterna Vista -->
                <div class="flex gap-2">
                    <button id="btn-grid" onclick="alterarVista('grid')" class="p-2.5 rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-primary hover:text-brand-primary text-slate-400 vista-btn active-view">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    </button>
                    <button id="btn-lista" onclick="alterarVista('lista')" class="p-2.5 rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-primary hover:text-brand-primary text-slate-400 vista-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    </button>
                </div>
            </div>

            <div id="view-grid" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($produtos as $produto)
                    <article class="group overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl flex flex-col">
                        <div class="aspect-square w-full overflow-hidden bg-slate-100">
                            @if($produto->imagem_principal)
                                <img src="{{ asset('storage/' . $produto->imagem_principal) }}" alt="{{ $produto->nome }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-5xl text-slate-300">📦</div>
                            @endif
                        </div>
                        <div class="p-4 flex flex-col flex-1">
                            <h2 class="font-bold text-slate-800 line-clamp-1">{{ $produto->nome }}</h2>
                            <p class="text-sm text-slate-500 line-clamp-2 flex-1 mt-1">{{ $produto->descricao_curta }}</p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="font-black text-brand-secondary">
                                    {{ $produto->preco_base ? 'R$ ' . number_format($produto->preco_base, 2, ',', '.') : 'Consulte' }}
                                </span>
                                <a href="{{ route('site.produto', $produto) }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-brand-secondary transition group-hover:bg-brand-primary group-hover:text-white">Ver →</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-3 py-16 text-center">
                        <span class="text-5xl block mb-3 opacity-30">🔍</span>
                        <span class="font-bold text-slate-600 text-lg block">Nenhum produto nesta categoria ainda.</span>
                    </div>
                @endforelse
            </div>

            <div id="view-lista" class="hidden flex-col gap-3">
                @foreach($produtos as $produto)
                    <article class="flex items-center gap-5 rounded-2xl border border-slate-100 bg-white p-4 shadow-md transition hover:shadow-lg hover:border-brand-primary/20">
                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-slate-100 border border-slate-100">
                            @if($produto->imagem_principal)
                                <img src="{{ asset('storage/' . $produto->imagem_principal) }}" alt="{{ $produto->nome }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-2xl text-slate-300">📦</div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="font-bold text-slate-800 truncate">{{ $produto->nome }}</h2>
                            <p class="text-sm text-slate-500 line-clamp-1">{{ $produto->descricao_curta }}</p>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <span class="font-black text-brand-secondary">{{ $produto->preco_base ? 'R$ ' . number_format($produto->preco_base, 2, ',', '.') : 'Consulte' }}</span>
                            <a href="{{ route('site.produto', $produto) }}" class="rounded-xl bg-brand-primary px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-orange-600">Ver →</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">{{ $produtos->links() }}</div>
        </div>
    </div>

    <style>
        .active-view { background: rgba(255,122,0,0.1); border-color: rgba(255,122,0,0.5); color: rgb(255,122,0); }
    </style>
    <script>
        const vistaKey = 'grafica_vista_catalogo';
        function alterarVista(tipo) {
            const g = document.getElementById('view-grid'), l = document.getElementById('view-lista');
            const bG = document.getElementById('btn-grid'), bL = document.getElementById('btn-lista');
            if (tipo === 'grid') {
                g.classList.remove('hidden'); g.classList.add('grid');
                l.classList.add('hidden'); l.classList.remove('flex');
                bG.classList.add('active-view'); bL.classList.remove('active-view');
            } else {
                l.classList.remove('hidden'); l.classList.add('flex');
                g.classList.add('hidden'); g.classList.remove('grid');
                bL.classList.add('active-view'); bG.classList.remove('active-view');
            }
            localStorage.setItem(vistaKey, tipo);
        }
        if (localStorage.getItem(vistaKey) === 'lista') alterarVista('lista');
    </script>
</x-layouts.publico>

