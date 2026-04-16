<x-layouts.app titulo="Fila de Produção - {{ $configSite['empresa_nome'] ?? 'Gráfica' }}">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-brand-secondary">Fila de Produção</h1>
            <p class="text-sm text-slate-500 font-medium mt-0.5">Execução e controle de prazos industriais</p>
        </div>
        <div class="flex items-center gap-3">
            <x-dashboard.action titulo="Ver Quadro Geral" url="{{ route('admin.ops.tasks.board') }}" cor="secondary" icone='<path d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />' />
        </div>
    </div>

    {{-- Cards de Produção --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <x-dashboard.card 
            titulo="Fila Prioritária" 
            valor="{{ $fila_prioritaria->count() }}" 
            cor="amber" 
            icone='<path d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.96 5.96m0 0V22.5L9 21l-1.5 1.5V18.375M12 12h.008v.008H12V12z" />' 
        />
        
        <x-dashboard.card 
            titulo="Entregas de Hoje" 
            valor="{{ $entregas_hoje }}" 
            cor="green" 
            icone='<path d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H18.375m-17.25 0h17.25M6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0-6h.008v.008H6.75V9z" />' 
        />

        <x-dashboard.card 
            titulo="Minhas Tarefas" 
            valor="{{ $minhas_tarefas->count() }}" 
            cor="blue" 
            icone='<path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />' 
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Fila Operacional --}}
        <x-dashboard.list titulo="Pedidos para Produzir" verTodosUrl="{{ route('admin.ops.tasks.board') }}">
            @forelse($fila_prioritaria as $item)
                <div class="p-4 rounded-xl bg-white border-l-4 border-l-brand-primary shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-black text-slate-800">PED-{{ str_pad((string)$item->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[10px] font-black uppercase {{ $item->prazo_entrega?->isPast() ? 'text-rose-600' : 'text-slate-400' }}">
                            Prazo: {{ $item->prazo_entrega ? $item->prazo_entrega->format('d/m/Y H:i') : 'Não definido' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-600 truncate mb-1">{{ $item->cliente->nome ?? 'Cliente Final' }}</p>
                            <div class="flex gap-1">
                                @foreach($item->itens as $subItem)
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[8px] font-bold text-slate-500 uppercase">{{ $subItem->descricao_item }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.sales.pedidos.show', $item) }}" class="p-2 rounded-lg bg-slate-100 text-slate-400 hover:text-brand-primary transition-colors" title="Ver Pedido">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-10">
                    <p class="text-xs font-bold text-slate-300 italic">Nenhum pedido na fila de produção</p>
                </div>
            @endforelse
        </x-dashboard.list>

        {{-- Minhas Tarefas / Alertas --}}
        <div class="space-y-6">
            <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest pl-1">Minhas Tarefas</h2>
            
            <x-dashboard.list titulo="Tarefas Atribuídas">
                @forelse($minhas_tarefas as $item)
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex items-center gap-3">
                        <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-primary focus:ring-brand-primary">
                        <div class="flex-1">
                            <p class="text-xs font-bold text-slate-700">{{ $item->titulo }}</p>
                            <p class="text-[10px] text-slate-400">{{ $item->setor ?? 'Geral' }} • {{ $item->status }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10">
                        <p class="text-xs font-bold text-slate-300 italic">Você não possui tarefas pendentes</p>
                    </div>
                @endforelse
            </x-dashboard.list>

            <x-dashboard.alert 
                tipo="atencao" 
                titulo="Organização de Arquivos" 
                mensagem="Lembre-se de anexar a arte finalizada ao pedido antes de enviá-lo para finalização."
            />
        </div>
    </div>
</x-layouts.app>
