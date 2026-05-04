<x-layouts.super-admin>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">VaptAcademy - Módulos</h1>
            <p class="text-sm text-slate-500">Posicione os módulos dentro das trilhas e mantenha a sequência de aprendizagem.</p>
        </div>
        <a href="{{ route('superadmin.support.academy-trilhas.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Ver trilhas</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white rounded-xl border border-slate-200 p-6">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-500 mb-4">Novo Módulo</h2>
            <form action="{{ route('superadmin.support.academy-cursos.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400 mb-1">Trilha</label>
                    <select name="track_id" class="w-full rounded-lg border-slate-200">
                        <option value="">Sem trilha</option>
                        @foreach($tracks as $track)
                            <option value="{{ $track->id }}">{{ $track->titulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400 mb-1">Nome</label>
                    <input type="text" name="nome" required class="w-full rounded-lg border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400 mb-1">Descrição</label>
                    <textarea name="descricao" rows="3" class="w-full rounded-lg border-slate-200"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-400 mb-1">Ordem</label>
                    <input type="number" name="ordem" min="0" value="0" class="w-full rounded-lg border-slate-200">
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" value="1" checked class="rounded border-slate-300">
                    Módulo ativo
                </label>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 text-white py-2 font-bold">Salvar Módulo</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Módulo</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-500">Trilha</th>
                        <th class="px-4 py-3 text-center text-xs font-black uppercase tracking-wider text-slate-500">Aulas</th>
                        <th class="px-4 py-3 text-center text-xs font-black uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-black uppercase tracking-wider text-slate-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($courses as $course)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $course->nome }}</div>
                                <div class="text-xs text-slate-500">{{ $course->descricao ?: 'Sem descrição' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $course->track?->titulo ?: 'Sem trilha' }}</td>
                            <td class="px-4 py-3 text-center text-sm font-bold text-slate-700">{{ $course->conteudos_count }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($course->ativo)
                                    <span class="px-2 py-1 text-xs rounded-full bg-emerald-100 text-emerald-700 font-bold">Ativo</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-slate-100 text-slate-600 font-bold">Inativo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('superadmin.support.academy-cursos.destroy', $course) }}" method="POST" class="inline" onsubmit="return confirm('Remover este módulo?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm font-bold">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-400">Nenhum módulo cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.super-admin>
