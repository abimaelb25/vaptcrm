{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 18:55
--}}
@php $isEdit = $insumo->exists; @endphp
<x-layouts.app>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">
                {{ $isEdit ? 'Editar' : 'Novo' }} <span class="text-brand-primary">Insumo</span>
            </h1>
            <p class="text-slate-500 font-medium">Cadastre detalhes técnicos da sua matéria-prima.</p>
        </div>
        <a href="{{ route('admin.inventory.insumos.index') }}" class="text-sm font-bold text-slate-400 hover:text-brand-primary transition">← Voltar para listagem</a>
    </div>

    <form action="{{ $isEdit ? route('admin.inventory.insumos.update', $insumo) : route('admin.inventory.insumos.store') }}" method="POST">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Dados Principais -->
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
                    <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                        <span class="bg-brand-primary/10 p-2 rounded-lg text-brand-primary">📋</span> Identificação
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-slate-400 uppercase mb-2">Nome do Insumo <span class="text-red-500">*</span></label>
                            <input type="text" name="nome" value="{{ old('nome', $insumo->nome) }}" required placeholder="Ex: Lona 440g Fosca, Papel Couchê 150g" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase mb-2">Código Interno</label>
                            <input type="text" name="codigo_interno" value="{{ old('codigo_interno', $insumo->codigo_interno) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase mb-2">Categoria</label>
                            <input type="text" name="categoria" value="{{ old('categoria', $insumo->categoria) }}" placeholder="Papel, Vinil, Tinta..." class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase mb-2">Unidade de Medida <span class="text-red-500">*</span></label>
                            <select name="unidade_medida" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                                <option value="">Selecione...</option>
                                @foreach(['unidade', 'folha', 'metro', 'm2', 'litro', 'kg', 'bobina', 'rolo', 'pacote'] as $und)
                                    <option value="{{ $und }}" {{ old('unidade_medida', $insumo->unidade_medida) == $und ? 'selected' : '' }}>{{ ucfirst($und) }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($isEdit)
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase mb-2">Status</label>
                            <select name="ativo" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                                <option value="1" {{ $insumo->ativo ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ !$insumo->ativo ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
                    <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                        <span class="bg-slate-100 p-2 rounded-lg text-slate-600">📝</span> Observações
                    </h3>
                    <textarea name="observacao" rows="4" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">{{ old('observacao', $insumo->observacao) }}</textarea>
                </div>
            </div>

            <!-- Configurações de Estoque -->
            <div class="space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
                    <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                        <span class="bg-orange-100 p-2 rounded-lg text-orange-600">🔔</span> Alertas de Estoque
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase mb-2">Estoque Mínimo <span class="text-red-500">*</span></label>
                            <input type="number" step="0.0001" name="estoque_minimo" value="{{ old('estoque_minimo', $insumo->estoque_minimo ?? 0) }}" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary text-lg font-black">
                            <p class="mt-1 text-[10px] text-slate-400">Gera alerta quando o estoque atingir este valor.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase mb-2">Estoque Máximo (Opcional)</label>
                            <input type="number" step="0.0001" name="estoque_maximo" value="{{ old('estoque_maximo', $insumo->estoque_maximo) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                            <p class="mt-1 text-[10px] text-slate-400">Ajuda no controle de excesso e pedidos de compra.</p>
                        </div>
                    </div>
                </div>

                @if($isEdit)
                <div class="rounded-2xl bg-brand-secondary p-6 shadow-xl text-white">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-1">Status Financeiro</p>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-bold opacity-70">Custo Médio Atual</p>
                            <p class="text-2xl font-black">R$ {{ number_format($insumo->custo_medio, 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold opacity-70">Último Custo de Compra</p>
                            <p class="text-xl font-bold">R$ {{ number_format($insumo->ultimo_custo, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                @else
                <div class="rounded-2xl bg-slate-50 p-6 border-2 border-dashed border-slate-200 text-center">
                    <p class="text-sm font-bold text-slate-400 mb-2">O saldo de estoque e custos serão definidos via "Registrar Entrada".</p>
                </div>
                @endif

                <button type="submit" class="w-full rounded-2xl bg-brand-primary py-5 text-center font-black text-white shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-sm">
                    {{ $isEdit ? 'Salvar Alterações' : 'Cadastrar Insumo' }}
                </button>
            </div>
        </div>
    </form>
</x-layouts.app>
