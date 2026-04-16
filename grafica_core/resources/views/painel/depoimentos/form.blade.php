<x-layouts.app titulo="{{ $depoimento->exists ? 'Editar Depoimento' : 'Novo Depoimento' }}">
    <div class="mb-8">
        <a href="{{ route('admin.system.depoimentos.index') }}" class="text-sm font-bold text-slate-500 hover:text-brand-primary mb-2 inline-block transition-colors tracking-tight">
            <i class="fas fa-arrow-left mr-1 scale-75"></i> Voltar para Lista
        </a>
        <h1 class="text-3xl font-black text-brand-secondary tracking-tight">{{ $depoimento->exists ? 'Editar Depoimento' : 'Novo Depoimento de Cliente' }}</h1>
        <p class="text-slate-400 text-sm font-medium mt-1">Insira os relatos reais dos seus clientes para gerar confiança em novos compradores.</p>
    </div>

    <form action="{{ $depoimento->exists ? route('admin.system.depoimentos.update', $depoimento->id) : route('admin.system.depoimentos.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl border border-slate-200 shadow-xl p-8 overflow-hidden max-w-4xl">
        @csrf
        @if($depoimento->exists) @method('PUT') @endif

        <div class="space-y-8">
            {{-- Dados do Autor --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Nome do Cliente / Autor *</label>
                    <input type="text" name="nome_autor" value="{{ old('nome_autor', $depoimento->nome_autor) }}" required placeholder="Ex: João Silva" class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Cargo / Profissão</label>
                    <input type="text" name="cargo_autor" value="{{ old('cargo_autor', $depoimento->cargo_autor) }}" placeholder="Ex: Gerente de Marketing" class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Empresa</label>
                    <input type="text" name="empresa_autor" value="{{ old('empresa_autor', $depoimento->empresa_autor) }}" placeholder="Ex: Supermercado XYZ" class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Cidade / UF</label>
                    <input type="text" name="cidade_autor" value="{{ old('cidade_autor', $depoimento->cidade_autor) }}" placeholder="Ex: Alagoinhas - BA" class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all">
                </div>
            </div>

            <hr class="border-slate-100">

            {{-- Conteúdo do Depoimento --}}
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Título do Depoimento (Opcional)</label>
                        <input type="text" name="titulo" value="{{ old('titulo', $depoimento->titulo) }}" placeholder="Ex: Atendimento Impecável!" class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Nota (1 a 5)</label>
                        <select name="nota" class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all">
                            @foreach([5, 4, 3, 2, 1] as $n)
                                <option value="{{ $n }}" {{ old('nota', $depoimento->nota ?? 5) == $n ? 'selected' : '' }}>{{ $n }} Estrelas</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Texto do Relato *</label>
                    <textarea name="depoimento_texto" rows="4" required placeholder="Escreva aqui o que o cliente disse sobre seu produto ou atendimento..." class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary transition-all">{{ old('depoimento_texto', $depoimento->depoimento_texto) }}</textarea>
                </div>
            </div>

            <hr class="border-slate-100">

            {{-- Mídia e Configurações --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Avatar / Foto do Autor</label>
                    <div class="flex items-center gap-4">
                        <div class="shrink-0">
                            @if($depoimento->exists && $depoimento->avatar_path)
                                <img src="{{ asset('storage/' . $depoimento->avatar_path) }}" class="h-16 w-16 rounded-2xl object-cover border-2 border-slate-100 shadow-sm" alt="Avatar">
                            @else
                                <div class="h-16 w-16 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 border-2 border-dashed border-slate-200">
                                    <i class="fas fa-camera text-xl"></i>
                                </div>
                            @endif
                        </div>
                        <input type="file" name="avatar_path" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20 transition-all cursor-pointer">
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Configurações de Exibição</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="publicado" value="1" {{ old('publicado', $depoimento->exists ? $depoimento->publicado : true) ? 'checked' : '' }} class="h-6 w-6 rounded-lg text-brand-primary border-slate-300 focus:ring-0">
                                <span class="text-sm font-bold text-slate-800">Publicado</span>
                            </label>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="destaque" value="1" {{ old('destaque', $depoimento->destaque) ? 'checked' : '' }} class="h-6 w-6 rounded-lg text-indigo-600 border-slate-300 focus:ring-0">
                                <span class="text-sm font-bold text-slate-800">Destaque</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Ordem de Prioridade (0 é maior)</label>
                        <input type="number" name="ordem_exibicao" value="{{ old('ordem_exibicao', $depoimento->ordem_exibicao ?? 0) }}" class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-brand-primary/10 focus:border-brand-primary text-sm font-bold">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 flex justify-end pt-8 border-t border-slate-100">
            <button type="submit" class="px-12 py-4 font-black text-white bg-brand-primary rounded-2xl shadow-xl hover:shadow-2xl hover:bg-orange-600 active:scale-95 transition-all text-sm uppercase tracking-widest">
                <i class="fas fa-save mr-2"></i> Salvar Depoimento
            </button>
        </div>
    </form>
</x-layouts.app>

