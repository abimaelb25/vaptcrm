{{-- Autoria: Abimael Borges | https://abimaelborges.adv.br | Data: 2026-04-16 --}}
<x-layouts.super-admin>
    <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Central de Tickets (Painel Master)</h1>
            <p class="text-gray-500 text-sm">Responda dúvidas técnicas de todas as lojas locatárias do VaptCRM.</p>
        </div>
        
        <form action="{{ route('superadmin.support.tickets.index') }}" method="GET" class="flex items-center gap-3">
            <select name="status" onchange="this.form.submit()" class="border-gray-300 rounded text-sm text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="abertos" {{ request('status', 'abertos') === 'abertos' ? 'selected' : '' }}>Somente Abertos / Pendentes</option>
                <option value="resolvidos" {{ request('status') === 'resolvidos' ? 'selected' : '' }}>Resolvidos / Fechados</option>
            </select>
            <select name="loja_id" onchange="this.form.submit()" class="border-gray-300 rounded text-sm text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todas as Lojas</option>
                @foreach($lojas as $loja)
                    <option value="{{ $loja->id }}" {{ request('loja_id') == $loja->id ? 'selected' : '' }}>#{{ $loja->id }} - {{ $loja->nome_fantasia }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden border">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 border-b text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">TICKET</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ASSUNTO</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">LOJA (TENANT)</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">USUÁRIO</th>
                    <th class="px-6 py-3 border-b text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">STATUS</th>
                    <th class="px-6 py-3 border-b text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">ÚLTIMA AÇÃO</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tickets as $ticket)
                    <tr class="hover:bg-slate-50 transition-colors {{ $ticket->status === 'aberto' ? 'bg-indigo-50/30' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-slate-400">
                            {{ $ticket->numero_ticket }}
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('superadmin.support.tickets.show', $ticket) }}" class="text-sm font-bold text-indigo-700 hover:text-indigo-900 border-b border-transparent hover:border-indigo-500 transition-colors">{{ $ticket->assunto }}</a>
                            <div class="text-[10px] text-gray-400 mt-1 uppercase font-semibold">Prioridade: 
                                <span class="{{ $ticket->prioridade === 'urgente' ? 'text-red-500 font-bold' : ($ticket->prioridade === 'alta' ? 'text-orange-500' : 'text-slate-500') }}">{{ $ticket->prioridade }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-800 font-medium">
                            {{ $ticket->loja->nome_fantasia ?? 'Desconhecida' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $ticket->user->nome ?? 'Usuário' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusColors = [
                                    'aberto' => 'bg-red-100 text-red-800',
                                    'aguardando_suporte' => 'bg-orange-100 text-orange-800',
                                    'aguardando_cliente' => 'bg-blue-100 text-blue-800',
                                    'resolvido' => 'bg-green-100 text-green-800',
                                    'fechado' => 'bg-gray-100 text-gray-600',
                                ];
                                $color = $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full {{ $color }}">
                                {{ str_replace('_', ' ', $ticket->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-xs text-gray-500">
                            {{ $ticket->ultimo_evento_em ? $ticket->ultimo_evento_em->diffForHumans() : 'Nunca' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="text-gray-400 mb-2"><i class="fas fa-check-double text-4xl"></i></div>
                            <span class="text-gray-500 font-medium text-lg">Tudo limpo! Nenhum ticket encontrado.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($tickets->hasPages())
            <div class="p-4 border-t">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</x-layouts.super-admin>
