{{-- Autoria: Abimael Borges | https://abimaelborges.adv.br | Data: 2026-04-16 --}}
<x-layouts.super-admin>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">VaptAcademy - Gestão de Vídeos</h1>
            <p class="text-slate-500 text-sm">Gerencie os tutoriais disponíveis para todas as lojas da plataforma.</p>
        </div>
        <a href="{{ route('superadmin.support.central-de-ajuda.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow text-sm font-semibold">
            <i class="fas fa-plus mr-1"></i> Novo Vídeo
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 border-b text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mídia</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Título</th>
                    <th class="px-6 py-3 border-b text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 border-b text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Destaque</th>
                    <th class="px-6 py-3 border-b text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($contents as $video)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($video->thumbnail)
                                <img src="{{ $video->thumbnail }}" alt="Thumb" class="h-12 w-20 object-cover rounded shadow-sm border">
                            @else
                                <div class="h-12 w-20 flex items-center justify-center bg-gray-100 rounded text-gray-400">
                                    <i class="fab fa-youtube text-2xl"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-slate-800">{{ $video->titulo }}</div>
                            <div class="text-xs text-slate-400 truncate max-w-xs">{{ $video->descricao }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($video->publicado)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Publicado</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-600">Rascunho</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($video->destaque)
                                <i class="fas fa-star text-amber-500 text-lg"></i>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('superadmin.support.central-de-ajuda.edit', $video) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                            <form action="{{ route('superadmin.support.central-de-ajuda.destroy', $video) }}" method="POST" class="inline-block" onsubmit="return confirm('Excluir este vídeo permanentemente?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            Nenhum conteúdo de ajuda cadastrado ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.super-admin>
