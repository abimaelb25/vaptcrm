<x-layouts.app titulo="Depoimentos de Clientes - CMS">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">Depoimentos dos Clientes</h1>
            <p class="text-slate-500 mt-1 font-medium">Gerencie as provas sociais exibidas no seu catálogo público.</p>
        </div>
        <a href="{{ route('admin.system.depoimentos.create') }}" class="btn bg-brand-primary text-white font-bold py-2.5 px-5 rounded-xl shadow-lg hover:bg-orange-600 transition-all">+ Novo Depoimento</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                <tr>
                    <th class="px-6 py-4">Autor / Empresa</th>
                    <th class="px-6 py-4">Localização / Cargo</th>
                    <th class="px-6 py-4 text-center">Nota</th>
                    <th class="px-6 py-4 text-center">Destaque</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($depoimentos as $d)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($d->avatar_path)
                                <img src="{{ asset('storage/' . $d->avatar_path) }}" class="h-8 w-8 rounded-full object-cover">
                            @else
                                <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                    <i class="fas fa-user text-xs"></i>
                                </div>
                            @endif
                            <div>
                                <p class="font-bold text-slate-800">{{ $d->nome_autor }}</p>
                                <p class="text-[11px] text-slate-400 font-medium">{{ $d->empresa_autor ?? '--' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-slate-600 font-medium">{{ $d->cidade_autor ?? '--' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $d->cargo_autor ?? '--' }}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-600">
                            {{ $d->nota ?? '0' }}/5
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($d->destaque)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-600 border border-indigo-100">SIM</span>
                        @else
                            <span class="text-slate-300">--</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($d->publicado)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase">Público</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-400 border border-slate-200 uppercase">Oculto</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.system.depoimentos.edit', $d->id) }}" class="p-2 hover:bg-slate-100 rounded-lg text-brand-primary transition-colors" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.system.depoimentos.destroy', $d->id) }}" method="POST" class="inline" onsubmit="return confirm('Excluir relato de cliente definitivamente?');">
                                @csrf @method('DELETE')
                                <button class="p-2 hover:bg-rose-50 rounded-lg text-rose-500 transition-colors" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-comment-slash text-4xl text-slate-200 mb-3"></i>
                            <p class="text-slate-400 font-medium tracking-tight">Nenhum depoimento de cliente cadastrado.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>

