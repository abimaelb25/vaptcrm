<x-layouts.app titulo="Conversa WhatsApp - VaptCRM">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">{{ $conversation->contact_name ?: $conversation->contact_phone }}</h1>
            <p class="text-slate-500 mt-1 font-medium">{{ $conversation->contact_phone }} · {{ $conversation->pedido?->numero_exibicao ?: 'Sem pedido vinculado' }}</p>
        </div>
        <a href="{{ route('admin.whatsapp.page.inbox') }}" class="px-5 py-3 rounded-2xl bg-white border border-slate-200 font-black text-slate-700 shadow-sm hover:border-brand-primary transition-colors">Voltar à inbox</a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1.7fr_0.9fr] gap-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-800">Histórico</h2>
                    <p class="text-sm text-slate-500">Diferencie mensagens humanas e automáticas no contexto operacional.</p>
                </div>
                <form method="GET" action="{{ route('admin.whatsapp.page.conversation', $conversation) }}">
                    <select name="message_origin" onchange="this.form.submit()" class="rounded-xl border-slate-200 focus:border-brand-primary text-sm font-bold">
                        <option value="all" @selected($messageOrigin === 'all')>Todas</option>
                        <option value="human" @selected($messageOrigin === 'human')>Somente humanas</option>
                        <option value="automated" @selected($messageOrigin === 'automated')>Somente automáticas</option>
                    </select>
                </form>
            </div>

            <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto bg-slate-50/70">
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
                    @endphp
                    <div class="{{ $message->isOutbound() ? 'ml-auto bg-brand-primary text-white' : 'mr-auto bg-white text-slate-800' }} max-w-2xl rounded-3xl border {{ $message->isOutbound() ? 'border-brand-primary' : 'border-slate-200' }} px-5 py-4 shadow-sm">
                        <div class="flex items-center gap-2 flex-wrap mb-2 text-[11px] font-black uppercase tracking-wide {{ $message->isOutbound() ? 'text-white/80' : 'text-slate-400' }}">
                            <span>{{ $message->direction }}</span>
                            <span>•</span>
                            <span>{{ $friendlyStatus }}</span>
                            @if($message->is_automated)
                                <span>•</span>
                                <span>Automática</span>
                            @else
                                <span>•</span>
                                <span>Humana</span>
                            @endif
                        </div>
                        <p class="text-sm leading-relaxed">{{ $message->body ?: 'Mensagem sem corpo textual.' }}</p>
                        @if($message->error_message)
                            <p class="mt-3 text-xs font-bold {{ $message->isOutbound() ? 'text-rose-100' : 'text-rose-600' }}">Falha: verifique número, opt-in e janela de 24h para este envio.</p>
                        @endif
                        <p class="mt-3 text-[11px] font-bold {{ $message->isOutbound() ? 'text-white/70' : 'text-slate-400' }}">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-slate-500">Nenhuma mensagem encontrada neste filtro.</div>
                @endforelse
            </div>

            <div class="px-6 py-4 border-t border-slate-100 bg-white">
                {{ $messages->links() }}
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-xl font-black text-slate-800 mb-4">Resumo da conversa</h2>
                <div class="space-y-4 text-sm">
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
                        <p class="text-slate-400 font-black uppercase text-[10px]">Janela 24h</p>
                        <p class="font-bold text-slate-700">{{ $conversation->isWithinServiceWindow() ? 'Aberta' : 'Fechada' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 font-black uppercase text-[10px]">Prioridade</p>
                        <form method="POST" action="{{ route('admin.whatsapp.inbox.priority', $conversation) }}" class="mt-1">
                            @csrf
                            @method('PATCH')
                            <select name="priority" onchange="this.form.submit()"
                                class="text-sm rounded-xl border-slate-200 focus:border-brand-primary font-semibold">
                                @foreach(['low' => 'Baixa', 'normal' => 'Normal', 'high' => 'Alta', 'urgent' => 'Urgente'] as $val => $label)
                                    <option value="{{ $val }}" {{ ($conversation->priority ?? 'normal') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    @if($conversation->origin_source)
                    <div>
                        <p class="text-slate-400 font-black uppercase text-[10px]">Origem</p>
                        <p class="font-bold text-slate-700">{{ $conversation->origin_source }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Internal notes --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-xl font-black text-slate-800 mb-4">Notas internas</h2>
                @php $notes = $conversation->notes()->with('user')->orderBy('created_at')->get(); @endphp
                @if($notes->count() > 0)
                    <div class="space-y-3 mb-4 max-h-48 overflow-y-auto">
                        @foreach($notes as $note)
                        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                            <p class="text-xs font-bold text-amber-700 mb-1">
                                {{ $note->user?->nome ?? 'Usuário' }} · {{ $note->created_at->format('d/m H:i') }}
                            </p>
                            <p class="text-sm text-slate-700">{{ $note->note }}</p>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400 mb-4">Nenhuma nota ainda.</p>
                @endif

                <form method="POST" action="{{ route('admin.whatsapp.page.conversation.note.store', $conversation) }}">
                    @csrf
                    <textarea name="note" rows="2" maxlength="2000"
                        class="w-full text-sm rounded-xl border-slate-200 focus:border-brand-primary resize-none"
                        placeholder="Adicionar nota interna (visível só para a equipe)..."></textarea>
                    <button type="submit"
                        class="mt-2 w-full py-2.5 rounded-xl bg-amber-500 text-white text-sm font-black hover:bg-amber-600 transition">
                        Salvar nota
                    </button>
                </form>
            </div>

            {{-- AI placeholder --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 opacity-75">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xl font-black text-slate-800">IA — Sugestões</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-700">
                        Em breve
                    </span>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-4 text-center">
                    <p class="text-sm text-slate-400">A IA vai sugerir respostas baseadas no histórico da conversa e no perfil do cliente.</p>
                    <p class="text-xs text-slate-300 mt-1">Disponível na próxima versão do módulo.</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-xl font-black text-slate-800 mb-4">Responder</h2>
                <form action="{{ route('admin.whatsapp.page.conversation.send', $conversation) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Tipo</label>
                        <select name="type" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                            <option value="text">Texto livre (janela 24h)</option>
                            <option value="template">Template aprovado</option>
                        </select>
                        <p class="text-xs text-slate-400 mt-2">Se a janela de 24h estiver fechada, use Template aprovado.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Texto</label>
                        <textarea name="body" rows="5" class="w-full rounded-xl border-slate-200 focus:border-brand-primary" placeholder="Mensagem humana para atendimento"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Template</label>
                        <select name="template_name" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                            <option value="">Selecionar template</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->name }}">{{ $template->name }} ({{ $template->language }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Idioma do template</label>
                        <input type="text" name="language" value="pt_BR" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                    </div>
                    <button type="submit" class="w-full py-4 rounded-2xl bg-brand-primary text-white font-black shadow-sm hover:opacity-90">Enviar para fila</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>