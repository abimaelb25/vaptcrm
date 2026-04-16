{{-- 
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-13 14:04 -03:00
--}}
<x-layouts.app :titulo="'Detalhes do Caixa #' . str_pad((string)$caixa->id, 5, '0', STR_PAD_LEFT)">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.bi.caixas.index') }}" class="text-[10px] font-black uppercase text-slate-400 hover:text-brand-primary transition-colors flex items-center gap-1">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                    Voltar para Lista
                </a>
            </div>
            <h1 class="text-3xl font-black text-brand-secondary">Caixa #{{ str_pad((string)$caixa->id, 5, '0', STR_PAD_LEFT) }}</h1>
            <p class="text-slate-500 font-medium font-serif italic">Resumo detalhado do turno e movimentações financeiras</p>
        </div>

        <div class="flex items-center gap-3">
             <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-5 py-2.5 text-xs font-black uppercase text-slate-600 shadow-sm hover:bg-slate-200 transition-all">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir Relatório
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Resumo Lateral --}}
        <div class="space-y-6">
            <div class="rounded-3xl bg-brand-secondary p-8 shadow-2xl text-white">
                <h4 class="text-xs font-black mb-6 uppercase tracking-widest text-amber-200 border-b border-white/10 pb-3">Informações do Turno</h4>
                <div class="space-y-5">
                    <div>
                        <p class="text-[10px] uppercase font-black text-white/40 tracking-tighter">Atendente</p>
                        <p class="text-sm font-bold">{{ $caixa->usuario->nome }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-black text-white/40 tracking-tighter">Abertura</p>
                        <p class="text-sm font-bold">{{ $caixa->data_abertura->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-black text-white/40 tracking-tighter">Fechamento</p>
                        <p class="text-sm font-bold">{{ $caixa->data_fechamento ? $caixa->data_fechamento->format('d/m/Y H:i') : 'Em Aberto' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-black text-white/40 tracking-tighter">Status Atual</p>
                         <span class="inline-block mt-1 px-3 py-1 rounded-full {{ $caixa->status === 'aberto' ? 'bg-emerald-500' : 'bg-slate-700' }} text-[10px] font-black uppercase">
                            {{ $caixa->status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-8 shadow-2xl border border-slate-100">
                <h4 class="text-xs font-black text-brand-secondary mb-6 uppercase tracking-widest border-b border-slate-50 pb-3">Fechamento Financeiro</h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-400">Fundo Inicial:</span>
                        <span class="font-black text-slate-700">R$ {{ number_format($caixa->valor_inicial, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-400">Total Vendido:</span>
                        <span class="font-black text-emerald-600">R$ + {{ number_format($caixa->valor_vendas, 2, ',', '.') }}</span>
                    </div>
                    <div class="pt-3 border-t border-slate-50 flex justify-between items-center">
                        <span class="text-[10px] font-black uppercase text-slate-500">Total Esperado:</span>
                        <span class="text-lg font-black text-brand-secondary">R$ {{ number_format($caixa->valor_inicial + $caixa->valor_vendas, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs pt-1">
                        <span class="font-bold text-slate-400 italic">Informado p/ Atendente:</span>
                        <span class="font-black text-slate-600">R$ {{ number_format($caixa->valor_fechamento, 2, ',', '.') }}</span>
                    </div>
                    
                    @if($caixa->status === 'fechado')
                        @php $dif = $caixa->diferenca; @endphp
                        <div class="mt-4 p-4 rounded-2xl {{ $dif >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            <p class="text-[10px] font-black uppercase tracking-wider mb-1">Diferença Final</p>
                            <h3 class="text-2xl font-black">R$ {{ number_format($dif, 2, ',', '.') }}</h3>
                            <p class="text-[10px] font-medium opacity-70 italic">{{ $dif == 0 ? 'Conferência perfeita' : ($dif > 0 ? 'Sobra de caixa registrada' : 'Falta de valores no caixa') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($caixa->observacoes)
                <div class="rounded-3xl bg-amber-50 p-6 border border-amber-100">
                    <h4 class="text-[10px] font-black text-amber-700 uppercase mb-2">Observações de Fechamento</h4>
                    <p class="text-xs text-amber-900 leading-relaxed font-medium italic">"{{ $caixa->observacoes }}"</p>
                </div>
            @endif

            {{-- Ações Administrativas --}}
            @if($caixa->status === 'aberto')
                <div class="rounded-3xl bg-slate-50 p-8 border border-slate-200">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <h4 class="text-xs font-black text-slate-700 uppercase tracking-widest">Ação Administrativa</h4>
                    </div>
                    
                    <form action="{{ route('admin.bi.caixas.fechar', $caixa->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-[10px] uppercase font-black text-slate-400 block mb-2">Valor Físico Contado (R$)</label>
                            <input type="number" step="0.01" name="valor_fechamento" required class="w-full rounded-xl border-slate-200 text-sm font-bold focus:border-brand-primary focus:ring-brand-primary" placeholder="0,00">
                        </div>
                        <div>
                            <label class="text-[10px] uppercase font-black text-slate-400 block mb-2">Motivo / Observação</label>
                            <textarea name="observacoes" rows="2" class="w-full rounded-xl border-slate-200 text-xs focus:border-brand-primary focus:ring-brand-primary placeholder:italic" placeholder="Ex: Atendente esqueceu de fechar..."></textarea>
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-brand-secondary py-3 text-[10px] font-black uppercase text-white shadow-lg hover:bg-slate-800 transition-all">
                            Encerrar Turno via Painel
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Lista de Vendas do Turno --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-3xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-50">
                    <h4 class="text-sm font-black text-brand-secondary uppercase tracking-tighter">Relação de Vendas do Turno</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-[10px] font-black uppercase text-slate-400">Hora</th>
                                <th class="px-6 py-3 text-[10px] font-black uppercase text-slate-400">Pedido / Cliente</th>
                                <th class="px-6 py-3 text-[10px] font-black uppercase text-slate-400">Meio Pagto.</th>
                                <th class="px-6 py-3 text-[10px] font-black uppercase text-slate-400 text-right">Valor Bruto</th>
                                <th class="px-6 py-3 text-[10px] font-black uppercase text-slate-400 text-right">Valor Final</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($caixa->movimentacoes as $mov)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 text-xs font-bold text-slate-400">
                                        {{ $mov->created_at->format('H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-700">
                                            @if($mov->pedido)
                                                <a href="{{ route('admin.sales.pedidos.show', $mov->pedido_id) }}" class="hover:text-brand-primary transition-colors">
                                                    OS #{{ $mov->pedido->id }}
                                                </a>
                                            @else
                                                <span class="text-slate-400 italic">Avulso</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] font-medium text-slate-400">
                                            {{ $mov->pedido->cliente->nome ?? 'Cliente não identificado' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-[10px] font-bold text-slate-500 uppercase">
                                            {{ $mov->metodo_pagamento }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-xs font-medium text-slate-400 line-through">
                                        R$ {{ number_format($mov->valor_recebido + ($mov->troco ?? 0), 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-slate-700">
                                        R$ {{ number_format($mov->valor_recebido, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                                        Nenhuma venda processada neste turno ainda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($caixa->movimentacoes->isNotEmpty())
                            <tfoot class="bg-slate-50 font-black text-slate-700 border-t border-slate-100">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-right uppercase tracking-widest text-[10px]">Total Acumulado:</td>
                                    <td class="px-6 py-4 text-right text-lg">R$ {{ number_format($caixa->valor_vendas, 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            
            <div class="rounded-3xl border-2 border-dashed border-slate-200 p-8 flex flex-col items-center justify-center text-center opacity-40">
                <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h6 class="text-xs font-black uppercase text-slate-600 mb-1">Rastreabilidade Segura</h6>
                <p class="text-[10px] font-medium text-slate-400 max-w-xs leading-relaxed">Este registro é imutável após o fechamento e serve para auditoria interna e conferência de equipe.</p>
            </div>
        </div>
    </div>
</x-layouts.app>
