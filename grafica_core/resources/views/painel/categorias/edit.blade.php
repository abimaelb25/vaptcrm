{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-10
--}}
<x-layouts.app>
    <div class="mb-6 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Editar: {{ $categoria->nome }}</h1>
            <p class="text-slate-500 font-medium">Slug: <code class="bg-slate-100 px-2 py-0.5 rounded text-xs font-mono">/catalogo/categoria/{{ $categoria->slug }}</code></p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <a href="{{ route('site.categoria', $categoria->slug) }}" target="_blank" class="rounded-xl border border-brand-primary/30 bg-orange-50 px-5 py-2 text-sm font-bold text-brand-primary shadow-sm transition hover:bg-orange-100">Ver no Site ↗</a>
            <a href="{{ route('admin.catalog.categorias.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50">Voltar</a>
        </div>
    </div>

    @if(session('erro'))
        <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 text-red-600 font-bold shadow-sm flex items-center gap-3">
            <span>⚠️</span> {{ session('erro') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.catalog.categorias.update', $categoria->id) }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        @csrf
        @method('PUT')
        
        <div class="lg:col-span-8 flex flex-col gap-6">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-md">
                <h2 class="text-lg font-black text-slate-800 mb-5 border-b border-slate-100 pb-2">Dados da Categoria</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-bold text-slate-600">Nome da Categoria <span class="text-red-500">*</span></label>
                        <input name="nome" value="{{ old('nome', $categoria->nome) }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 shadow-inner focus:bg-white focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-bold text-slate-600">Descrição Curta</label>
                        <input name="descricao" value="{{ old('descricao', $categoria->descricao) }}" maxlength="255" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 shadow-inner focus:bg-white focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-bold text-slate-600">Texto de Destaque</label>
                        <textarea name="texto_destaque" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 shadow-inner focus:bg-white focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">{{ old('texto_destaque', $categoria->texto_destaque) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-600">Ordem de Exibição</label>
                        <input type="number" min="0" name="ordem_exibicao" value="{{ old('ordem_exibicao', $categoria->ordem_exibicao) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 shadow-inner focus:bg-white focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-md">
                <h2 class="text-lg font-black text-slate-800 mb-4 border-b border-slate-100 pb-2">Banner da Categoria</h2>
                <div class="relative group cursor-pointer w-full h-44 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center hover:border-brand-primary hover:bg-brand-primary/5 transition overflow-hidden">
                    @if($categoria->banner)
                        <img src="{{ asset('storage/' . $categoria->banner) }}" class="absolute inset-0 w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                            <span class="text-white font-bold">Atualizar Banner</span>
                        </div>
                    @else
                        <div class="pointer-events-none text-center">
                            <span class="text-4xl block mb-2 text-slate-300">🖼️</span>
                            <span class="text-sm font-bold text-slate-500 block">Upload Banner (1200×400px)</span>
                        </div>
                    @endif
                    <input type="file" name="banner" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                </div>
            </div>
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-md">
                <h3 class="font-black text-slate-800 mb-4 border-b border-slate-100 pb-2 text-sm uppercase tracking-widest">Status</h3>
                
                <label class="flex items-center gap-3 cursor-pointer p-3 bg-slate-50 border border-slate-100 rounded-xl hover:border-brand-primary transition mb-6">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" value="1" class="w-5 h-5 text-brand-primary border-slate-300 rounded focus:ring-brand-primary" {{ old('ativo', $categoria->ativo) ? 'checked' : '' }}>
                    <div>
                        <span class="font-bold text-slate-800 block">Categoria Ativa</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase">Visível na loja</span>
                    </div>
                </label>

                <button type="submit" class="w-full rounded-xl bg-slate-800 px-6 py-4 text-center font-black text-white shadow-xl transition-transform hover:-translate-y-1 text-base uppercase tracking-wider">
                    Gravar Alterações
                </button>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-widest mb-2">Estatísticas</p>
                <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3">
                    <span class="text-2xl">📦</span>
                    <div>
                        <p class="font-black text-slate-700 text-xl">{{ $categoria->produtos()->count() }}</p>
                        <p class="text-xs text-slate-500 font-semibold">Produtos vinculados</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>

