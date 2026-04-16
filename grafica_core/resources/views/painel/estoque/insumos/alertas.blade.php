{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:25
--}}
<x-layouts.app>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Alertas de <span class="text-orange-500">Estoque Crítico</span></h1>
            <p class="text-slate-500 font-medium">Itens que atingiram ou estão abaixo do estoque mínimo de segurança.</p>
        </div>
        <a href="{{ route('admin.inventory.insumos.index') }}" class="text-sm font-bold text-slate-400 hover:text-brand-primary transition">← Voltar para todos</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($insumos as $insumo)
            <div class="rounded-2xl bg-white p-6 shadow-xl border-2 {{ $insumo->estoque_atual <= 0 ? 'border-red-200 bg-red-50/10' : 'border-orange-200 bg-orange-50/10' }} flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-3xl">{{ $insumo->estoque_atual <= 0 ? '🚫' : '⚠️' }}</span>
                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase {{ $insumo->estoque_atual <= 0 ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600' }}">
                            {{ $insumo->estoque_atual <= 0 ? 'Crítico' : 'Baixo' }}
                        </span>
                    </div>
                    
                    <h3 class="text-lg font-black text-slate-800 leading-tight mb-4">{{ $insumo->nome }}</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                            <span class="text-xs font-bold text-slate-400 uppercase">Estoque Atual</span>
                            <span class="text-lg font-black text-slate-800">{{ number_format($insumo->estoque_atual, 2) }} {{ $insumo->unidade_medida }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                            <span class="text-xs font-bold text-slate-400 uppercase">Mínimo Esperado</span>
                            <span class="text-sm font-bold text-slate-600">{{ number_format($insumo->estoque_minimo, 2) }} {{ $insumo->unidade_medida }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <a href="{{ route('admin.inventory.movimentacoes.entrada', ['insumo_id' => $insumo->id]) }}" class="flex-1 rounded-xl bg-slate-800 py-3 text-center font-black text-white text-xs uppercase tracking-widest shadow-lg hover:bg-slate-700 transition">
                        Repor Estoque
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl bg-white p-20 text-center border border-dashed border-slate-200">
                <span class="text-6xl block mb-4">🎉</span>
                <p class="text-lg font-black text-slate-600">Parabéns! Nenhum insumo com estoque baixo.</p>
                <p class="text-sm text-slate-400">Todos os materiais estão dentro da margem de segurança.</p>
            </div>
        @endforelse
    </div>
</x-layouts.app>
