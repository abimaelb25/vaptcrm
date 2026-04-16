<x-layouts.super-admin>
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gerenciamento de Lojas</h1>
            <p class="text-sm text-gray-500 mt-1">Lista de todas as lojas (tenants) cadastradas na plataforma.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Filters -->
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <form action="{{ route('superadmin.lojas.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label class="sr-only">Buscar</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="busca" value="{{ request('busca') }}" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Buscar por nome, email ou subdomínio...">
                    </div>
                </div>
                <div>
                    <select name="status" class="block w-full pl-3 pr-10 py-2 border border-gray-300 bg-white rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Todos os status</option>
                        <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativa</option>
                        <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativa</option>
                        <option value="bloqueada" {{ request('status') === 'bloqueada' ? 'selected' : '' }}>Bloqueada</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                        Filtrar
                    </button>
                </div>
                @if(request()->anyFilled(['busca', 'status']))
                    <div>
                        <a href="{{ route('superadmin.lojas.index') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            Limpar
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs uppercase text-gray-500 bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-4 font-semibold">Loja / Subdomínio</th>
                        <th class="px-6 py-4 font-semibold">Responsável</th>
                        <th class="px-6 py-4 font-semibold">Plano</th>
                        <th class="px-6 py-4 font-semibold">Status / Assinatura</th>
                        <th class="px-6 py-4 font-semibold">Cadastro</th>
                        <th class="px-6 py-4 font-semibold text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($lojas as $loja)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $loja->nome_fantasia }}</div>
                                <div class="text-sm text-gray-500">{{ $loja->subdominio }}.vaptcrm.com.br</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $loja->responsavel_nome }}</div>
                                <div class="text-sm text-gray-500">{{ $loja->responsavel_email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $loja->plano->nome ?? 'Sem Plano' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-1">
                                    @if($loja->estaBloqueada())
                                        <span class="inline-flex items-center w-max px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Bloqueada
                                        </span>
                                    @elseif($loja->status === 'ativo')
                                        <span class="inline-flex items-center w-max px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Ativa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center w-max px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Inativa
                                        </span>
                                    @endif
                                    
                                    @php $assinatura = $loja->assinaturas->first(); @endphp
                                    @if($assinatura)
                                        <span class="text-xs text-gray-500">
                                            Assinatura: {{ ucfirst($assinatura->status) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $loja->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="{{ route('superadmin.lojas.show', $loja->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-md hover:bg-indigo-100 transition-colors">Detalhes</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Nenhuma loja encontrada com os filtros atuais.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($lojas->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $lojas->links() }}
            </div>
        @endif
    </div>
</x-layouts.super-admin>
