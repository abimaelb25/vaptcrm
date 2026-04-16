<x-layouts.app titulo="Banners e Destaques - CMS">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">Banners (Destaques)</h1>
            <p class="text-slate-500 mt-1 font-medium">Gerencie os slides e banners principais da página inicial.</p>
        </div>
        <a href="{{ route('admin.system.banners.create') }}" class="btn bg-brand-primary text-white font-bold py-2.5 px-5 rounded-xl shadow">+ Novo Banner</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">Imagem</th>
                    <th class="px-6 py-4">Título</th>
                    <th class="px-6 py-4">Ordem</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($banners as $b)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">
                        <img src="{{ asset('storage/' . $b->imagem) }}" class="h-10 w-16 object-cover rounded shadow-sm border border-slate-200" alt="Banner">
                    </td>
                    <td class="px-6 py-4 font-bold text-slate-800">{{ $b->titulo }}</td>
                    <td class="px-6 py-4 text-slate-500">{{ $b->ordem }}</td>
                    <td class="px-6 py-4">
                        @if($b->ativo)
                            <span class="text-status-success font-bold text-xs uppercase">Ativo</span>
                        @else
                            <span class="text-slate-400 font-bold text-xs uppercase">Inativo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.system.banners.edit', $b->id) }}" class="text-brand-primary font-bold hover:underline">Editar</a>
                        <form action="{{ route('admin.system.banners.destroy', $b->id) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Deseja excluir permanentemente este banner?');">
                            @csrf @method('DELETE')
                            <button class="text-red-500 font-bold">Excluir</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-8 text-center text-slate-500">Nenhum banner cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>

