<x-layouts.app titulo="Campanhas WhatsApp - VaptCRM">
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Campanhas WhatsApp</h1>
            <p class="text-sm text-slate-500 mt-1">Envie mensagens segmentadas para grupos de clientes</p>
        </div>
        <a href="{{ route('admin.whatsapp.campaigns.create') }}"
            class="inline-flex items-center gap-2 bg-emerald-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-emerald-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nova campanha
        </a>
    </div>

    @if(session('sucesso'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl px-4 py-3">
            {{ session('sucesso') }}
        </div>
    @endif

    {{-- Campaign list --}}
    @if($campaigns->count() > 0)
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-500">Nome</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-500 hidden md:table-cell">Segmento</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500 hidden md:table-cell">Destinatários</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500 hidden md:table-cell">Enviados</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-500">Criada em</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($campaigns as $campaign)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $campaign->nome }}</td>
                        <td class="px-4 py-3 text-slate-500 hidden md:table-cell">
                            {{ $segmentOptions[$campaign->segment_type] ?? $campaign->segment_type }}
                        </td>
                        <td class="px-4 py-3 text-center text-slate-600 hidden md:table-cell">
                            {{ number_format($campaign->total_recipients) }}
                        </td>
                        <td class="px-4 py-3 text-center hidden md:table-cell">
                            @if($campaign->total_recipients > 0)
                                <span class="text-emerald-700 font-medium">{{ $campaign->sent_count }}</span>
                                @if($campaign->failed_count > 0)
                                    <span class="text-rose-500 text-xs ml-1">({{ $campaign->failed_count }} falhas)</span>
                                @endif
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $campaign->statusBadgeClass() }}">
                                {{ $campaign->humanStatus() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-slate-500 text-xs">
                            {{ $campaign->created_at->format('d/m/y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.whatsapp.campaigns.show', $campaign) }}"
                                class="text-xs text-emerald-600 hover:underline font-medium">Ver →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $campaigns->links() }}
            </div>
        </div>
    @else
        {{-- Empty state --}}
        <div class="bg-white rounded-xl border border-slate-200 border-dashed px-8 py-16 text-center">
            <div class="mx-auto w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-slate-800">Nenhuma campanha criada</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-xs mx-auto">
                Crie sua primeira campanha para alcançar segmentos de clientes via WhatsApp.
            </p>
            <a href="{{ route('admin.whatsapp.campaigns.create') }}"
                class="mt-5 inline-flex items-center gap-2 bg-emerald-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-emerald-700 transition">
                Criar primeira campanha
            </a>
        </div>
    @endif

    {{-- Nav --}}
    <div class="flex gap-3 flex-wrap">
        <a href="{{ route('admin.whatsapp.index') }}"
            class="text-sm text-slate-600 bg-white border border-slate-200 px-4 py-2 rounded-lg hover:bg-slate-50 transition">
            ← Configurações
        </a>
        <a href="{{ route('admin.whatsapp.dashboard') }}"
            class="text-sm text-slate-600 bg-white border border-slate-200 px-4 py-2 rounded-lg hover:bg-slate-50 transition">
            Dashboard
        </a>
    </div>

</div>
</x-layouts.app>
