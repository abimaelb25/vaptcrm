{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-06 00:00 -03:00
--}}
<x-layouts.app>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <a href="{{ route('admin.catalog.produtos.create') }}" class="rounded-xl bg-gradient-to-r from-brand-primary to-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-md transition-transform duration-300 hover:scale-105 hover:shadow-lg flex items-center gap-2">
                <span>➕</span> Novo Produto/Serviço
            </a>
        </div>
    </div>


    <!-- Tabela de Produtos -->
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-brand-secondary text-left text-white">
                    <tr>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs w-16 text-center">ID</th>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs">Apresentação</th>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs w-48">Preço & Prazos</th>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs w-32 text-center">Vitrine (Ativo)</th>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs w-28 text-center"><span class="sr-only">Ações</span>Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($produtos as $produto)
                        <tr class="transition-colors hover:bg-slate-50 group">
                            <td class="px-5 py-4 font-bold text-slate-400 text-center">#{{ $produto->id }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-slate-200 shadow-inner bg-slate-100">
                                        @if($produto->imagem_principal)
                                            <img src="{{ asset('storage/' . $produto->imagem_principal) }}" alt="{{ $produto->nome }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-xl text-slate-300">📦</div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800">{{ $produto->nome }}</span>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="inline-flex items-center rounded-md bg-cyan-50 px-2 py-0.5 text-xs font-semibold text-cyan-700 ring-1 ring-inset ring-cyan-700/10">{{ $produto->categoria }}</span>
                                            <span class="text-xs text-slate-400">{{ $produto->imagens->count() }} img(s) na galeria</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-brand-secondary text-lg">R$ {{ number_format((float) $produto->preco_base, 2, ',', '.') }}</div>
                                <div class="text-xs text-slate-500 font-medium">Prazo: {{ $produto->prazo_estimado ?: '--' }}</div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <form method="POST" action="{{ route('admin.catalog.produtos.toggle', $produto->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2 {{ $produto->ativo ? 'bg-status-success' : 'bg-slate-300' }}" role="switch" aria-checked="{{ $produto->ativo ? 'true' : 'false' }}">
                                        <span class="sr-only">Alternar status de exibição</span>
                                        <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $produto->ativo ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </form>
                                @if($produto->ativo)
                                    <p class="text-[10px] font-bold text-status-success uppercase mt-1">Exibindo</p>
                                @else
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">Oculto</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <form method="POST" action="{{ route('admin.catalog.produtos.duplicate', ['produto' => $produto->id]) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-blue-50 px-3 py-2 text-sm font-bold text-blue-600 transition-colors hover:bg-blue-100 ring-1 ring-blue-200" title="Duplicar Produto">
                                            Clonar
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.catalog.produtos.edit', $produto->id) }}" class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-600 transition-colors hover:bg-emerald-100 ring-1 ring-emerald-200" title="Editar Configurações">
                                        Editar
                                    </a>
                                    @if(auth()->user()->temPermissao('apagar_produto'))
                                    <form method="POST" action="{{ route('admin.catalog.produtos.destroy', $produto->id) }}" onsubmit="return confirm('Deseja excluir este item e suas variações?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-red-50 px-3 py-2 text-sm font-bold text-red-600 transition-colors hover:bg-red-100 ring-1 ring-red-200" title="Apagar Produto">
                                            Apagar
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center">
                                    <span class="text-4xl mb-3">📭</span>
                                    <span class="font-semibold text-lg">Nenhum produto cadastrado!</span>
                                    <span class="text-sm">Preencha o formulário acima para adicionar à base.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $produtos->links() }}
        </div>
    </div>

</x-layouts.app>
