<x-layouts.super-admin>
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Relatório de Assinaturas</h1>
            <p class="text-sm text-gray-500 mt-1">Visão geral de todas as assinaturas emitidas (ativas, canceladas, vencidas).</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Filters -->
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <form action="{{ route('superadmin.assinaturas.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div>
                    <select name="status" class="block w-full pl-3 pr-10 py-2 border border-gray-300 bg-white rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Todos os status</option>
                        <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial (Avaliação)</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativa</option>
                        <option value="past_due" {{ request('status') === 'past_due' ? 'selected' : '' }}>Atrasada</option>
                        <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Cancelada</option>
                        <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Inadimplente (Unpaid)</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                        Filtrar
                    </button>
                </div>
                @if(request()->anyFilled(['status', 'plano_id']))
                    <div>
                        <a href="{{ route('superadmin.assinaturas.index') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
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
                        <th class="px-6 py-4 font-semibold">Cód / Loja</th>
                        <th class="px-6 py-4 font-semibold">Stripe Sub ID</th>
                        <th class="px-6 py-4 font-semibold">Plano</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Fim Trial / Próx Renovação</th>
                        <th class="px-6 py-4 font-semibold">Cadastro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($assinaturas as $assinatura)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 border-b border-gray-100 pb-1 mb-1">Assinatura #{{ $assinatura->id }}</div>
                                <a href="{{ route('superadmin.lojas.show', $assinatura->loja_id) }}" class="text-sm font-medium text-indigo-600 hover:underline">
                                    {{ $assinatura->loja->nome_fantasia ?? 'Loja Removida' }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-500">
                                {{ $assinatura->stripe_subscription_id ?? 'Local/Integrando' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $assinatura->plano->nome ?? 'Nenhum' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $color = 'gray';
                                    if($assinatura->status == 'active') $color = 'green';
                                    if($assinatura->status == 'trial') $color = 'blue';
                                    if($assinatura->status == 'past_due') $color = 'yellow';
                                    if($assinatura->status == 'canceled' || $assinatura->status == 'unpaid') $color = 'red';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                    {{ strtoupper($assinatura->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">Vigência: {{ $assinatura->ends_at ? $assinatura->ends_at->format('d/m/Y') : '-' }}</div>
                                <div class="text-xs text-gray-500 mt-1">Trial fim: {{ $assinatura->trial_ends_at ? $assinatura->trial_ends_at->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $assinatura->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Nenhuma assinatura encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($assinaturas->hasPages())
           <div class="px-6 py-4 border-t border-gray-100">
               {{ $assinaturas->links() }}
           </div>
       @endif
    </div>
</x-layouts.super-admin>
