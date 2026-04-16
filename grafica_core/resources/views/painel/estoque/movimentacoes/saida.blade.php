{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:05
--}}
<x-layouts.app>
    <div class="mb-8 max-w-2xl mx-auto">
        <h1 class="text-3xl font-black text-brand-secondary">Registrar <span class="text-red-500">Baixa de Estoque</span></h1>
        <p class="text-slate-500 font-medium">Lance o consumo de materiais na produção ou perdas técnicas.</p>
    </div>

    <div class="max-w-2xl mx-auto rounded-3xl bg-white p-8 shadow-2xl border border-slate-100">
        <form action="{{ route('admin.inventory.movimentacoes.processar-saida') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Insumo / Material <span class="text-red-500">*</span></label>
                <select name="insumo_id" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-red-500 text-lg font-bold text-slate-700">
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
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Quantidade a Retirar <span class="text-red-500">*</span></label>
                    <input type="number" step="0.0001" name="quantidade" required class="w-full rounded-xl border-slate-200 bg-red-50/30 focus:ring-red-500 text-2xl font-black text-red-700">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Motivo da Saída <span class="text-red-500">*</span></label>
                    <select name="origem" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-red-500 font-bold">
                        <option value="producao">Consumo em Produção</option>
                        <option value="perda">Perda / Descarte</option>
                        <option value="manual">Saída Manual</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Data da Movimentação</label>
                <input type="datetime-local" name="data_movimentacao" required value="{{ date('Y-m-d\TH:i') }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Descrição / Ordem de Serviço</label>
                <textarea name="descricao" rows="2" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-red-500" placeholder="Ex: Consumo para Pedido #999, Erro de corte, etc."></textarea>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full rounded-2xl bg-red-500 py-5 text-center font-black text-white shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-sm">
                    Confirmar Baixa de Estoque
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
