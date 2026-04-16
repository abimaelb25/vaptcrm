<x-layouts.app titulo="Dashboard Gerente - {{ $configSite['empresa_nome'] ?? 'Gráfica' }}">

    <div class="mb-6">
        <h1 class="text-3xl font-black tracking-tight text-brand-secondary">Controle da Loja</h1>
        <p class="text-sm text-slate-500 font-medium mt-0.5">Visão operacional e gestão de equipe</p>
    </div>

    {{-- Cards Operacionais --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-dashboard.card 
            titulo="Pedidos do Dia" 
            valor="{{ $pedidos_dia }}" 
            cor="blue" 
            icone='<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />' 
        />
        
        <x-dashboard.card 
            titulo="Faturamento (Hoje)" 
            valor="R$ {{ number_format($faturamento_dia, 2, ',', '.') }}" 
            cor="green" 
            icone='<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />' 
        />

        <x-dashboard.card 
            titulo="Atrasos Críticos" 
            valor="{{ $atrasos_criticos }}" 
            cor="red" 
            icone='<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />' 
        />

        <x-dashboard.card 
            titulo="Em Produção" 
            valor="{{ $pedidos_por_status['em_producao'] ?? 0 }}" 
            cor="cyan" 
            icone='<path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.96 5.96m0 0V22.5L9 21l-1.5 1.5V18.375M12 12h.008v.008H12V12z" />' 
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        {{-- Ranking de Equipe --}}
        <x-dashboard.list titulo="Ranking de Vendas (Mês)" verTodosUrl="{{ route('admin.bi.index') }}">
            @forelse($ranking_vendas as $item)
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-brand-primary text-white flex items-center justify-center font-bold text-xs uppercase">
                            {{ substr($item->atendente->nome ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-800">{{ $item->atendente->nome ?? 'Vendedor Externo' }}</p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $item->qtd_pedidos }} pedidos</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-black text-brand-secondary">R$ {{ number_format($item->total_vendas, 2, ',', '.') }}</p>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-10">
                    <p class="text-xs font-bold text-slate-300 italic">Nenhum dado de venda disponível</p>
                </div>
            @endforelse
        </x-dashboard.list>

        {{-- Alertas e Gargalos --}}
        <div class="space-y-6">
            <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest pl-1">Alertas da Equipe</h2>
            
            @if($atrasos_criticos > 0)
                <x-dashboard.alert 
                    tipo="erro" 
                    titulo="Gargalo na Entrega" 
                    mensagem="Existem {{ $atrasos_criticos }} pedidos com prazo estourado que ainda não foram entregues." 
                    link="{{ route('admin.sales.pedidos.index', ['status' => 'atrasados']) }}"
                />
            @endif

            <x-dashboard.alert 
                tipo="atencao" 
                titulo="Pedidos Aguardando Ação" 
                mensagem="Existem {{ $pedidos_por_status['aguardando_aprovacao'] ?? 0 }} pedidos aguardando aprovação financeira ou do cliente."
            />

            <div class="grid grid-cols-2 gap-4">
                <x-dashboard.action titulo="Ver Equipe" url="{{ route('admin.system.equipe.index') }}" cor="secondary" icone='<path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />' />
                <x-dashboard.action titulo="Novo Pedido" url="{{ route('admin.sales.pedidos.create') }}" icone='<path d="M12 4.5v15m7.5-7.5h-15" />' />
            </div>
        </div>
    </div>
</x-layouts.app>
