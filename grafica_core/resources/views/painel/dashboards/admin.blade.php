<x-layouts.app titulo="Dashboard Admin - {{ $configSite['empresa_nome'] ?? 'Gráfica' }}">

    <div class="mb-6">
        <h1 class="text-3xl font-black tracking-tight text-brand-secondary">Visão Estratégica</h1>
        <p class="text-sm text-slate-500 font-medium mt-0.5">Métricas de crescimento e saúde financeira</p>
    </div>

    {{-- Cards Principais --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-dashboard.card 
            titulo="Faturamento (Mês)" 
            valor="R$ {{ number_format($faturamento_mes, 2, ',', '.') }}" 
            cor="green" 
            icone='<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />' 
        />
        
        <x-dashboard.card 
            titulo="Novos Clientes" 
            valor="{{ $novos_clientes_mes }}" 
            cor="blue" 
            icone='<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />' 
        />

        <x-dashboard.card 
            titulo="Ticket Médio" 
            valor="R$ {{ number_format($ticket_medio, 2, ',', '.') }}" 
            cor="purple" 
            icone='<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.1l1.24.394c.49.156.99-.216.99-.73V5.413c0-.422-.321-.778-.737-.822a61.488 61.488 0 00-11.603-1.042l-1.24.394a1.05 1.05 0 00-.737.957V18.75z" />' 
        />

        <x-dashboard.card 
            titulo="Pedidos Hoje" 
            valor="{{ $pedidos_hoje }}" 
            cor="amber" 
            icone='<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />' 
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Coluna de Alertas e Insights --}}
        <div class="lg:col-span-1 space-y-6">
            <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest pl-1">Insights & Riscos</h2>
            
            @if($alertas_risco['inadimplencia'] > 0)
                <x-dashboard.alert 
                    tipo="erro" 
                    titulo="Inadimplência Detectada" 
                    mensagem="Existem {{ $alertas_risco['inadimplencia'] }} pagamentos pendentes com data vencida." 
                    link="{{ route('admin.finance.index') }}"
                />
            @endif

            <x-dashboard.alert 
                tipo="info" 
                titulo="Dica Estratégica" 
                mensagem="Seu ticket médio está em R$ {{ number_format($ticket_medio, 2) }}. Que tal criar combos para aumentar esse valor?"
            />

            {{-- Resumo da Assinatura SaaS --}}
            <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-6">
                <p class="text-xs font-black text-blue-800 uppercase tracking-wider mb-2">Plano Atual</p>
                <div class="flex items-center justify-between">
                    <span class="text-xl font-black text-blue-900">{{ $assinatura->plano->nome ?? 'SaaS Gratuito' }}</span>
                    @if($assinatura->trial_ends_at)
                        <span class="text-[10px] font-bold text-blue-600">Expira em {{ $assinatura->trial_ends_at->format('d/m/Y') }}</span>
                    @endif
                </div>
                <a href="{{ route('admin.billing.index') }}" class="mt-4 block text-center rounded-xl bg-blue-600 py-2 text-xs font-black text-white hover:bg-blue-700 transition-colors">Gerenciar Assinatura</a>
            </div>
        </div>

        {{-- Coluna de Ações Rápidas e Resumo Operacional --}}
        <div class="lg:col-span-2 space-y-6">
            <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest pl-1">Ações Administrativas</h2>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <x-dashboard.action 
                    titulo="Relatórios" 
                    url="{{ route('admin.bi.index') }}" 
                    icone='<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />' 
                />
                <x-dashboard.action 
                    titulo="Configurações" 
                    url="{{ route('admin.system.config.index') }}" 
                    cor="secondary"
                    icone='<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />' 
                />
                <x-dashboard.action 
                    titulo="Minha Equipe" 
                    url="{{ route('admin.system.equipe.index') }}" 
                    cor="slate"
                    icone='<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />' 
                />
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-8 text-center">
                <p class="text-sm font-bold text-slate-400">Gráficos e Análises Avançadas</p>
                <p class="text-xs text-slate-300 mt-1">Disponível em breve na Fase 2 do Dashboard.</p>
            </div>
        </div>
    </div>
</x-layouts.app>
