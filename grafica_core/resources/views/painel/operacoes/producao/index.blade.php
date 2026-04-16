{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:15
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Monitor de <span class="text-brand-primary">Produção</span></h1>
            <p class="text-slate-500 font-medium">Controle operacional e progresso de pedidos no chão de fábrica.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.ops.production.settings') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50 flex items-center gap-2">
                <span>⚙️</span> Fluxos Padrão
            </a>
        </div>
    </div>

    <!-- Filtros de Chão de Fábrica -->
    <div class="mb-8 rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
        <form class="flex flex-wrap items-center gap-6">
            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-black text-slate-400 uppercase">Status</label>
                <select name="status" class="rounded-lg border-slate-200 text-sm focus:ring-brand-primary min-w-[180px]">
                    <option value="">Ativos (Pendentes/Em curso)</option>
                    <option value="aguardando" {{ request('status') == 'aguardando' ? 'selected' : '' }}>Aguardando Início</option>
                    <option value="em_producao" {{ request('status') == 'em_producao' ? 'selected' : '' }}>Em Produção</option>
                    <option value="pausado" {{ request('status') == 'pausado' ? 'selected' : '' }}>Pausado</option>
                    <option value="finalizado" {{ request('status') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-black text-slate-400 uppercase">Prioridade</label>
                <select name="prioridade" class="rounded-lg border-slate-200 text-sm focus:ring-brand-primary min-w-[150px]">
                    <option value="">Todas</option>
                    <option value="baixa">Baixa</option>
                    <option value="media">Média</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>

            <div class="flex items-end h-[52px]">
                <button type="submit" class="rounded-lg bg-slate-800 px-6 py-2.5 text-sm font-bold text-white hover:bg-slate-700 transition">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- Visão de Cards Operacionais -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($orders as $po)
            <div class="rounded-3xl bg-white p-6 shadow-xl border border-slate-100 flex flex-col justify-between transition hover:-translate-y-1 hover:shadow-2xl relative overflow-hidden">
                <!-- Indicador de Status/Prioridade -->
                <div class="absolute top-0 right-0 left-0 h-1.5 flex">
                    <div class="flex-1 {{ $po->prioridade === 'urgente' ? 'bg-red-500' : ($po->prioridade === 'alta' ? 'bg-orange-500' : 'bg-slate-200') }}"></div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Pedido #{{ $po->pedido->numero }}</span>
                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase 
                            {{ $po->status === 'finalizado' ? 'bg-emerald-50 text-emerald-600' : ($po->status === 'pausado' ? 'bg-amber-50 text-amber-600' : 'bg-brand-primary/10 text-brand-primary') }}">
                            {{ str_replace('_', ' ', $po->status) }}
                        </span>
                    </div>

                    <h2 class="text-xl font-black text-slate-800 mb-1">{{ $po->pedido->cliente->nome }}</h2>
                    <p class="text-xs font-bold text-slate-400 mb-6 flex items-center gap-1">
                        <span>📅 Previsão:</span>
                        <span class="{{ $po->data_previsao && $po->data_previsao->isPast() ? 'text-red-500 underline' : 'text-slate-600' }}">
                            {{ $po->data_previsao ? $po->data_previsao->format('d/m/Y') : 'Não definida' }}
                        </span>
                    </p>

                    <!-- Progresso Visual -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-black text-slate-400 uppercase">Evolução</span>
                            <span class="text-[10px] font-bold text-brand-primary">{{ $po->progresso }}%</span>
                        </div>
                        <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-brand-primary transition-all duration-700" style="width: {{ $po->progresso }}%"></div>
                        </div>
                    </div>

                    <!-- Etapas Compactas -->
                    <div class="space-y-3 mb-8">
                        @foreach($po->stages as $stage)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $stage->status === 'concluido' ? 'bg-emerald-500' : ($stage->status === 'em_andamento' ? 'bg-brand-primary animate-pulse' : 'bg-slate-200') }}"></span>
                                    <span class="text-xs font-bold {{ $stage->status === 'concluido' ? 'text-slate-400 line-through' : 'text-slate-700' }}">
                                        {{ $stage->stepDefinition->nome }}
                                    </span>
                                </div>
                                @if($stage->status === 'em_andamento')
                                    <span class="text-[8px] font-black bg-brand-primary/10 text-brand-primary px-1.5 py-0.5 rounded italic">EM CURSO</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                    <div class="flex -space-x-2 overflow-hidden">
                        @if($po->responsavel)
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-800 text-white flex items-center justify-center text-[10px] font-black" title="Responsável: {{ $po->responsavel->nome }}">
                                {{ substr($po->responsavel->nome, 0, 1) }}
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-[10px] font-black text-slate-300" title="Sem responsável">
                                ?
                            </div>
                        @endif
                    </div>
                    
                    <a href="{{ route('admin.ops.production.show', $po) }}" class="rounded-xl bg-slate-100 px-4 py-2 text-[10px] font-black text-slate-600 hover:bg-brand-primary hover:text-white transition uppercase tracking-widest">
                        Gerenciar Etapas
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl bg-white p-20 text-center border-2 border-dashed border-slate-100">
                <span class="text-8xl block mb-4 opacity-10">🏭</span>
                <p class="text-lg font-black text-slate-400 italic">Pátio de produção vazio. Inicie novos pedidos para vê-los aqui.</p>
            </div>
        @endforelse
    </div>

    @if($orders->hasPages())
        <div class="mt-12">
            {{ $orders->links() }}
        </div>
    @endif
</x-layouts.app>
