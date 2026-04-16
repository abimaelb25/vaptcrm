{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-16 01:05 BRT
--}}
<x-layouts.super-admin>
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Prova Social - VaptCRM</h1>
            <p class="text-sm text-gray-500 mt-1">Gerencie depoimentos de usuários sobre o software para a landing page.</p>
        </div>
        <a href="{{ route('superadmin.depoimentos.create') }}" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700 transition-all">
            <i class="fas fa-plus mr-2 text-xs"></i> Novo Depoimento Software
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-600 font-bold border-b border-gray-100 uppercase text-[10px] tracking-widest">
                <tr>
                    <th class="px-6 py-4">Usuário / Empresa</th>
                    <th class="px-6 py-4">Cargo / Local</th>
                    <th class="px-6 py-4 text-center">Nota</th>
                    <th class="px-6 py-4 text-center">Visibilidade</th>
                    <th class="px-6 py-4 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($depoimentos as $d)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($d->avatar_path)
                                <img src="{{ asset('storage/' . $d->avatar_path) }}" class="h-10 w-10 rounded-xl object-cover">
                            @else
                                <div class="h-10 w-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                            <div>
                                <p class="font-bold text-gray-900">{{ $d->nome_autor }}</p>
                                <p class="text-xs text-gray-400 font-medium">{{ $d->empresa_autor ?? 'Sem empresa' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-gray-700 font-medium">{{ $d->cargo_autor ?? '--' }}</p>
                        <p class="text-xs text-gray-400">{{ $d->cidade_autor ?? '--' }}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-600 border border-amber-100">
                            {{ $d->nota ?? '5' }} / 5
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col gap-1 items-center">
                            @if($d->publicado)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-green-50 text-green-600 border border-green-100 uppercase">Landing Page</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-gray-100 text-gray-400 border border-gray-200 uppercase">Oculto</span>
                            @endif
                            @if($d->destaque)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase">Destaque</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('superadmin.depoimentos.edit', $d->id) }}" class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('superadmin.depoimentos.destroy', $d->id) }}" method="POST" class="inline" onsubmit="return confirm('Excluir prova social do software?');">
                                @csrf @method('DELETE')
                                <button class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-16 text-center">
                        <i class="fas fa-quote-right text-4xl text-gray-100 mb-4 block"></i>
                        <p class="text-gray-400 font-medium">Nenhum depoimento institucional cadastrado para a landing page.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.super-admin>
