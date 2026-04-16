<x-layouts.app titulo="{{ $banner->exists ? 'Editar Banner' : 'Novo Banner' }}">
    <div class="mb-8">
        <a href="{{ route('admin.system.banners.index') }}" class="text-sm font-bold text-slate-500 hover:text-brand-primary mb-2 inline-block">&larr; Voltar para Banners</a>
        <h1 class="text-3xl font-black text-brand-secondary tracking-tight">{{ $banner->exists ? 'Editar Banner' : 'Criar Novo Banner' }}</h1>
    </div>

    <form action="{{ $banner->exists ? route('admin.system.banners.update', $banner->id) : route('admin.system.banners.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 overflow-hidden max-w-3xl">
        @csrf
        @if($banner->exists) @method('PUT') @endif

        <div class="space-y-6">
            @if($banner->exists && $banner->imagem)
                <div>
                    <p class="text-sm font-bold text-slate-700 mb-2">Imagem Atual</p>
                    <img src="{{ asset('storage/' . $banner->imagem) }}" class="h-32 object-cover rounded-xl border border-slate-200" alt="Atual">
                </div>
            @endif

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Arquivo de Imagem (Upload) {{ $banner->exists ? '' : '*' }}</label>
                <input type="file" name="imagem" accept="image/*" {{ $banner->exists ? '' : 'required' }} class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Título Grande *</label>
                <input type="text" name="titulo" value="{{ old('titulo', $banner->titulo) }}" required placeholder="Ex: Oferta de Cartões" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Subtítulo (Opcional)</label>
                <input type="text" name="subtitulo" value="{{ old('subtitulo', $banner->subtitulo) }}" placeholder="Ex: Válido até o fim do mês" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Link de Destino (Opcional)</label>
                    <input type="url" name="link" value="{{ old('link', $banner->link) }}" placeholder="https://..." class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Ordem (Ex: 1, 2, 3)</label>
                    <input type="number" name="ordem" value="{{ old('ordem', $banner->ordem ?? 0) }}" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" id="ativo" name="ativo" value="1" {{ old('ativo', $banner->ativo ?? true) ? 'checked' : '' }} class="h-5 w-5 rounded border-slate-300 text-brand-primary">
                <label for="ativo" class="text-sm font-bold text-slate-800">Banner Ativo (Exibir na Home)</label>
            </div>
        </div>

        <div class="mt-8 flex justify-end pt-6 border-t border-slate-100">
            <button type="submit" class="px-8 py-3 font-bold text-white bg-brand-primary rounded-xl shadow-md">
                Salvar Banner
            </button>
        </div>
    </form>
</x-layouts.app>

