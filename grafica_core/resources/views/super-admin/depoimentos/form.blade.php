{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-16 01:10 BRT
--}}
<x-layouts.super-admin>
    <div class="mb-8">
        <a href="{{ route('superadmin.depoimentos.index') }}" class="text-xs font-bold text-gray-400 hover:text-indigo-600 mb-2 inline-flex items-center transition-colors">
            <i class="fas fa-chevron-left mr-1"></i> Voltar
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $depoimento->exists ? 'Editar Depoimento Software' : 'Novo Depoimento Administrativo' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Este depoimento será exibido na landing page oficial do VaptCRM como prova social do sistema.</p>
    </div>

    <form action="{{ $depoimento->exists ? route('superadmin.depoimentos.update', $depoimento->id) : route('superadmin.depoimentos.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 max-w-4xl">
        @csrf
        @if($depoimento->exists) @method('PUT') @endif

        <div class="space-y-8">
            {{-- Dados do Usuário/Cliente do SaaS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nome do Usuário *</label>
                    <input type="text" name="nome_autor" value="{{ old('nome_autor', $depoimento->nome_autor) }}" required placeholder="Ex: Roberto Oliveira" class="w-full rounded-xl border-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Cargo / Especialidade</label>
                    <input type="text" name="cargo_autor" value="{{ old('cargo_autor', $depoimento->cargo_autor) }}" placeholder="Ex: Proprietário de Gráfica" class="w-full rounded-xl border-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Empresa Atendida</label>
                    <input type="text" name="empresa_autor" value="{{ old('empresa_autor', $depoimento->empresa_autor) }}" placeholder="Ex: Gráfica Premium" class="w-full rounded-xl border-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Cidade / Estado</label>
                    <input type="text" name="cidade_autor" value="{{ old('cidade_autor', $depoimento->cidade_autor) }}" placeholder="Ex: São Paulo - SP" class="w-full rounded-xl border-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500">
                </div>
            </div>

            <hr class="border-gray-50">

            {{-- Conteúdo --}}
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Título do Depoimento</label>
                        <input type="text" name="titulo" value="{{ old('titulo', $depoimento->titulo) }}" placeholder="Ex: O segredo para escalar minha produção" class="w-full rounded-xl border-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nota Software (1-5)</label>
                        <select name="nota" class="w-full rounded-xl border-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500">
                            @foreach([5, 4, 3, 2, 1] as $n)
                                <option value="{{ $n }}" {{ old('nota', $depoimento->nota ?? 5) == $n ? 'selected' : '' }}>{{ $n }} Estrelas</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Depoimento sobre o VaptCRM *</label>
                    <textarea name="depoimento_texto" rows="5" required placeholder="Relato do usuário sobre como o software ajudou a empresa dele..." class="w-full rounded-xl border-gray-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500">{{ old('depoimento_texto', $depoimento->depoimento_texto) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-end">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Avatar / Foto</label>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        @if($depoimento->exists && $depoimento->avatar_path)
                            <img src="{{ asset('storage/' . $depoimento->avatar_path) }}" class="h-14 w-14 rounded-xl object-cover border border-white shadow-sm">
                        @else
                           <div class="h-14 w-14 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-300">
                               <i class="fas fa-camera"></i>
                           </div>
                        @endif
                        <input type="file" name="avatar_path" accept="image/*" class="text-[10px] text-gray-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition-colors">
                    </div>
                </div>

                <div class="flex gap-4">
                    <label class="flex-1 p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="publicado" value="1" {{ old('publicado', $depoimento->exists ? $depoimento->publicado : true) ? 'checked' : '' }} class="h-5 w-5 rounded-lg text-indigo-600 border-gray-300 focus:ring-0">
                        <span class="text-sm font-bold text-gray-700">Publicado</span>
                    </label>
                    <label class="flex-1 p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="destaque" value="1" {{ old('destaque', $depoimento->destaque) ? 'checked' : '' }} class="h-5 w-5 rounded-lg text-indigo-600 border-gray-300 focus:ring-0">
                        <span class="text-sm font-bold text-gray-700">Destaque</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-10 flex justify-end pt-8 border-t border-gray-50">
            <button type="submit" class="px-10 py-3.5 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:shadow-indigo-500/20 hover:scale-[1.02] active:scale-95 transition-all text-sm uppercase tracking-widest">
                Salvar Prova Social
            </button>
        </div>
    </form>
</x-layouts.super-admin>
