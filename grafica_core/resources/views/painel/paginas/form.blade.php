<x-layouts.app titulo="{{ $pagina->exists ? 'Editar Página ' : 'Nova Página ' }} Institucional">
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <a href="{{ route('admin.system.paginas-legais.index') }}" class="text-sm font-bold text-slate-500 hover:text-brand-primary mb-2 inline-block">&larr; Voltar para Páginas</a>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">{{ $pagina->exists ? 'Editar Página: ' . $pagina->titulo : 'Criar Nova Página' }}</h1>
            
            @if(isset($templateSelecionado) && $templateSelecionado)
                <p class="text-emerald-600 font-bold text-sm mt-2 flex items-center gap-2">
                    <x-icon name="check-circle" class="w-4 h-4" /> Modelo Carregado Automaticamente
                </p>
            @endif
        </div>
    </div>

    @if(session('erro'))
        <div class="mb-4 p-4 rounded-xl bg-red-50 text-red-600 font-bold border border-red-100">
            {{ session('erro') }}
        </div>
    @endif

    <form action="{{ $pagina->exists ? route('admin.system.paginas-legais.update', $pagina->id) : route('admin.system.paginas-legais.store') }}" method="POST" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 overflow-hidden relative">
        @csrf
        @if($pagina->exists) @method('PUT') @endif
        
        <input type="hidden" name="tipo" value="{{ old('tipo', $pagina->tipo ?? (isset($templateSelecionado) ? $templateSelecionado : 'personalizada')) }}">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-3 space-y-6">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Título da Página / Exibição no Menu *</label>
                    <input type="text" name="titulo" value="{{ old('titulo', $pagina->titulo) }}" required placeholder="Ex: Termos de Uso" class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50 px-4 py-3 font-bold text-slate-800 focus:border-brand-primary focus:bg-white transition-all">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-500">Conteúdo da Página *</label>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Aceita tags HTML</span>
                    </div>
                    
                    @if(isset($templateSelecionado) || in_array($pagina->tipo, ['politica_privacidade', 'termos_condicoes', 'reembolso_devolucao', 'entregas_finalizacoes']))
                        <div class="mb-3 p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-800">
                            <strong>Dica Dinâmica:</strong> Você pode usar variáveis como <code>@{{ loja_nome }}</code>, <code>@{{ loja_email }}</code>, <code>@{{ loja_whatsapp }}</code> e <code>@{{ loja_cidade_uf }}</code> no texto. Na hora que o cliente acessar o site, elas serão substituídas pelos dados reais da sua empresa automaticamente!
                        </div>
                    @endif

                    <textarea name="conteudo" rows="18" required class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm leading-relaxed text-slate-700/90 focus:border-brand-primary focus:bg-white transition-all">{{ old('conteudo', $pagina->conteudo) }}</textarea>
                </div>
            </div>

            <div class="space-y-6 bg-slate-50 rounded-2xl p-6 border border-slate-100">
                <h4 class="font-black text-slate-700 border-b border-slate-200 pb-2 mb-4 uppercase tracking-widest text-[10px]">Configurações da Página</h4>

                <div>
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input type="checkbox" id="ativa" name="ativa" value="1" {{ old('ativa', $pagina->ativa) ? 'checked' : '' }} class="mt-0.5 h-5 w-5 rounded border-2 border-slate-300 text-brand-primary focus:ring-brand-primary transition-all">
                        <div>
                            <span class="block text-sm font-bold text-slate-800 group-hover:text-brand-primary transition-colors">Página Publicada</span>
                            <span class="block text-xs text-slate-500 leading-tight mt-1">Se visível e acessível pela URL pública do seu catálogo.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-200">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input type="checkbox" id="exibir_no_rodape" name="exibir_no_rodape" value="1" {{ old('exibir_no_rodape', $pagina->exibir_no_rodape) ? 'checked' : '' }} class="mt-0.5 h-5 w-5 rounded border-2 border-slate-300 text-blue-600 focus:ring-blue-600 transition-all">
                        <div>
                            <span class="block text-sm font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Exibir link no Rodapé</span>
                            <span class="block text-xs text-slate-500 leading-tight mt-1">Adiciona automaticamente um atalho nas opções de Institucional do site.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-200">
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Ordem Exibição</label>
                    <input type="number" name="ordem_exibicao" value="{{ old('ordem_exibicao', $pagina->ordem_exibicao ?? 0) }}" min="0" class="w-full rounded-xl border-2 border-slate-200 bg-white px-3 py-2 font-bold text-slate-700 focus:border-brand-primary text-sm transition-all">
                    <p class="text-[10px] text-slate-400 mt-2 font-medium">Use números baixos (0, 1, 2) para aparecer primeiro na lista do rodapé.</p>
                </div>
                
                @if($pagina->exists && $pagina->pagina_sistema)
                    <div class="pt-4 border-t border-slate-200 mt-4">
                        <span class="inline-block bg-brand-secondary text-white text-[9px] uppercase tracking-widest font-black px-3 py-1 rounded-full w-full text-center">
                            Essencial do Sistema
                        </span>
                        <p class="text-[10px] text-center text-slate-500 mt-2">Esta página é imodificável em sua exclusão por exigências dos provedores de pagamento.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8 flex justify-end pt-6 border-t border-slate-100">
            <button type="submit" class="px-10 py-4 font-black tracking-widest uppercase text-white bg-brand-primary hover:bg-orange-600 rounded-2xl shadow-xl shadow-brand-primary/20 hover:scale-105 transition-all">
                Salvar Página
            </button>
        </div>
    </form>
</x-layouts.app>

