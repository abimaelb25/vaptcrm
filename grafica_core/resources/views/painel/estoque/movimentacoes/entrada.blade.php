{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:00
--}}
<x-layouts.app>
    <div class="mb-8 max-w-2xl mx-auto">
        <h1 class="text-3xl font-black text-brand-secondary">Registrar <span class="text-emerald-500">Entrada de Estoque</span></h1>
        <p class="text-slate-500 font-medium">Lance compras de materiais e atualize seu custo médio automaticamente.</p>
    </div>

    <div class="max-w-2xl mx-auto rounded-3xl bg-white p-8 shadow-2xl border border-slate-100">
        <form action="{{ route('admin.inventory.movimentacoes.processar-entrada') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Insumo / Material <span class="text-red-500">*</span></label>
                <select name="insumo_id" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-emerald-500 text-lg font-bold text-slate-700">
                    <option value="">Selecione o Insumo...</option>
                    @foreach($insumos as $insumo)
                        <option value="{{ $insumo->id }}" {{ $insumo_id == $insumo->id ? 'selected' : '' }}>
                            {{ $insumo->nome }} (Saldo: {{ number_format($insumo->estoque_atual, 2) }} {{ $insumo->unidade_medida }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Quantidade <span class="text-red-500">*</span></label>
                    <input type="number" step="0.0001" name="quantidade" required class="w-full rounded-xl border-slate-200 bg-emerald-50/30 focus:ring-emerald-500 text-2xl font-black text-emerald-700">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Custo Unitário (R$) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="custo_unitario" required class="w-full rounded-xl border-slate-200 bg-emerald-50/30 focus:ring-emerald-500 text-2xl font-black text-emerald-700">
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Fornecedor</label>
                <select name="fornecedor_id" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-emerald-500">
                    <option value="">Selecione o Fornecedor (opcional)</option>
                    @foreach($fornecedores as $forn)
                        <option value="{{ $forn->id }}">{{ $forn->nome }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-[10px] text-slate-400">Ajuda a rastrear preços por parceiro.</p>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Data da Compra/Recebimento</label>
                <input type="datetime-local" name="data_movimentacao" required value="{{ date('Y-m-d\TH:i') }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Observação / Nota Fiscal</label>
                <textarea name="descricao" rows="2" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-emerald-500" placeholder="Ex: NF 1234, Lote A2, etc."></textarea>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full rounded-2xl bg-emerald-500 py-5 text-center font-black text-white shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-sm">
                    Confirmar Entrada e Atualizar Custo
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
