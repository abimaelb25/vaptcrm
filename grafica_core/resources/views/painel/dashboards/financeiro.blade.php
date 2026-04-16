<x-layouts.app titulo="Dashboard Financeiro - {{ $configSite['empresa_nome'] ?? 'Gráfica' }}">

    <div class="mb-6">
        <h1 class="text-3xl font-black tracking-tight text-brand-secondary">Fluxo Financeiro</h1>
        <p class="text-sm text-slate-500 font-medium mt-0.5">Controladoria, recebíveis e contas a pagar</p>
    </div>

    {{-- Cards Financeiros --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <x-dashboard.card 
            titulo="A Receber (Hoje)" 
            valor="R$ {{ number_format($receber_hoje, 2, ',', '.') }}" 
            cor="blue" 
            icone='<path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />' 
        />
        
        <x-dashboard.card 
            titulo="Total em Atraso" 
            valor="R$ {{ number_format($atrasados_total, 2, ',', '.') }}" 
            cor="red" 
            icone='<path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />' 
        />

        <x-dashboard.card 
            titulo="Últimos Recebimentos" 
            valor="{{ $ultimos_pagamentos->count() }}" 
            cor="green" 
            icone='<path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />' 
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Listagem Financeira --}}
        <x-dashboard.list titulo="Últimas Movimentações" verTodosUrl="{{ route('admin.finance.index') }}">
            @forelse($ultimos_pagamentos as $item)
                <div class="flex items-center justify-between p-4 rounded-xl bg-white border border-slate-100 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg {{ $item->tipo == 'entrada' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center">
                            @if($item->tipo == 'entrada')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75" /></svg>
                            @else
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75" /></svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-800">{{ $item->descricao ?: 'Lançamento sem descrição' }}</p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $item->categoria }} • {{ $item->data_movimentacao->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-black {{ $item->tipo == 'entrada' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $item->tipo == 'entrada' ? '+' : '-' }} R$ {{ number_format($item->valor, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-10">
                    <p class="text-xs font-bold text-slate-300 italic">Nenhuma movimentação recente</p>
                </div>
            @endforelse
        </x-dashboard.list>

        {{-- Ações e Alertas Financeiros --}}
        <div class="space-y-6">
            <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest pl-1">Ações de Tesouraria</h2>
            
            <div class="grid grid-cols-2 gap-4">
                <x-dashboard.action titulo="Nova Entrada" url="{{ route('admin.finance.create', ['tipo' => 'entrada']) }}" cor="green" icone='<path d="M12 4.5v15m7.5-7.5h-15" />' />
                <x-dashboard.action titulo="Nova Saída" url="{{ route('admin.finance.create', ['tipo' => 'saida']) }}" cor="red" icone='<path d="M19.5 12h-15" />' />
            </div>

            <x-dashboard.alert 
                tipo="atencao" 
                titulo="Inadimplência Elevada" 
                mensagem="O volume de recebíveis em atraso superou R$ 500,00. Considere iniciar ações de cobrança."
            />

            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-6">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Conciliação Bancária</p>
                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-slate-200">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                        <span class="text-xs font-bold text-slate-600">Caixa Operacional</span>
                    </div>
                    <span class="text-xs font-black text-brand-secondary">Conectado</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
