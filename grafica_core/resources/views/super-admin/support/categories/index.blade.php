{{-- Autoria: Abimael Borges | https://abimaelborges.adv.br | Data: 2026-04-16 --}}
<x-layouts.super-admin>
    <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Categorias de Suporte</h1>
            <p class="text-gray-500 text-sm">Gerencie as opções de categorias que as Lojas visualizarão ao abrir tickets.</p>
        </div>
        <button onclick="document.getElementById('modalNovaCategoria').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow text-sm font-semibold flex items-center gap-2">
            <i class="fas fa-plus"></i> Nova Categoria
        </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden border">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 border-b text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nome da Categoria</th>
                    <th class="px-6 py-3 border-b text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Tickets Abertos/Vinculados</th>
                    <th class="px-6 py-3 border-b text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 border-b text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categorias as $categoria)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800">
                            {{ $categoria->nome }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-indigo-800 bg-indigo-100 rounded-full">
                                {{ $categoria->tickets_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($categoria->ativo)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Ativa</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-600">Inativa</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <form action="{{ route('superadmin.support.categorias.destroy', $categoria) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza? Isso pode falhar caso haja tickets vinculados a ela.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 mx-2"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            Nenhuma categoria cadastrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal Nova Categoria -->
    <div id="modalNovaCategoria" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Adicionar Categoria</h3>
                <button onclick="document.getElementById('modalNovaCategoria').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form action="{{ route('superadmin.support.categorias.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nome da Categoria</label>
                    <input type="text" name="nome" required placeholder="Ex: Dúvida Operacional" class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="ativo" value="1" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 font-medium">Categoria visível ativa</span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modalNovaCategoria').classList.add('hidden')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded font-semibold hover:bg-gray-200">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded font-semibold hover:bg-indigo-700">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.super-admin>
