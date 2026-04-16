{{-- Autoria: Abimael Borges | https://abimaelborges.adv.br | Data: 2026-04-16 --}}
<x-layouts.app>
    <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Meus Chamados (Tickets)</h1>
            <p class="text-slate-500 text-sm">Acompanhe e interaja com o suporte do VaptCRM referente à sua conta.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.support.meus-tickets.index') }}" method="GET">
                <select name="status" onchange="this.form.submit()" class="border-gray-300 rounded-lg text-sm text-gray-600 shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                    <option value="abertos" {{ request('status', 'abertos') === 'abertos' ? 'selected' : '' }}>Somente em Andamento</option>
                    <option value="encerrados" {{ request('status') === 'encerrados' ? 'selected' : '' }}>Histórico Encerrado</option>
                </select>
            </form>
            <a href="{{ route('admin.support.meus-tickets.create') }}" class="bg-brand-primary hover:bg-orange-600 text-white px-5 py-2.5 rounded-lg shadow font-semibold transition-colors flex items-center gap-2">
                <i class="fas fa-plus"></i> Abrir Ticket
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Ticket #</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Assunto</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Atualização</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($tickets as $ticket)
                    <tr class="hover:bg-slate-50/80 transition-colors {{ $ticket->status === 'aguardando_cliente' ? 'bg-amber-50/50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-slate-400">
                            {{ $ticket->numero_ticket }}
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.support.meus-tickets.show', $ticket) }}" class="text-sm font-bold text-slate-800 hover:text-brand-primary transition-colors block">{{ $ticket->assunto }}</a>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $ticket->categoria->nome ?? 'Problema Técnico' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusColors = [
                                    'aberto' => 'bg-indigo-100 text-indigo-800',
                                    'aguardando_suporte' => 'bg-blue-100 text-blue-800',
                                    'aguardando_cliente' => 'bg-amber-100 text-amber-800',
                                    'resolvido' => 'bg-emerald-100 text-emerald-800',
                                    'fechado' => 'bg-slate-100 text-slate-600',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded bg-slate-100 {{ $statusColors[$ticket->status] ?? '' }}">
                                {{ str_replace('_', ' ', $ticket->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-xs text-slate-500 font-medium">
                            {{ $ticket->ultimo_evento_em ? $ticket->ultimo_evento_em->diffForHumans() : 'Aguardando' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                                <i class="fas fa-ticket-alt text-2xl"></i>
                            </div>
                            <span class="text-slate-500 font-medium block">Nenhum ticket encontrado.</span>
                            <p class="text-slate-400 text-sm mt-1">Se precisar de ajuda, abra um novo ticket e nossa equipe irá auxiliá-lo.</p>
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
</x-layouts.app>
