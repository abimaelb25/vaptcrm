<x-layouts.super-admin>
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">VaptAcademy</h1>
            <p class="mt-2 max-w-3xl text-sm text-slate-500">Organize treinamentos, etapas e aulas sem romper a base atual da Central de Ajuda.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('superadmin.support.academy-cursos.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                Módulos
            </a>
            <a href="{{ route('superadmin.support.central-de-ajuda.index') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow transition hover:bg-indigo-700">
                Aulas
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[360px,1fr]">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-500">Nova trilha</h2>
            @if($tracks->isEmpty())
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <div class="text-xs font-black uppercase tracking-widest text-amber-700">Primeiro passo</div>
                    <p class="mt-2 text-sm font-semibold text-amber-900">Crie sua primeira trilha de treinamento.</p>
                    <p class="mt-1 text-sm text-amber-700">Você pode começar com uma sugestão pronta e ajustar depois.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach(['Comercial', 'Produção', 'WhatsApp', 'Financeiro'] as $suggestedTrack)
                            <button type="button" onclick="document.querySelector('[name=titulo]').value='{{ $suggestedTrack }}'; document.querySelector('[name=descricao]').value='Treinamento base da área de {{ $suggestedTrack }}.';" class="rounded-full border border-amber-200 bg-white px-3 py-1 text-xs font-black text-amber-700 transition hover:bg-amber-100">{{ $suggestedTrack }}</button>
                        @endforeach
                    </div>
                </div>
            @endif
            <form action="{{ route('superadmin.support.academy-trilhas.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-black uppercase tracking-wider text-slate-400">Título</label>
                    <input type="text" name="titulo" required class="w-full rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-black uppercase tracking-wider text-slate-400">Descrição</label>
                    <textarea name="descricao" rows="4" class="w-full rounded-xl border-slate-200"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-black uppercase tracking-wider text-slate-400">Ordem</label>
                    <input type="number" name="ordem" min="0" value="0" class="w-full rounded-xl border-slate-200">
                </div>
                <label class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                    <input type="hidden" name="publicado" value="0">
                    <input type="checkbox" name="publicado" value="1" checked class="rounded border-slate-300">
                    Trilha publicada
                </label>
                <label class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                    <input type="hidden" name="destaque" value="0">
                    <input type="checkbox" name="destaque" value="1" class="rounded border-slate-300">
                    Priorizar na biblioteca
                </label>
                <button type="submit" class="w-full rounded-xl bg-slate-900 py-3 text-sm font-black text-white transition hover:bg-slate-800">Salvar trilha</button>
            </form>
        </section>

        <section class="space-y-4">
            @forelse($tracks as $track)
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-xl font-black text-slate-900">{{ $track->titulo }}</h3>
                                @if($track->destaque)
                                    <span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-black uppercase tracking-wider text-amber-700">Destaque</span>
                                @endif
                                <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-wider {{ $track->publicado ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $track->publicado ? 'Publicada' : 'Oculta' }}</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-500">{{ $track->descricao ?: 'Sem descrição operacional cadastrada para esta trilha.' }}</p>
                        </div>
                        <div class="min-w-[180px] rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            <div class="font-black text-slate-900">{{ $track->modulos_count }} módulo(s)</div>
                            <div class="text-xs uppercase tracking-wider text-slate-400">Ordem {{ $track->ordem }}</div>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr,320px]">
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Módulos vinculados</h4>
                            <div class="mt-3 space-y-3">
                                @forelse($track->modulos as $module)
                                    <div class="rounded-xl border border-slate-200 px-4 py-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <div class="font-bold text-slate-800">{{ $module->nome }}</div>
                                                <div class="text-xs text-slate-500">{{ $module->conteudos_count }} aula(s)</div>
                                            </div>
                                            <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-wider {{ $module->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $module->ativo ? 'Ativo' : 'Inativo' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-400">Nenhum módulo nesta trilha ainda.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <form action="{{ route('superadmin.support.academy-trilhas.update', $track) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="mb-1 block text-[11px] font-black uppercase tracking-wider text-slate-400">Título</label>
                                <input type="text" name="titulo" value="{{ $track->titulo }}" required class="w-full rounded-xl border-slate-200 bg-white">
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-black uppercase tracking-wider text-slate-400">Descrição</label>
                                <textarea name="descricao" rows="3" class="w-full rounded-xl border-slate-200 bg-white">{{ $track->descricao }}</textarea>
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-black uppercase tracking-wider text-slate-400">Ordem</label>
                                <input type="number" name="ordem" min="0" value="{{ $track->ordem }}" class="w-full rounded-xl border-slate-200 bg-white">
                            </div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                <input type="hidden" name="publicado" value="0">
                                <input type="checkbox" name="publicado" value="1" {{ $track->publicado ? 'checked' : '' }} class="rounded border-slate-300">
                                Publicada
                            </label>
                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                <input type="hidden" name="destaque" value="0">
                                <input type="checkbox" name="destaque" value="1" {{ $track->destaque ? 'checked' : '' }} class="rounded border-slate-300">
                                Destacar na biblioteca
                            </label>
                            <div class="pt-2">
                                <button type="submit" class="flex-1 rounded-xl bg-white px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-100">Salvar</button>
                            </div>
                            </form>
                            <form action="{{ route('superadmin.support.academy-trilhas.destroy', $track) }}" method="POST" onsubmit="return confirm('Excluir esta trilha?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-xl bg-rose-600 px-3 py-2 text-sm font-bold text-white transition hover:bg-rose-700">Excluir</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-16 text-center text-slate-400">
                    Nenhuma trilha cadastrada ainda.
                </div>
            @endforelse
        </section>
    </div>
</x-layouts.super-admin>
