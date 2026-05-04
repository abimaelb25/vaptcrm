<x-layouts.app titulo="Campanha WhatsApp - VaptCRM">
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <a href="{{ route('admin.whatsapp.campaigns.index') }}"
                class="text-sm text-slate-500 hover:text-slate-700">← Campanhas</a>
            <div class="flex items-center gap-3 mt-2">
                <h1 class="text-2xl font-bold text-slate-800">{{ $campaign->nome }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $campaign->statusBadgeClass() }}">
                    {{ $campaign->humanStatus() }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Segmento: <span class="font-medium text-slate-700">{{ $campaign->segment_type }}</span>
                &nbsp;·&nbsp; Tipo: <span class="font-medium text-slate-700">{{ $campaign->message_type === 'manual_link' ? 'Link manual (wa.me)' : 'Template oficial' }}</span>
                &nbsp;·&nbsp; Criada em {{ $campaign->created_at->format('d/m/y \à\s H:i') }}
            </p>
        </div>
        @if(!in_array($campaign->status, ['done', 'cancelled']))
        <form method="POST" action="{{ route('admin.whatsapp.campaigns.cancel', $campaign) }}"
            onsubmit="return confirm('Cancelar esta campanha?')">
            @csrf
            <button type="submit"
                class="text-sm text-rose-600 border border-rose-200 bg-rose-50 hover:bg-rose-100 px-4 py-2 rounded-lg transition">
                Cancelar campanha
            </button>
        </form>
        @endif
    </div>

    @if(session('sucesso'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl px-4 py-3">
            {{ session('sucesso') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-xl px-4 py-3">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    {{-- Progress bar --}}
    @if($campaign->total_recipients > 0)
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between text-sm mb-2">
            <span class="font-medium text-slate-700">Progresso de envio</span>
            <span class="text-slate-500">
                {{ $campaign->sent_count }}/{{ $campaign->total_recipients }} enviados
                @if($campaign->failed_count > 0)
                    &nbsp;·&nbsp; <span class="text-rose-600">{{ $campaign->failed_count }} falhas</span>
                @endif
            </span>
        </div>
        @php $pct = round(($campaign->sent_count / $campaign->total_recipients) * 100); @endphp
        <div class="bg-slate-100 rounded-full h-2">
            <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
        </div>
        <p class="text-xs text-slate-400 mt-1">{{ $pct }}% concluído</p>
    </div>
    @endif

    {{-- Message preview --}}
    @if($campaign->manual_message)
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-2">Mensagem configurada</h3>
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 text-sm text-slate-700 whitespace-pre-wrap">{{ $campaign->manual_message }}</div>
        <p class="text-xs text-slate-400 mt-2">
            <code class="bg-slate-100 px-1 rounded">{{nome_cliente}}</code> será substituído pelo nome de cada destinatário.
        </p>
    </div>
    @endif

    {{-- Recipients table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">
                Destinatários ({{ $campaign->total_recipients }})
            </h3>
            @if($campaign->status === 'draft' || $campaign->status === 'running')
                <p class="text-xs text-slate-400">
                    Clique em "Abrir conversa" para enviar o link wa.me e, após enviar, marque como enviado.
                </p>
            @endif
        </div>

        @if($recipients->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-500">Nome / Telefone</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500">Status</th>
                        @if($campaign->message_type === 'manual_link')
                            <th class="text-center px-4 py-3 font-medium text-slate-500">Link</th>
                            <th class="px-4 py-3"></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recipients as $recipient)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $recipient->cliente?->nome ?? 'Cliente' }}</p>
                            <p class="text-xs font-mono text-slate-400">{{ $recipient->phone }}</p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $badgeCls = match($recipient->status) {
                                    'sent'    => 'bg-emerald-100 text-emerald-700',
                                    'failed'  => 'bg-rose-100 text-rose-700',
                                    'skipped' => 'bg-slate-100 text-slate-500',
                                    default   => 'bg-amber-100 text-amber-700',
                                };
                                $badgeTxt = match($recipient->status) {
                                    'sent'    => 'Enviado',
                                    'failed'  => 'Falha',
                                    'skipped' => 'Ignorado',
                                    default   => 'Pendente',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeCls }}">
                                {{ $badgeTxt }}
                            </span>
                            @if($recipient->sent_at)
                                <p class="text-xs text-slate-400 mt-0.5">{{ $recipient->sent_at->format('d/m H:i') }}</p>
                            @endif
                        </td>
                        @if($campaign->message_type === 'manual_link')
                        <td class="px-4 py-3 text-center">
                            @if($recipient->wa_me_link)
                                <a href="{{ $recipient->wa_me_link }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1.5 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.9.525 3.68 1.438 5.203L2.181 22l4.906-1.283A9.934 9.934 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z" opacity=".2"/></svg>
                                    Abrir conversa
                                </a>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($recipient->status === 'pending')
                                <form method="POST"
                                    action="{{ route('admin.whatsapp.campaigns.recipient.sent', [$campaign, $recipient]) }}">
                                    @csrf
                                    <button type="submit"
                                        class="text-xs text-slate-500 hover:text-emerald-700 hover:underline transition">
                                        ✓ Marcar enviado
                                    </button>
                                </form>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $recipients->links() }}
        </div>
        @else
            <p class="text-sm text-slate-400 text-center py-10">Nenhum destinatário com opt-in válido foi encontrado para este segmento.</p>
        @endif
    </div>

</div>
</x-layouts.app>
