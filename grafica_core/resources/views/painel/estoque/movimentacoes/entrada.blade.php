{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:00
--}}
<x-layouts.app>
    <div class="mb-8 max-w-2xl mx-auto">
        <h1 class="text-3xl font-black text-brand-secondary">Registrar <span class="text-emerald-500">Entrada de Estoque</span></h1>
        <p class="text-slate-500 font-medium">Registrar entrada: esta acao aumenta o estoque e atualiza o custo medio.</p>
    </div>

    <div class="max-w-2xl mx-auto rounded-3xl bg-white p-8 shadow-2xl border border-slate-100">
        <div class="mb-5 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
            <p class="text-xs font-black uppercase text-emerald-700">Proposito da tela</p>
            <p class="text-sm font-semibold text-emerald-800">Use para compras e recebimentos. Para corrigir divergencia de contagem fisica, use Ajuste de saldo fisico.</p>
        </div>

        <form action="{{ route('admin.inventory.movimentacoes.processar-entrada') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Insumo / Material <span class="text-red-500">*</span></label>
                <select name="insumo_id" id="insumo_id" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-emerald-500 text-lg font-bold text-slate-700">
                    <option value="">Selecione o Insumo...</option>
                    @foreach($insumos as $insumo)
                        <option value="{{ $insumo->id }}" {{ $insumo_id == $insumo->id ? 'selected' : '' }}>
                            {{ $insumo->nome }} (Saldo: {{ number_format($insumo->estoque_atual, 2) }} {{ $insumo->unidade_medida }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Bloco de contexto de conversão (visível apenas para insumos com conversão) --}}
            <div id="bloco-conversao" class="hidden rounded-xl border border-violet-200 bg-violet-50 p-4">
                <p class="text-xs font-black text-violet-700 uppercase mb-2">Este insumo tem conversão de unidade</p>
                <p class="text-sm font-bold text-violet-800" id="conversao-info">—</p>
                <label class="flex items-center gap-3 mt-3 cursor-pointer">
                    <input type="checkbox" name="em_unidade_compra" id="em_unidade_compra" value="1" checked class="rounded text-violet-600 focus:ring-violet-500">
                    <span class="text-sm font-bold text-violet-700">
                        Informar quantidade em <span id="label-unidade-compra">pacotes</span>
                        (o sistema converte automaticamente para <span id="label-unidade-consumo">unidades</span>)
                    </span>
                </label>
            </div>

            <input type="hidden" name="em_unidade_compra" id="em_unidade_compra_hidden" value="0">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2" id="label-quantidade">
                        Quantidade <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.0001" name="quantidade" id="quantidade" required
                           class="w-full rounded-xl border-slate-200 bg-emerald-50/30 focus:ring-emerald-500 text-2xl font-black text-emerald-700">
                    <p class="mt-1 text-[10px] text-slate-400" id="hint-quantidade">Ex: 2 (pacotes), 3 (rolos)...</p>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2" id="label-custo">
                        Custo Unitário (R$) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.000001" name="custo_unitario" id="custo_unitario" required
                           class="w-full rounded-xl border-slate-200 bg-emerald-50/30 focus:ring-emerald-500 text-2xl font-black text-emerald-700">
                    <p class="mt-1 text-[10px] text-slate-400" id="hint-custo">Custo por unidade informada acima.</p>
                </div>
            </div>

            {{-- Preview do custo de consumo em tempo real --}}
            <div id="preview-custo-consumo" class="hidden rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-bold text-emerald-800"></div>

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

    <script>
    (function () {
        var insumosData  = {!! $insumosJson !!};
        var selectInsumo = document.getElementById('insumo_id');
        var blocoConv    = document.getElementById('bloco-conversao');
        var cbConversao  = document.getElementById('em_unidade_compra');
        var hiddenConv   = document.getElementById('em_unidade_compra_hidden');
        var labelQtd     = document.getElementById('label-quantidade');
        var hintQtd      = document.getElementById('hint-quantidade');
        var labelCusto   = document.getElementById('label-custo');
        var hintCusto    = document.getElementById('hint-custo');
        var convInfo     = document.getElementById('conversao-info');
        var labelUC      = document.getElementById('label-unidade-compra');
        var labelUM      = document.getElementById('label-unidade-consumo');
        var inputQtd     = document.getElementById('quantidade');
        var inputCusto   = document.getElementById('custo_unitario');
        var previewDiv   = document.getElementById('preview-custo-consumo');

        function atualizar() {
            var id   = selectInsumo.value;
            var info = id ? insumosData[id] : null;

            if (info && info.tem_conversao) {
                blocoConv.classList.remove('hidden');
                labelUC.textContent = info.unidade_compra;
                labelUM.textContent = info.unidade_medida;
                var fator = parseFloat(info.fator_conversao || info.quantidade_por_compra || 1);
                convInfo.textContent =
                    'Cada ' + info.unidade_compra + ' contém ' +
                    fator.toLocaleString('pt-BR') + ' ' + info.unidade_medida + '(s).';
            } else {
                blocoConv.classList.add('hidden');
                if (hiddenConv) hiddenConv.value = '0';
            }

            atualizarLabels(info);
        }

        function atualizarLabels(info) {
            var emCompra = info && info.tem_conversao && cbConversao.checked;

            if (emCompra) {
                var fator = parseFloat(info.fator_conversao || info.quantidade_por_compra || 1);
                hiddenConv.value = '1';
                labelQtd.innerHTML = 'Quantidade de <strong>' + info.unidade_compra + '(s)</strong> recebidos <span class="text-red-500">*</span>';
                hintQtd.textContent  = 'Ex: 3 ' + info.unidade_compra + 's = ' + (3 * fator) + ' ' + info.unidade_medida + 's.';
                labelCusto.innerHTML = 'Custo por <strong>' + info.unidade_compra + '</strong> (R$) <span class="text-red-500">*</span>';
                hintCusto.textContent = 'O custo por ' + info.unidade_medida + ' será calculado automaticamente.';
            } else {
                hiddenConv.value = '0';
                var und = (info ? info.unidade_medida : null) || 'unidade';
                labelQtd.innerHTML = 'Quantidade (' + und + ') <span class="text-red-500">*</span>';
                hintQtd.textContent  = '';
                labelCusto.innerHTML = 'Custo por ' + und + ' (R$) <span class="text-red-500">*</span>';
                hintCusto.textContent = '';
            }

            calcularPreview(info, emCompra);
        }

        function calcularPreview(info, emCompra) {
            var qtd   = parseFloat(inputQtd.value)   || 0;
            var custo = parseFloat(inputCusto.value) || 0;

            if (!info || !info.tem_conversao || !emCompra || !custo) {
                previewDiv.classList.add('hidden');
                return;
            }

            var fator = parseFloat(info.fator_conversao || info.quantidade_por_compra || 1);
            var custoConsumo = custo / fator;
            var qtdConsumo   = qtd * fator;
            previewDiv.classList.remove('hidden');
            previewDiv.textContent =
                'Custo por ' + info.unidade_medida + ': R$ ' + custoConsumo.toFixed(6).replace('.', ',') +
                ' | Total de ' + info.unidade_medida + '(s): ' + qtdConsumo.toLocaleString('pt-BR');
        }

        selectInsumo.addEventListener('change', atualizar);
        cbConversao.addEventListener('change', function () {
            var id   = selectInsumo.value;
            var info = id ? insumosData[id] : null;
            atualizarLabels(info);
        });
        inputQtd.addEventListener('input', function () {
            var id   = selectInsumo.value;
            var info = id ? insumosData[id] : null;
            calcularPreview(info, info && info.tem_conversao && cbConversao.checked);
        });
        inputCusto.addEventListener('input', function () {
            var id   = selectInsumo.value;
            var info = id ? insumosData[id] : null;
            calcularPreview(info, info && info.tem_conversao && cbConversao.checked);
        });

        atualizar();
    })();
    </script>
</x-layouts.app>
