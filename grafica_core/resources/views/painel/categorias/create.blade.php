{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-10
--}}
<x-layouts.app>
    <div class="mb-6 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Nova Categoria</h1>
            <p class="text-slate-500 font-medium">Defina um segmento para organizar os produtos do catálogo.</p>
        </div>
        <a href="{{ route('admin.catalog.categorias.index') }}" class="mt-4 sm:mt-0 rounded-xl border border-slate-200 bg-white px-5 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50">Voltar</a>
    </div>

    @if(session('erro'))
        <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 text-red-600 font-bold shadow-sm flex items-center gap-3">
            <span>⚠️</span> {{ session('erro') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.catalog.categorias.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        @csrf
        
        <!-- Campos principais -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-md">
                <h2 class="text-lg font-black text-slate-800 mb-5 border-b border-slate-100 pb-2">Dados da Categoria</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-bold text-slate-600">Nome da Categoria <span class="text-red-500">*</span></label>
                        <input name="nome" value="{{ old('nome') }}" required placeholder="Ex: Impressos Gráficos" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 shadow-inner focus:bg-white focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-bold text-slate-600">Descrição Curta</label>
                        <input name="descricao" value="{{ old('descricao') }}" placeholder="Uma frase sobre esta família de produtos" maxlength="255" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 shadow-inner focus:bg-white focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-bold text-slate-600">Texto de Destaque (Parágrafo visível no topo da listagem)</label>
                        <textarea name="texto_destaque" rows="3" placeholder="Descrição longa exibida quando o cliente acessa a categoria específica..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 shadow-inner focus:bg-white focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">{{ old('texto_destaque') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-600">Ordem de Exibição</label>
                        <input type="number" min="0" name="ordem_exibicao" value="{{ old('ordem_exibicao', 0) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 shadow-inner focus:bg-white focus:border-brand-primary focus:ring-1 focus:ring-brand-primary">
                        <p class="text-xs text-slate-400 mt-1">Menor número = aparece primeiro. Pode reordenar pelo painel depois.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-md">
                <h2 class="text-lg font-black text-slate-800 mb-5 border-b border-slate-100 pb-2">Banner da Categoria</h2>
                <div class="relative cursor-pointer w-full h-44 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center hover:border-brand-primary hover:bg-brand-primary/5 transition overflow-hidden">
                    <div class="pointer-events-none text-center">
                        <span class="text-4xl block mb-2 text-slate-300">🖼️</span>
                        <span class="text-sm font-bold text-slate-500 block">Upload do Banner (JPG, PNG ou WEBP)</span>
                        <span class="text-xs text-slate-400 block">Recomendado: 1200×400px</span>
                    </div>
                    <input type="file" name="banner" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                </div>
            </div>
        </div>

        <!-- Publicação -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            <div class="rounded-2xl border border-brand-primary/30 bg-orange-50/30 p-6 shadow-lg shadow-orange-500/10">
                <h3 class="font-black text-brand-secondary mb-5 uppercase tracking-widest text-xs border-b border-brand-primary/20 pb-2">Publicação</h3>
                
                <label class="flex items-center gap-3 cursor-pointer p-3 bg-white border border-slate-100 rounded-xl shadow-sm hover:border-brand-primary transition mb-6">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" value="1" checked class="w-5 h-5 text-brand-primary border-slate-300 rounded focus:ring-brand-primary">
                    <div>
                        <span class="font-bold text-slate-800 block">Categoria Ativa</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase">Visível na loja</span>
                    </div>
                </label>

                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-brand-primary to-orange-500 px-6 py-4 text-center font-black text-white shadow-xl shadow-orange-500/30 transition-transform hover:-translate-y-1 text-base uppercase tracking-wider">
                    Criar Categoria 🚀
                </button>
            </div>
        </div>
    </form>
</x-layouts.app>

