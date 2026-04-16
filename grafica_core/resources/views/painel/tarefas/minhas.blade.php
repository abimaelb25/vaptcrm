<x-layouts.app titulo="Minhas Tarefas - Gráfica Vapt Vupt">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">Minhas Tarefas</h1>
            <p class="text-slate-500 mt-1 font-medium">Acompanhe seu fluxo de trabalho pessoal</p>
        </div>
        <button onclick="document.getElementById('modalNovaTarefa').classList.remove('hidden')" class="btn btn-primary rounded-xl px-5 py-2.5 font-bold shadow-md hover:-translate-y-1 transition-all bg-brand-primary text-white">
            Nova Tarefa
        </button>
    </div>

    @php
        $statusMap = [
            'backlog' => ['label' => 'Backlog', 'color' => 'bg-slate-100 border-slate-300'],
            'a_fazer' => ['label' => 'A Fazer', 'color' => 'bg-blue-50 border-blue-200'],
            'em_andamento' => ['label' => 'Em Andamento', 'color' => 'bg-amber-50 border-amber-200'],
            'bloqueada' => ['label' => 'Bloqueada', 'color' => 'bg-red-50 border-red-200'],
            'concluida' => ['label' => 'Concluída', 'color' => 'bg-emerald-50 border-emerald-200'],
            'cancelada' => ['label' => 'Cancelada', 'color' => 'bg-slate-50 border-slate-200 opacity-70'],
        ];
    @endphp

    <div class="flex overflow-x-auto gap-6 pb-6 w-full -mx-4 px-4 scrollbar-thin scrollbar-thumb-slate-300 min-h-[600px]">
        @foreach($statusMap as $k => $cfg)
            <div class="flex-shrink-0 w-80 rounded-2xl border-2 {{ $cfg['color'] }} flex flex-col pt-4">
                <div class="px-4 mb-4 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">{{ $cfg['label'] }}</h3>
                    <span class="text-xs font-black bg-white rounded-full px-2.5 py-1 border border-black/10 text-slate-600">
                        {{ $tarefas[$k]->count() }}
                    </span>
                </div>
                
                <div class="flex-1 overflow-y-auto px-4 pb-4 space-y-4">
                    @forelse($tarefas[$k] as $t)
                        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 hover:shadow-md transition-shadow relative group">
                            <form action="{{ route('admin.ops.tasks.destroy', $t->id) }}" method="POST" class="absolute top-3 right-3 hidden group-hover:block" onsubmit="return confirm('Tem certeza?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 rounded-lg p-1" title="Excluir"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </form>

                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full 
                                    {{ $t->prioridade === 'urgente' ? 'bg-red-100 text-red-700' : ($t->prioridade === 'alta' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $t->prioridade }}
                                </span>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm leading-snug">{{ $t->titulo }}</h4>
                            @if($t->descricao)
                                <p class="text-xs text-slate-500 mt-2 line-clamp-2">{{ $t->descricao }}</p>
                            @endif
                            <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-medium">Resp: {{ $t->responsavel->nome ?? 'Você' }}</span>
                                @if($t->prazo)
                                    <span class="text-xs font-semibold {{ $t->prazo->isPast() && $k!=='concluida' ? 'text-red-500' : 'text-slate-500' }}">{{ $t->prazo->format('d/m/Y') }}</span>
                                @endif
                            </div>

                            <!-- Botões de Movimentação Simples -->
                            <div class="mt-3 flex flex-wrap gap-1">
                                <form action="{{ route('admin.ops.tasks.status', $t->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="text-xs border-slate-200 rounded-lg bg-slate-50 py-1 pl-2 pr-6">
                                        <option value="">Mover...</option>
                                        @foreach($statusMap as $sk => $scfg)
                                            @if($sk !== $k)
                                                <option value="{{ $sk }}">{{ $scfg['label'] }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-sm text-slate-400 font-medium border-2 border-dashed border-black/10 rounded-xl">
                            Vazio
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Nova Tarefa -->
    <div id="modalNovaTarefa" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('modalNovaTarefa').classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6">
            <h2 class="text-2xl font-bold text-brand-secondary mb-4">Nova Tarefa</h2>
            <form action="{{ route('admin.ops.tasks.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Título *</label>
                        <input type="text" name="titulo" required class="w-full rounded-xl border-slate-200 focus:border-brand-primary focus:ring-brand-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Descrição</label>
                        <textarea name="descricao" rows="3" class="w-full rounded-xl border-slate-200 focus:border-brand-primary focus:ring-brand-primary"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Prioridade</label>
                            <select name="prioridade" class="w-full rounded-xl border-slate-200 focus:border-brand-primary focus:ring-brand-primary">
                                <option value="baixa">Baixa</option>
                                <option value="media" selected>Média</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Status Base</label>
                            <select name="status" class="w-full rounded-xl border-slate-200 focus:border-brand-primary focus:ring-brand-primary">
                                <option value="backlog">Backlog</option>
                                <option value="a_fazer" selected>A Fazer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Prazo Realização</label>
                            <input type="date" name="prazo" class="w-full rounded-xl border-slate-200 focus:border-brand-primary focus:ring-brand-primary">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modalNovaTarefa').classList.add('hidden')" class="px-5 py-2 font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2 font-bold text-white bg-brand-primary hover:-translate-y-1 shadow-md rounded-xl transition-all">Salvar Tarefa</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

