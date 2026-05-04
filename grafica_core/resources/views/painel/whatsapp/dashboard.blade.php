<x-layouts.app titulo="Dashboard WhatsApp - VaptCRM">
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard WhatsApp</h1>
            <p class="text-sm text-slate-500 mt-1">Visão geral das mensagens e conversas no período selecionado</p>
        </div>
        <form method="GET" action="{{ route('admin.whatsapp.dashboard') }}" class="flex items-center gap-2">
            <input type="date" name="inicio" value="{{ $inicio }}"
                class="text-sm border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <span class="text-slate-400 text-sm">até</span>
            <input type="date" name="fim" value="{{ $fim }}"
                class="text-sm border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <button type="submit"
                class="bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-emerald-700 transition">
                Filtrar
            </button>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Enviadas</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">{{ number_format($metrics['sentInPeriod']) }}</p>
            <p class="text-xs text-slate-400 mt-1">mensagens no período</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Recebidas</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">{{ number_format($metrics['receivedInPeriod']) }}</p>
            <p class="text-xs text-slate-400 mt-1">mensagens no período</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Falhas</p>
            <p class="text-3xl font-bold {{ $metrics['failedInPeriod'] > 0 ? 'text-rose-600' : 'text-slate-800' }} mt-1">
                {{ number_format($metrics['failedInPeriod']) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">mensagens com erro</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Conversas abertas</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">{{ number_format($metrics['openConversations']) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $metrics['unreadConversations'] }} não lidas</p>
        </div>
    </div>

    {{-- Response metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Taxa de resposta</p>
            @php $rate = $metrics['responseRate']; @endphp
            <div class="flex items-end gap-2">
                <span class="text-2xl font-bold text-emerald-600">{{ $rate }}%</span>
                <span class="text-xs text-slate-400 pb-1">conversas respondidas</span>
            </div>
            <div class="mt-3 bg-slate-100 rounded-full h-2">
                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ min(100, $rate) }}%"></div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Tempo médio de 1ª resposta</p>
            <p class="text-2xl font-bold text-sky-600">
                {{ \App\Services\WhatsApp\WhatsAppDashboardService::formatSeconds($metrics['avgFirstReply']) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">da mensagem do cliente até 1ª resposta da loja</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Mensagens por origem</p>
            <div class="flex items-center gap-4 mt-2">
                <div class="text-center">
                    <p class="text-lg font-bold text-violet-600">{{ $metrics['automatedVsHuman']['automated'] }}</p>
                    <p class="text-xs text-slate-500">Automatizadas</p>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div class="text-center">
                    <p class="text-lg font-bold text-amber-600">{{ $metrics['automatedVsHuman']['human'] }}</p>
                    <p class="text-xs text-slate-500">Manuais</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Volume chart (HTML table as sparkline) --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Volume diário</h3>
        @if(count($metrics['dailyVolume']) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="text-slate-500 border-b border-slate-100">
                            <th class="text-left pb-2 pr-4 font-medium">Data</th>
                            <th class="text-right pb-2 pr-4 font-medium text-emerald-600">Enviadas</th>
                            <th class="text-right pb-2 font-medium text-sky-600">Recebidas</th>
                            <th class="pb-2 w-40"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @php
                            $maxVol = max(1, max(array_map(fn($d) => $d['sent'] + $d['received'], $metrics['dailyVolume'])));
                        @endphp
                        @foreach($metrics['dailyVolume'] as $day)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2 pr-4 font-medium text-slate-600">
                                {{ \Carbon\Carbon::parse($day['date'])->format('d/m') }}
                            </td>
                            <td class="py-2 pr-4 text-right text-emerald-700">{{ $day['sent'] }}</td>
                            <td class="py-2 text-right text-sky-700">{{ $day['received'] }}</td>
                            <td class="py-2 pl-4">
                                <div class="flex gap-0.5 h-4 items-end">
                                    @php $sentW = round(($day['sent'] / $maxVol) * 100); $recvW = round(($day['received'] / $maxVol) * 100); @endphp
                                    @if($day['sent'] > 0)
                                        <div class="bg-emerald-400 rounded-sm" style="width: {{ max(2, $sentW) }}%; height: 100%;"></div>
                                    @endif
                                    @if($day['received'] > 0)
                                        <div class="bg-sky-400 rounded-sm" style="width: {{ max(2, $recvW) }}%; height: 100%;"></div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-slate-400 text-center py-6">Nenhuma mensagem no período selecionado.</p>
        @endif
    </div>

    {{-- Failure breakdown --}}
    @if(!empty($metrics['failureBreakdown']) && array_sum($metrics['failureBreakdown']) > 0)
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Motivos de falha</h3>
        <table class="min-w-full text-sm">
            <tbody class="divide-y divide-slate-100">
                @foreach($metrics['failureBreakdown'] as $reason => $count)
                @if($count > 0)
                <tr>
                    <td class="py-2 text-slate-600">{{ $reason }}</td>
                    <td class="py-2 text-right font-semibold text-rose-600">{{ $count }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Recent webhook events --}}
    @if(!empty($metrics['webhookEvents']) && count($metrics['webhookEvents']) > 0)
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-700">Eventos recentes (webhook)</h3>
            <a href="{{ route('admin.whatsapp.logs') }}" class="text-xs text-emerald-600 hover:underline">Ver todos →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-100">
                        <th class="text-left pb-2 pr-4 font-medium">Tipo</th>
                        <th class="text-left pb-2 pr-4 font-medium">Telefone</th>
                        <th class="text-left pb-2 font-medium">Data/Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($metrics['webhookEvents'] as $event)
                    <tr class="hover:bg-slate-50">
                        <td class="py-2 pr-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">
                                {{ $event->event_type ?? '—' }}
                            </span>
                        </td>
                        <td class="py-2 pr-4 font-mono text-slate-600">{{ $event->from_phone ?? '—' }}</td>
                        <td class="py-2 text-slate-500">{{ $event->created_at->format('d/m H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Quick nav --}}
    <div class="flex gap-3 flex-wrap">
        <a href="{{ route('admin.whatsapp.index') }}"
            class="inline-flex items-center gap-2 text-sm text-slate-600 bg-white border border-slate-200 px-4 py-2 rounded-lg hover:bg-slate-50 transition">
            ← Configurações
        </a>
        <a href="{{ route('admin.whatsapp.page.inbox') }}"
            class="inline-flex items-center gap-2 text-sm text-slate-600 bg-white border border-slate-200 px-4 py-2 rounded-lg hover:bg-slate-50 transition">
            Caixa de entrada
        </a>
        <a href="{{ route('admin.whatsapp.campaigns.index') }}"
            class="inline-flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-lg hover:bg-emerald-100 transition">
            Campanhas
        </a>
    </div>

</div>
</x-layouts.app>
