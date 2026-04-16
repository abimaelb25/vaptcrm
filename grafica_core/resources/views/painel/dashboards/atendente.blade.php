<x-layouts.app titulo="Meus Pedidos - {{ $configSite['empresa_nome'] ?? 'Gráfica' }}">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-brand-secondary">Meu Trabalho</h1>
            <p class="text-sm text-slate-500 font-medium mt-0.5">Gestão de atendimentos e vendas pessoais</p>
        </div>
        <div class="flex items-center gap-3">
            @if(!empty($widgets_adicionais['financeiro_mini']))
                <x-dashboard.action titulo="Meu Caixa" url="{{ route('admin.caixas.index') }}" cor="emerald" icone='<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />' />
            @endif
            <x-dashboard.action titulo="Abrir PDV" url="{{ route('admin.pos.index') }}" cor="secondary" icone='<path d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />' />
            <x-dashboard.action titulo="Novo Pedido" url="{{ route('admin.sales.pedidos.create') }}" icone='<path d="M12 4.5v15m7.5-7.5h-15" />' />
        </div>
    </div>

    {{-- Cards de Desempenho Pessoal --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <x-dashboard.card 
            titulo="Meus Pedidos (Hoje)" 
            valor="{{ $meus_pedidos_hoje }}" 
            cor="blue" 
            icone='<path d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />' 
        />
        
        <x-dashboard.card 
            titulo="Minha Venda (Hoje)" 
            valor="R$ {{ number_format($minha_venda_dia, 2, ',', '.') }}" 
            cor="green" 
            icone='<path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />' 
        />

        <x-dashboard.card 
            titulo="Aguardando Ação" 
            valor="{{ $fila_acao->count() }}" 
            cor="amber" 
            icone='<path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />' 
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Minha Fila de Trabalho --}}
        <x-dashboard.list titulo="Minha Fila de Trabalho" verTodosUrl="{{ route('admin.sales.pedidos.index') }}">
            @forelse($fila_acao as $item)
                <a href="{{ route('admin.sales.pedidos.show', $item) }}" class="flex items-center justify-between p-4 rounded-xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg bg-brand-primary/5 text-brand-primary flex items-center justify-center font-black text-xs">
                            PED
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-800">#{{ str_pad((string)$item->id, 4, '0', STR_PAD_LEFT) }} - {{ $item->cliente->nome ?? 'Cliente Final' }}</p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $item->status }} • {{ $item->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-black text-slate-700">R$ {{ number_format($item->total, 2, ',', '.') }}</p>
                    </div>
                </a>
            @empty
                <div class="flex flex-col items-center justify-center py-10">
                    <p class="text-xs font-bold text-slate-300 italic">Nenhum pedido aguardando ação</p>
                </div>
            @endforelse
        </x-dashboard.list>

        {{-- Alertas do Atendente --}}
        <div class="space-y-6">
            <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest pl-1">Avisos Importantes</h2>
            
            <x-dashboard.alert 
                tipo="info" 
                titulo="Pesquise seus Clientes" 
                mensagem="Utilize a barra de busca antes de criar um novo pedido para evitar duplicidade de cadastros."
            />

            <x-dashboard.alert 
                tipo="atencao" 
                titulo="Pedidos em Rascunho" 
                mensagem="Você possui pedidos em rascunho que precisam ser finalizados ou excluídos para não poluir o sistema."
            />

            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-6">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Busca Rápida de Clientes</p>
                <form action="{{ route('admin.sales.clientes.index') }}" method="GET" class="relative">
                    <input type="text" name="busca" placeholder="Nome, E-mail ou CPF..." class="w-full rounded-xl border-slate-200 pl-4 py-2.5 text-sm focus:border-brand-primary focus:ring-0">
                    <button type="submit" class="absolute right-3 top-2.5 text-slate-300 hover:text-brand-primary">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
