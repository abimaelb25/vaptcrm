{{-- 
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-13 14:03 -03:00
--}}
<x-layouts.app :titulo="'Relatórios de Caixa - ' . config('app.name')">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Controle de Caixas</h1>
            <p class="text-slate-500 font-medium font-serif italic">Histórico de turnos, aberturas e fechamentos por atendente</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pos.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-primary px-5 py-2.5 text-xs font-black uppercase text-white shadow-xl hover:bg-orange-600 transition-all">
                Ir para o PDV
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="mb-8 rounded-3xl bg-white p-6 shadow-xl border border-slate-100">
        <form method="GET" action="{{ route('admin.bi.caixas.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Atendente</label>
                <select name="usuario_id" class="w-full rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:ring-brand-primary">
                    <option value="">Todos os Atendentes</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" @selected(request('usuario_id') == $u->id)>{{ $u->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Data Início</label>
                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:ring-brand-primary">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2">Data Fim</label>
                <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:ring-brand-primary">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-brand-secondary text-white py-2.5 rounded-xl font-bold hover:bg-slate-800 transition-colors uppercase text-[10px] tracking-widest">
                    Filtrar Relatório
                </button>
                <a href="{{ route('admin.bi.caixas.index') }}" class="bg-slate-100 text-slate-400 p-2.5 rounded-xl hover:bg-slate-200 transition-colors" title="Limpar Filtros">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </div>
        </form>
    </div>

    {{-- Tabela de Resultados --}}
    <div class="overflow-hidden rounded-3xl bg-white shadow-2xl border border-slate-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Atendente / ID</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Abertura</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Fundo (R$)</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Vendas (R$)</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Diferença</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($caixas as $caixa)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700">{{ $caixa->usuario->nome }}</div>
                                <div class="text-[10px] font-black text-slate-400">#{{ str_pad((string)$caixa->id, 5, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-slate-600">{{ $caixa->data_abertura->format('d/m/Y') }}</div>
                                <div class="text-[10px] font-medium text-slate-400 italic">às {{ $caixa->data_abertura->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($caixa->status === 'aberto')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase text-emerald-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                        Aberto
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase text-slate-500">
                                        Fechado em {{ $caixa->data_fechamento->format('d/m H:i') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-600">
                                {{ number_format($caixa->valor_inicial, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-black text-brand-secondary">
                                {{ number_format($caixa->valor_vendas, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($caixa->status === 'fechado')
                                    @php $dif = $caixa->diferenca; @endphp
                                    @if($dif == 0)
                                        <span class="text-[10px] font-black text-emerald-500 uppercase">OK</span>
                                    @elseif($dif > 0)
                                        <span class="text-[10px] font-black text-blue-500 uppercase" title="Sobra">+ {{ number_format($dif, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-[10px] font-black text-rose-500 uppercase" title="Quebra">{{ number_format($dif, 2, ',', '.') }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right flex justify-end gap-2">
                                @if($caixa->status === 'aberto')
                                    <a href="{{ route('admin.bi.caixas.show', $caixa->id) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 hover:bg-emerald-100 transition-all shadow-sm" title="Fechar este Caixa">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </a>
                                @endif
                                <a href="{{ route('admin.bi.caixas.show', $caixa->id) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:border-brand-primary hover:text-brand-primary transition-all shadow-sm" title="Ver Detalhes">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">
                                <div class="flex flex-col items-center gap-3 opacity-50">
                                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p>Nenhum registro de caixa encontrado.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($caixas->hasPages())
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50 shadow-inner">
                {{ $caixas->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
