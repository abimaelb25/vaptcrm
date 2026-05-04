<x-layouts.app titulo="Logs WhatsApp - VaptCRM">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">Histórico do WhatsApp</h1>
            <p class="text-slate-500 mt-1 font-medium">Veja o que foi enviado, o que falhou e o que os clientes responderam em linguagem simples.</p>
        </div>
        <a href="{{ route('admin.whatsapp.index') }}" class="px-5 py-3 rounded-2xl bg-white border border-slate-200 font-black text-slate-700 shadow-sm hover:border-brand-primary transition-colors">Voltar às configurações</a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <form method="GET" action="{{ route('admin.whatsapp.logs') }}" class="p-6 flex flex-col gap-4 md:flex-row md:items-end">
            <div>
                <label class="block text-sm font-black text-slate-700 mb-1">Filtrar por situação</label>
                <select name="message_status" class="rounded-xl border-slate-200 focus:border-brand-primary min-w-[220px]">
                    <option value="all" @selected($messageStatus === 'all')>Todos</option>
                    <option value="pending" @selected($messageStatus === 'pending')>Aguardando envio</option>
                    <option value="sent" @selected($messageStatus === 'sent')>Enviado</option>
                    <option value="delivered" @selected($messageStatus === 'delivered')>Entregue</option>
                    <option value="read" @selected($messageStatus === 'read')>Lido</option>
                    <option value="failed" @selected($messageStatus === 'failed')>Falhou</option>
                    <option value="received" @selected($messageStatus === 'received')>Recebido do cliente</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-3 rounded-2xl bg-slate-900 text-white font-black hover:bg-black transition-colors">Filtrar</button>
        </form>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h2 class="text-xl font-black text-slate-800">Ações de mensagem</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wide">
                        <tr>
                            <th class="text-left px-4 py-3">Quando</th>
                            <th class="text-left px-4 py-3">Resumo</th>
                            <th class="text-left px-4 py-3">Situação</th>
                            <th class="text-left px-4 py-3">Detalhe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            @php
                                $friendlyStatus = match($message->status) {
                                    'pending' => 'Aguardando envio',
                                    'sent' => 'Enviado',
                                    'delivered' => 'Entregue',
                                    'read' => 'Lido',
                                    'failed' => 'Falhou',
                                    'received' => 'Recebido',
                                    default => ucfirst((string) $message->status),
                                };
                                $friendlyError = '-';
                                if (!empty($message->error_message)) {
                                    $rawError = mb_strtolower((string) $message->error_message);
                                    if (str_contains($rawError, 'invalid') || str_contains($rawError, 'number')) {
                                        $friendlyError = 'Falha: número inválido.';
                                    } elseif (str_contains($rawError, 'opt-in')) {
                                        $friendlyError = 'Falha: cliente sem autorização de recebimento.';
                                    } elseif (str_contains($rawError, '24h')) {
                                        $friendlyError = 'Falha: fora da janela de 24h, use template.';
                                    } else {
                                        $friendlyError = 'Falha: não foi possível concluir o envio.';
                                    }
                                }
                                $summary = $message->isInbound()
                                    ? 'Mensagem recebida do cliente'
                                    : ('Mensagem enviada para ' . ($message->conversation?->contact_name ?: $message->conversation?->contact_phone ?: 'cliente'));
                            @endphp
                            <tr class="border-t border-slate-100 align-top">
                                <td class="px-4 py-4 font-bold text-slate-700">{{ $message->created_at->format('d/m H:i') }}</td>
                                <td class="px-4 py-4 text-slate-700 font-semibold">{{ $summary }}
                                    <span class="block text-[11px] font-black {{ $message->is_automated ? 'text-amber-600' : 'text-sky-600' }}">{{ $message->is_automated ? 'Automática' : 'Humana' }}</span>
                                </td>
                                <td class="px-4 py-4 font-bold text-slate-700">{{ $friendlyStatus }}</td>
                                <td class="px-4 py-4 text-rose-600 text-xs font-semibold">{{ $friendlyError }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">Nenhuma mensagem encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100">{{ $messages->links() }}</div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h2 class="text-xl font-black text-slate-800">Eventos recebidos da Meta</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wide">
                        <tr>
                            <th class="text-left px-4 py-3">Quando</th>
                            <th class="text-left px-4 py-3">O que aconteceu</th>
                            <th class="text-left px-4 py-3">Processamento</th>
                            <th class="text-left px-4 py-3">Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($webhookEvents as $event)
                            @php
                                $eventLabel = match($event->event_type) {
                                    'message' => 'Mensagem recebida do cliente',
                                    'status' => 'Atualização de status de entrega/leitura',
                                    default => 'Evento de webhook recebido',
                                };
                                $processingLabel = match($event->processing_status) {
                                    'processed' => 'Concluído',
                                    'failed' => 'Falhou',
                                    'pending' => 'Pendente',
                                    default => 'Ignorado',
                                };
                            @endphp
                            <tr class="border-t border-slate-100 align-top">
                                <td class="px-4 py-4 font-bold text-slate-700">{{ $event->created_at->format('d/m H:i') }}</td>
                                <td class="px-4 py-4 text-slate-700 font-semibold">{{ $eventLabel }}</td>
                                <td class="px-4 py-4 font-bold text-slate-700">{{ $processingLabel }}</td>
                                <td class="px-4 py-4 text-rose-600 text-xs font-semibold">{{ $event->processing_error ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">Nenhum webhook encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100">{{ $webhookEvents->links() }}</div>
        </div>
    </div>
</x-layouts.app>