{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-10
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-8">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.finance.index') }}" class="text-slate-400 hover:text-brand-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                <h1 class="text-3xl font-black tracking-tight text-brand-secondary">Extrato de Caixa</h1>
            </div>
            <p class="text-slate-500 font-medium ml-9">Histórico detalhado de todas as movimentações financeiras</p>
        </div>

        <form action="{{ route('admin.finance.extrato') }}" method="GET" class="flex flex-wrap items-center gap-3 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
            <select name="tipo" class="bg-slate-50 border-none rounded-xl text-xs font-black uppercase text-slate-600 focus:ring-brand-primary">
                <option value="">Todos os Tipos</option>
                <option value="entrada" {{ request('tipo') === 'entrada' ? 'selected' : '' }}>Entradas (+)</option>
                <option value="saida" {{ request('tipo') === 'saida' ? 'selected' : '' }}>Saídas (-)</option>
            </select>
            
            <div class="flex items-center gap-2">
                <input type="date" name="inicio" value="{{ request('inicio') }}" class="bg-slate-50 border-none rounded-xl text-xs font-bold text-slate-600">
                <span class="text-slate-300">até</span>
                <input type="date" name="fim" value="{{ request('fim') }}" class="bg-slate-50 border-none rounded-xl text-xs font-bold text-slate-600">
            </div>

            <button type="submit" class="bg-brand-primary text-white px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-orange-600 transition-colors shadow-lg shadow-brand-primary/20">
                Filtrar
            </button>
            
            @if(request()->anyFilled(['tipo', 'inicio', 'fim']))
                <a href="{{ route('admin.finance.extrato') }}" class="text-xs font-bold text-slate-400 hover:text-status-error transition-colors px-2">Limpar</a>
            @endif
        </form>
    </div>

    <div class="rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <th class="px-8 py-5">Data</th>
                        <th class="px-6 py-5">Tipo</th>
                        <th class="px-6 py-5">Categoria/Descrição</th>
                        <th class="px-6 py-5">Vínculo</th>
                        <th class="px-6 py-5">Forma</th>
                        <th class="px-6 py-5">Status</th>
                        <th class="px-8 py-5 text-right">Valor</th>
                        <th class="px-8 py-5 text-center">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse($movimentacoes as $mov)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-8 py-5 text-slate-500 font-bold whitespace-nowrap">{{ $mov->data_movimentacao->format('d/m/Y') }}</td>
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center gap-1.5 font-black uppercase text-[10px] {{ $mov->tipo === 'entrada' ? 'text-emerald-500' : 'text-rose-500' }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $mov->tipo }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="block font-black text-slate-700">{{ $mov->categoria }}</span>
                                @if($mov->descricao)
                                    <span class="text-xs text-slate-400 line-clamp-1 italic">{{ $mov->descricao }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                @if($mov->pedido)
                                    <span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] font-black text-slate-600 border border-slate-200">PED #{{ $mov->pedido->numero }}</span>
                                @else
                                    <span class="text-slate-300 font-light italic text-xs">Aululsa</span>
                                @endif
                            </td>
                            <td class="px-6 py-5 uppercase font-black text-[10px] text-slate-400 tracking-tighter">{{ $mov->forma_pagamento }}</td>
                            <td class="px-6 py-5">
                                <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-tighter {{ $mov->status === 'pago' ? 'bg-emerald-100 text-emerald-700' : ($mov->status === 'pendente' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                    {{ $mov->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right font-black text-base {{ $mov->tipo === 'entrada' ? 'text-emerald-600' : 'text-slate-800' }}">
                                {{ $mov->tipo === 'entrada' ? '+' : '-' }} R$ {{ number_format($mov->valor, 2, ',', '.') }}
                            </td>
                            <td class="px-8 py-5 text-center">
                                @if(!$mov->pedido_id)
                                    <form action="{{ route('admin.finance.destroy', $mov) }}" method="POST" onsubmit="return confirm('Deseja realmente remover este lançamento?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-300 hover:text-status-error transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        </button>
                                    </form>
                                @else
                                    <span title="Vinculado a Pedido" class="text-slate-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-8 py-20 text-center text-slate-400 font-bold">Nenhum registro selecionado com os filtros atuais.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($movimentacoes->hasPages())
            <div class="p-8 border-t border-slate-50 bg-slate-50/20">
                {{ $movimentacoes->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>

