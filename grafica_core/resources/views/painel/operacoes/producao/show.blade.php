{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:20
--}}
<x-layouts.app>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Controle de <span class="text-brand-primary">Processo</span></h1>
            <p class="text-slate-500 font-medium tracking-tight">Pedido #{{ $order->pedido->numero }} • {{ $order->pedido->cliente->nome }}</p>
        </div>
        <a href="{{ route('admin.ops.production.index') }}" class="text-sm font-bold text-slate-400 hover:text-brand-primary transition uppercase tracking-widest">← Voltar Painel</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Detalhamento de Etapas -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-3xl bg-white p-8 shadow-xl border border-slate-100 relative">
                <div class="absolute top-8 right-8">
                    <span class="rounded-full px-4 py-1.5 text-sm font-black uppercase {{ $order->prioridade === 'urgente' ? 'bg-red-500 text-white animate-pulse' : 'bg-slate-100 text-slate-600' }}">
                        🔥 {{ $order->prioridade }}
                    </span>
                </div>

                <h3 class="text-xl font-black text-slate-800 mb-8 border-b border-slate-50 pb-4">Etapas da Produção</h3>
                
                <div class="space-y-4">
                    @foreach($order->stages as $stage)
                        @php $isCurrent = $stage->status === 'em_andamento'; @endphp
                        <div class="rounded-2xl p-6 border {{ $stage->status === 'concluido' ? 'bg-slate-50 border-slate-100 opacity-60' : ($isCurrent ? 'bg-brand-primary/5 border-brand-primary/20 ring-1 ring-brand-primary' : 'bg-white border-slate-100 shadow-sm') }} flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all">
                            <div class="flex items-start gap-4">
                                <span class="w-10 h-10 rounded-xl flex items-center justify-center text-lg font-black {{ $stage->status === 'concluido' ? 'bg-emerald-500 text-white' : ($isCurrent ? 'bg-brand-primary text-white animate-bounce' : 'bg-slate-100 text-slate-400') }}">
                                    {{ $loop->iteration }}
                                </span>
                                <div>
                                    <h4 class="text-base font-black {{ $stage->status === 'concluido' ? 'text-slate-500 line-through' : 'text-slate-800' }}">
                                        {{ $stage->stepDefinition->nome }}
                                    </h4>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                                        @if($stage->data_inicio)
                                            <span class="text-[10px] font-black text-slate-400 uppercase italic">⏳ Início: {{ $stage->data_inicio->format('H:i') }}</span>
                                        @endif
                                        @if($stage->responsavel)
                                            <span class="text-[10px] font-black text-brand-secondary uppercase">🏭 Resp: {{ $stage->responsavel->nome }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('admin.ops.production.step.update', $stage) }}" method="POST" class="flex items-center gap-3">
                                @csrf
                                @if($stage->status === 'pendente')
                                    <input type="hidden" name="status" value="em_andamento">
                                    <button type="submit" class="rounded-xl bg-brand-primary px-5 py-2.5 text-[10px] font-black text-white hover:bg-brand-secondary transition uppercase tracking-widest">
                                        Iniciar Etapa
                                    </button>
                                @elseif($isCurrent)
                                    <input type="hidden" name="status" value="concluido">
                                    <button type="submit" class="rounded-xl bg-emerald-500 px-5 py-2.5 text-[10px] font-black text-white hover:bg-emerald-600 transition uppercase tracking-widest flex items-center gap-2 shadow-lg">
                                        <span>✓</span> Finalizar Etapa
                                    </button>
                                @else
                                    <span class="text-[10px] font-black text-emerald-600 uppercase italic">✓ Concluído</span>
                                @endif
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Resumo Técnico do Pedido -->
            <div class="rounded-3xl bg-slate-900 p-8 shadow-2xl text-white">
                <h3 class="text-lg font-black text-slate-300 mb-6 flex items-center gap-2">
                    <span class="bg-slate-800 p-2 rounded-lg text-slate-500">📄</span> Itens e Arquivos
                </h3>
                <div class="space-y-6">
                    @foreach($order->pedido->itens as $item)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-800 pb-4 last:border-0 last:pb-0">
                            <div>
                                <p class="text-sm font-black text-white leading-tight mb-1">{{ $item->produto->nome }}</p>
                                <p class="text-xs font-bold text-slate-500">{{ $item->quantidade }}x • {{ $item->descricao_item }}</p>
                            </div>
                            @if($item->caminho_arte)
                                <a href="{{ Storage::url($item->caminho_arte) }}" target="_blank" class="mt-3 sm:mt-0 rounded-lg bg-brand-primary px-3 py-1.5 text-[10px] font-black text-white hover:scale-105 transition flex items-center gap-2 uppercase">
                                    <span>🎨</span> Ver Arte
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar Administrativa/Informativa -->
        <div class="space-y-8">
            <div class="rounded-3xl bg-white p-8 shadow-xl border border-slate-100 flex flex-col justify-center items-center text-center">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Geral</p>
                <h4 class="text-2xl font-black text-slate-800 uppercase mb-4">{{ str_replace('_', ' ', $order->status) }}</h4>
                <div class="w-full h-3 bg-slate-50 rounded-full overflow-hidden mb-2">
                    <div class="h-full bg-brand-primary transition-all duration-1000" style="width: {{ $order->progresso }}%"></div>
                </div>
                <p class="text-sm font-black text-brand-primary">{{ $order->progresso }}% concluído</p>
            </div>

            <div class="rounded-3xl bg-white p-8 shadow-xl border border-slate-100">
                <h3 class="text-sm font-black text-slate-800 mb-6 flex items-center gap-2 tracking-widest uppercase">
                    <span>💡</span> Info Operacional
                </h3>
                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1 underline decoration-brand-primary decoration-2">Prazo de Entrega</p>
                        <p class="text-xl font-black text-slate-800">{{ $order->data_previsao ? $order->data_previsao->format('d/m/Y') : 'Contrato Flex' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1 underline decoration-brand-primary decoration-2">Observações</p>
                        <p class="text-sm font-bold text-slate-600 italic leading-relaxed">{{ $order->observacao ?? 'Nenhuma anotação de chão de fábrica.' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-emerald-500 p-8 shadow-2xl text-white relative overflow-hidden group">
                <span class="absolute -right-4 -bottom-4 text-9xl transform rotate-12 opacity-10 group-hover:scale-125 transition duration-500">✅</span>
                <p class="text-lg font-black leading-tight mb-2">Pronto para a logística?</p>
                <p class="text-xs font-bold opacity-80 mb-6">Ao concluir todas as etapas, a OP será finalizada automaticamente.</p>
            </div>
        </div>
    </div>
</x-layouts.app>
