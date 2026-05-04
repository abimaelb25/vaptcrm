<x-layouts.app titulo="Inbox WhatsApp - VaptCRM">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">Inbox WhatsApp</h1>
            <p class="text-slate-500 mt-1 font-medium">Filtre conversas não lidas, por pedido vinculado, responsável e acompanhe o atendimento humano e automatizado.</p>
        </div>
        <a href="{{ route('admin.whatsapp.index') }}" class="px-5 py-3 rounded-2xl bg-white border border-slate-200 font-black text-slate-700 shadow-sm hover:border-brand-primary transition-colors">Voltar às configurações</a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <form method="GET" action="{{ route('admin.whatsapp.page.inbox') }}" class="p-6 grid grid-cols-1 md:grid-cols-4 xl:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-sm font-black text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                    <option value="all" @selected($status === 'all')>Todos</option>
                    <option value="open" @selected($status === 'open')>Abertas</option>
                    <option value="waiting" @selected($status === 'waiting')>Aguardando</option>
                    <option value="resolved" @selected($status === 'resolved')>Resolvidas</option>
                    <option value="bot" @selected($status === 'bot')>Bot</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-black text-slate-700 mb-1">Responsável</label>
                <select name="assigned_to" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                    <option value="">Todos</option>
                    @foreach($responsaveis as $responsavel)
                        <option value="{{ $responsavel->id }}" @selected((string) request('assigned_to') === (string) $responsavel->id)>{{ $responsavel->nome }}</option>
                    @endforeach
                </select>
            </div>
            <label class="inline-flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 bg-slate-50 text-sm font-bold text-slate-700">
                <input type="checkbox" name="unread" value="1" class="rounded border-slate-300 text-brand-primary focus:ring-brand-primary" @checked($filters['unread'])>
                Somente não lidas
            </label>
            <label class="inline-flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 bg-slate-50 text-sm font-bold text-slate-700">
                <input type="checkbox" name="linked_order" value="1" class="rounded border-slate-300 text-brand-primary focus:ring-brand-primary" @checked($filters['linked_order'])>
                Somente com pedido
            </label>
            <button type="submit" class="px-6 py-3 rounded-2xl bg-slate-900 text-white font-black hover:bg-black transition-colors">Aplicar filtros</button>
        </form>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        @forelse($conversations as $conversation)
            @php
                $humanStatus = match ($conversation->status) {
                    'resolved' => 'Resolvida',
                    'waiting' => 'Aguardando',
                    'bot' => 'Automação',
                    default => ($conversation->is_unread ? 'Nova' : 'Ativa'),
                };
                $statusClass = match ($humanStatus) {
                    'Nova' => 'bg-emerald-100 text-emerald-700',
                    'Ativa' => 'bg-sky-100 text-sky-700',
                    'Resolvida' => 'bg-slate-200 text-slate-700',
                    'Aguardando' => 'bg-amber-100 text-amber-700',
                    default => 'bg-violet-100 text-violet-700',
                };
            @endphp
            <a href="{{ route('admin.whatsapp.page.conversation', $conversation) }}" class="block rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-brand-primary hover:-translate-y-0.5 transition-all">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-lg font-black text-slate-800">{{ $conversation->contact_name ?: $conversation->contact_phone }}</h2>
                            @if($conversation->is_unread)
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-black uppercase tracking-wide">Não lida</span>
                            @endif
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wide {{ $statusClass }}">{{ $humanStatus }}</span>
                            @php $priority = $conversation->priority ?? 'normal'; @endphp
                            @if($priority !== 'normal')
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wide {{ $conversation->priorityBadgeClass() }}">
                                    {{ $conversation->humanPriority() }}
                                </span>
                            @endif
                            @if($conversation->origin_source)
                                <span class="px-2 py-0.5 rounded text-[10px] text-slate-400 bg-slate-100">{{ $conversation->origin_source }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 mt-1">{{ $conversation->contact_phone }}</p>
                    </div>
                    <span class="text-xs text-slate-400 font-bold">{{ optional($conversation->last_message_at)->format('d/m H:i') ?: '--' }}</span>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-slate-400 font-black uppercase text-[10px]">Cliente</p>
                        <p class="font-bold text-slate-700">{{ $conversation->cliente?->nome ?: 'Não vinculado' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 font-black uppercase text-[10px]">Pedido</p>
                        <p class="font-bold text-slate-700">{{ $conversation->pedido?->numero_exibicao ?: 'Não vinculado' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 font-black uppercase text-[10px]">Responsável</p>
                        <p class="font-bold text-slate-700">{{ $conversation->assignedTo?->nome ?: 'Sem responsável' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 font-black uppercase text-[10px]">Conta</p>
                        <p class="font-bold text-slate-700">{{ $conversation->account?->display_name ?: $conversation->account?->phone_number }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="xl:col-span-3 rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 font-medium">
                @if($allConversationsCount === 0)
                    <p class="text-lg font-black text-slate-700">Nenhuma conversa ainda</p>
                    <p class="mt-2">Quando um cliente enviar mensagem, ela aparecerá aqui para sua equipe responder.</p>
                    <a href="{{ route('admin.whatsapp.campaigns.create') }}"
                        class="mt-5 inline-flex items-center gap-2 bg-emerald-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-emerald-700 transition">
                        Criar campanha para iniciar contatos
                    </a>
                @else
                    Nenhuma conversa encontrada com os filtros atuais.
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $conversations->links() }}
    </div>
</x-layouts.app>