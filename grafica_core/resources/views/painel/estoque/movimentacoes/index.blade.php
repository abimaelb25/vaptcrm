{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:10
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Histórico de <span class="text-brand-primary">Movimentações</span></h1>
            <p class="text-slate-500 font-medium">Timeline completa de entradas, saídas e ajustes de estoque.</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
        <form class="flex flex-wrap items-center gap-6">
            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-black text-slate-400 uppercase">Insumo</label>
                <select name="insumo_id" class="rounded-lg border-slate-200 text-sm focus:ring-brand-primary min-w-[200px]">
                    <option value="">Todos os Insumos</option>
                    @foreach($insumos as $ins)
                        <option value="{{ $ins->id }}" {{ request('insumo_id') == $ins->id ? 'selected' : '' }}>{{ $ins->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-black text-slate-400 uppercase">Tipo</label>
                <select name="tipo" class="rounded-lg border-slate-200 text-sm focus:ring-brand-primary">
                    <option value="">Todos</option>
                    <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                    <option value="saida" {{ request('tipo') == 'saida' ? 'selected' : '' }}>Saída</option>
                    <option value="ajuste" {{ request('tipo') == 'ajuste' ? 'selected' : '' }}>Ajuste</option>
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-black text-slate-400 uppercase">Período</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="inicio" value="{{ request('inicio') }}" class="rounded-lg border-slate-200 text-sm focus:ring-brand-primary">
                    <span class="text-slate-300">até</span>
                    <input type="date" name="fim" value="{{ request('fim') }}" class="rounded-lg border-slate-200 text-sm focus:ring-brand-primary">
                </div>
            </div>

            <div class="flex items-end h-[52px]">
                <button type="submit" class="rounded-lg bg-slate-800 px-6 py-2.5 text-sm font-bold text-white hover:bg-slate-700 transition">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- Timeline -->
    <div class="space-y-4">
        @forelse($movimentacoes as $mov)
            <div class="relative pl-8 before:absolute before:left-3 before:top-0 before:bottom-0 before:w-px before:bg-slate-200 last:before:hidden">
                <div class="absolute left-0 top-1 w-6 h-6 rounded-full border-4 border-white shadow-sm flex items-center justify-center
                    {{ $mov->tipo === 'entrada' ? 'bg-emerald-500' : ($mov->tipo === 'saida' ? 'bg-red-500' : 'bg-orange-500') }}">
                </div>
                
                <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 transition hover:shadow-md">
                    <div class="flex gap-4 items-start">
                        <div class="hidden sm:flex flex-col items-center justify-center bg-slate-50 rounded-xl p-2 min-w-[70px]">
                            <span class="text-[10px] font-black text-slate-400 uppercase">{{ $mov->data_movimentacao->format('M') }}</span>
                            <span class="text-xl font-black text-slate-700">{{ $mov->data_movimentacao->format('d') }}</span>
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[10px] font-black uppercase tracking-widest {{ $mov->tipo === 'entrada' ? 'text-emerald-600' : ($mov->tipo === 'saida' ? 'text-red-600' : 'text-orange-600') }}">
                                    {{ $mov->tipo }} • {{ $mov->origem }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-bold">{{ $mov->data_movimentacao->format('H:i') }}</span>
                            </div>
                            <h3 class="text-base font-black text-slate-800">{{ $mov->insumo->nome }}</h3>
                            <p class="text-sm text-slate-500 font-medium">{{ $mov->descricao ?? 'Sem descrição adicional.' }}</p>
                            
                            @if($mov->fornecedor)
                                <p class="mt-2 text-[10px] font-bold text-slate-400 flex items-center gap-1">
                                    <span>🚚 Fornecedor:</span>
                                    <span class="text-slate-600">{{ $mov->fornecedor->nome }}</span>
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between md:flex-col md:items-end gap-2 border-t md:border-t-0 pt-3 md:pt-0">
                        <div class="text-right">
                            <p class="text-xl font-black {{ $mov->tipo === 'entrada' ? 'text-emerald-600' : ($mov->tipo === 'saida' ? 'text-red-600' : 'text-slate-700') }}">
                                {{ $mov->tipo === 'entrada' ? '+' : ($mov->tipo === 'saida' ? '-' : '') }}
                                {{ number_format(abs($mov->quantidade), 2, ',', '.') }} 
                                <span class="text-xs opacity-60">{{ $mov->insumo->unidade_medida }}</span>
                            </p>
                            @if($mov->valor_total > 0)
                                <p class="text-xs font-bold text-slate-400">Total: R$ {{ number_format($mov->valor_total, 2, ',', '.') }}</p>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">🧤 {{ $mov->usuario->nome }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-white p-20 text-center border border-dashed border-slate-200">
                <span class="text-6xl block mb-4 opacity-10">⏳</span>
                <p class="text-lg font-black text-slate-400">Nenhuma movimentação registrada no período.</p>
            </div>
        @endforelse

        @if($movimentacoes->hasPages())
            <div class="mt-8">
                {{ $movimentacoes->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
