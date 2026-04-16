{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:15
--}}
<x-layouts.app>
    <div class="mb-8 max-w-lg mx-auto">
        <h1 class="text-3xl font-black text-brand-secondary">Ajuste de <span class="text-orange-500">Saldo Real</span></h1>
        <p class="text-slate-500 font-medium">Sincronize o estoque do sistema com a contagem física da prateleira.</p>
    </div>

    <div class="max-w-lg mx-auto rounded-3xl bg-white p-8 shadow-2xl border border-orange-100">
        <div class="mb-6 p-4 bg-orange-50 rounded-xl border border-orange-100 flex items-center gap-4">
            <span class="text-3xl">⚖️</span>
            <div>
                <p class="text-xs font-black text-orange-600 uppercase">Ajustando Item:</p>
                <p class="text-xl font-black text-slate-800">{{ $insumo->nome }}</p>
                <p class="text-sm font-bold text-slate-500">Saldo Atual no Sistema: {{ number_format($insumo->estoque_atual, 2) }} {{ $insumo->unidade_medida }}</p>
            </div>
        </div>

        <form action="{{ route('admin.inventory.insumos.processar-ajuste', $insumo) }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Novo Saldo Real (Contagem Física) <span class="text-red-500">*</span></label>
                <input type="number" step="0.0001" name="quantidade" required value="{{ number_format($insumo->estoque_atual, 2, '.', '') }}" class="w-full rounded-xl border-slate-200 bg-orange-50/20 focus:ring-orange-500 text-3xl font-black text-slate-800">
                <p class="mt-2 text-[11px] text-slate-400 font-medium italic">Ao salvar, o sistema irá calcular a diferença (+ ou -) e gerar uma movimentação do tipo "Ajuste".</p>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Motivo do Ajuste <span class="text-red-500">*</span></label>
                <input type="text" name="descricao" required placeholder="Ex: Balanço Mensal, Correção de erro de lançamento..." class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-orange-500">
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full rounded-2xl bg-orange-500 py-5 text-center font-black text-white shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-sm">
                    Confirmar Ajuste de Saldo
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
