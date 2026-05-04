{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-28 11:30
--}}
@php
    $isEdit = $insumo->exists;
    $tipoAtual = old('tipo_item_operacional', $insumo->tipo_item_operacional ?? 'consumivel');
    $resumoCustosConversao = $isEdit ? $insumo->getResumoConversaoCustos() : null;
@endphp
<x-layouts.app>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">
                {{ $isEdit ? 'Editar' : 'Novo' }} <span class="text-brand-primary">Insumo</span>
            </h1>
            <p class="text-slate-500 font-medium">Configurar insumo: estrutura, unidade e regras operacionais. Esta tela nao altera saldo fisico.</p>
        </div>
        <a href="{{ route('admin.inventory.insumos.index') }}" class="text-sm font-bold text-slate-400 hover:text-brand-primary transition">&larr; Voltar para listagem</a>
    </div>

    <div class="mb-6 rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
        <p class="text-sm font-black text-sky-900">O custo inicial deste item será definido ao registrar a primeira entrada ou ao importar a nota fiscal.</p>
        <p class="mt-1 text-sm font-medium text-sky-800">Use esta tela para configuracao. Entradas e ajustes de saldo sao feitos em telas operacionais dedicadas.</p>
    </div>

    <form action="{{ $isEdit ? route('admin.inventory.insumos.update', $insumo) : route('admin.inventory.insumos.store') }}" method="POST" class="space-y-6">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
            <h3 class="text-lg font-black text-slate-800 mb-6">1. Identificacao</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Nome do item <span class="text-red-500">*</span></label>
                    <input type="text" name="nome" value="{{ old('nome', $insumo->nome) }}" required placeholder="Ex: Dye Magenta 1L, Frasco B1 100ml" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Codigo interno</label>
                    <input type="text" name="codigo_interno" value="{{ old('codigo_interno', $insumo->codigo_interno) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Categoria</label>
                    <input type="text" name="categoria" value="{{ old('categoria', $insumo->categoria) }}" placeholder="Papel, Tinta, Embalagem..." class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
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
            <h3 class="text-lg font-black text-slate-800 mb-2">2. Classificacao do item</h3>
            <p class="text-xs text-slate-500 mb-5">Este material e usado diretamente na producao ou apenas como apoio?</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Tipo do item <span class="text-red-500">*</span></label>
                    <select name="tipo_item_operacional" id="tipo_item_operacional" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary" required>
                        <option value="consumivel" {{ $tipoAtual === 'consumivel' ? 'selected' : '' }}>Consumivel</option>
                        <option value="embalagem" {{ $tipoAtual === 'embalagem' ? 'selected' : '' }}>Embalagem</option>
                        <option value="componente" {{ $tipoAtual === 'componente' ? 'selected' : '' }}>Componente</option>
                        <option value="apoio" {{ $tipoAtual === 'apoio' ? 'selected' : '' }}>Apoio</option>
                        <option value="ignorado" {{ $tipoAtual === 'ignorado' ? 'selected' : '' }}>Ignorado</option>
                    </select>
                </div>

                <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase text-slate-400 mb-2">Impacto desta escolha</p>
                    <p id="tipo-item-help" class="text-sm font-semibold text-slate-700">Consumivel: entra no custo principal do produto.</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
            <h3 class="text-lg font-black text-slate-800 mb-6">3. Unidade e consumo</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Unidade de estoque <span class="text-red-500">*</span></label>
                    <select name="unidade_medida" id="unidade_medida" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                        <option value="">Selecione...</option>
                        @foreach(['unidade', 'folha', 'metro', 'm2', 'litro', 'ml', 'kg', 'g', 'bobina', 'rolo', 'pacote'] as $und)
                            <option value="{{ $und }}" {{ old('unidade_medida', $insumo->unidade_medida) == $und ? 'selected' : '' }}>{{ ucfirst($und) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Compra em</label>
                    <select name="unidade_compra" id="unidade_compra" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                        <option value="">Sem conversao (mesma unidade)</option>
                        @foreach(['pacote', 'rolo', 'caixa', 'bobina', 'frasco', 'galao', 'tambor', 'resma', 'kit', 'fardo', 'saco'] as $uc)
                            <option value="{{ $uc }}" {{ old('unidade_compra', $insumo->unidade_compra) == $uc ? 'selected' : '' }}>{{ ucfirst($uc) }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="bloco-qtd-por-compra">
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Cada unidade de compra equivale a</label>
                    <input type="number" step="0.0001" min="0.0001" name="quantidade_por_compra" id="quantidade_por_compra"
                           value="{{ old('quantidade_por_compra', $insumo->quantidade_por_compra ?? 1) }}"
                           class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                    <p class="text-[10px] text-slate-400 mt-1">Ex: 1 frasco = 1000 ml. Use apenas quando a compra nao tiver embalagem intermediaria.</p>
                </div>
            </div>

            {{-- Segundo nivel: embalagem interna (ex: caixa com 6 frascos de 100ml) --}}
            <div id="bloco-embalagem-interna" class="{{ (old('unidade_compra', $insumo->unidade_compra)) ? '' : 'hidden' }} mt-5 border-t border-slate-100 pt-5">
                <label class="flex items-center gap-3 cursor-pointer mb-4">
                    <input type="checkbox" id="tem_embalagem_interna" name="tem_embalagem_interna" value="1"
                           {{ old('tem_embalagem_interna', $insumo->temDoisNiveisConversao() ? '1' : '') ? 'checked' : '' }}
                           class="rounded text-brand-primary focus:ring-brand-primary h-4 w-4">
                    <span class="text-sm font-bold text-slate-700">Esta compra possui embalagem interna?
                        <span class="text-xs font-normal text-slate-400">(ex: caixa com 6 frascos de 100 ml)</span>
                    </span>
                </label>

                <div id="campos-dois-niveis" class="{{ old('tem_embalagem_interna', $insumo->temDoisNiveisConversao() ? '1' : '') ? '' : 'hidden' }}">
                    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-black text-indigo-600 uppercase mb-2">
                                Quantas unidades intermediarias ha por compra? <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.0001" min="0.0001" name="quantidade_subunidades_por_compra" id="qtd_sub"
                                   value="{{ old('quantidade_subunidades_por_compra', $insumo->quantidade_subunidades_por_compra) }}"
                                   placeholder="Ex: 6" class="w-full rounded-xl border-indigo-200 bg-white focus:ring-brand-primary">
                            <p class="text-[10px] text-indigo-400 mt-1">Ex: 6 frascos por caixa</p>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-indigo-600 uppercase mb-2">
                                Nome da unidade intermediaria <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="unidade_subunidade" id="unidade_sub"
                                   value="{{ old('unidade_subunidade', $insumo->unidade_subunidade) }}"
                                   placeholder="frasco, ampola, sache..." class="w-full rounded-xl border-indigo-200 bg-white focus:ring-brand-primary">
                            <p class="text-[10px] text-indigo-400 mt-1">Ex: frasco, ampola, sache. Deve ser diferente da unidade de compra.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-indigo-600 uppercase mb-2">
                                Quanto ha em cada unidade intermediaria? <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.0001" min="0.0001" name="quantidade_consumo_por_subunidade" id="qtd_consumo_sub"
                                   value="{{ old('quantidade_consumo_por_subunidade', $insumo->quantidade_consumo_por_subunidade) }}"
                                   placeholder="Ex: 100" class="w-full rounded-xl border-indigo-200 bg-white focus:ring-brand-primary">
                            <p class="text-[10px] text-indigo-400 mt-1">Ex: 1000 ml por frasco</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="conversao-preview" class="mt-4 hidden rounded-xl border border-violet-200 bg-violet-50 p-4">
                <p class="text-xs font-black text-violet-700 uppercase mb-1">Pre-visualizacao da conversao</p>
                <p class="text-xs text-violet-700 mb-2">Este quadro mostra apenas a estrutura operacional da conversao. Custos e precos devem ser ajustados nas entradas de estoque ou na importacao da NF-e.</p>
                <div id="conversao-linhas" class="space-y-1"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
                <h3 class="text-lg font-black text-slate-800 mb-6">4. Estoque e alertas</h3>
                <div class="space-y-4">
                    <label class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <span class="text-sm font-bold text-slate-700">Controlar estoque deste item?</span>
                        <input type="checkbox" name="controlar_estoque" value="1" {{ old('controlar_estoque', $insumo->controlar_estoque ?? true) ? 'checked' : '' }} class="rounded text-brand-primary focus:ring-brand-primary">
                    </label>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase mb-2">Estoque minimo</label>
                        <input type="number" step="0.0001" name="estoque_minimo" value="{{ old('estoque_minimo', $insumo->estoque_minimo ?? 0) }}" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase mb-2">Estoque maximo</label>
                        <input type="number" step="0.0001" name="estoque_maximo" value="{{ old('estoque_maximo', $insumo->estoque_maximo) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
                <h3 class="text-lg font-black text-slate-800 mb-6">5. Uso no custo e precificacao</h3>
                <label class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-3 mb-4">
                    <span class="text-sm font-bold text-slate-700">Usar no custo do produto?</span>
                    <input type="checkbox" name="usar_na_precificacao" value="1" {{ old('usar_na_precificacao', $insumo->usar_na_precificacao ?? true) ? 'checked' : '' }} class="rounded text-brand-primary focus:ring-brand-primary">
                </label>
                <p class="text-xs text-slate-500">Se desmarcado, o item pode existir no cadastro e ate no estoque, mas nao entra no custo principal da precificacao.</p>

                @if($isEdit)
                <div class="mt-4 rounded-xl bg-brand-secondary p-4 text-white">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-1">Referencia financeira</p>
                    <p class="text-xs opacity-80 mb-3">Somente leitura. Ajustes de custo devem ser feitos pela entrada de estoque ou pela importacao da NF-e.</p>
                    @if($resumoCustosConversao && ($resumoCustosConversao['configuracao_compra_invalida'] ?? false))
                        <p class="text-xs font-bold text-amber-200 mb-2">Configuracao invalida detectada: unidade de compra e unidade intermediaria representam o mesmo item. Ajuste para evitar distorcao na preview.</p>
                    @endif
                    @if($resumoCustosConversao && $resumoCustosConversao['tem_conversao'])
                        <p class="text-xs opacity-70">Custo por unidade de estoque ({{ $resumoCustosConversao['unidade_consumo'] }})</p>
                        <p class="text-xl font-black">R$ {{ number_format($resumoCustosConversao['custo_por_unidade_base'], 6, ',', '.') }}</p>
                        <p class="text-xs opacity-70 mt-2">Custo por unidade de compra ({{ $resumoCustosConversao['unidade_compra'] }}): R$ {{ number_format($resumoCustosConversao['custo_por_unidade_compra'], 2, ',', '.') }}</p>
                        @if($resumoCustosConversao['tem_dois_niveis'] && $resumoCustosConversao['custo_por_subunidade'] !== null)
                            <p class="text-xs opacity-70">Custo por unidade intermediaria ({{ $resumoCustosConversao['unidade_subunidade'] }}): R$ {{ number_format($resumoCustosConversao['custo_por_subunidade'], 4, ',', '.') }}</p>
                        @endif
                    @else
                        <p class="text-xs opacity-70">Custo por unidade de estoque</p>
                        <p class="text-xl font-black">R$ {{ number_format($insumo->getCustoEfetivo(), 6, ',', '.') }}</p>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
            <h3 class="text-lg font-black text-slate-800 mb-4">6. Observacoes</h3>
            <textarea name="observacao" rows="4" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">{{ old('observacao', $insumo->observacao) }}</textarea>
        </div>

        @if($isEdit)
            <button type="submit" class="w-full rounded-2xl bg-brand-primary py-5 text-center font-black text-white shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-sm">
                Salvar alteracoes
            </button>
        @else
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <button type="submit" name="submit_action" value="save" class="w-full rounded-2xl bg-brand-primary py-5 text-center font-black text-white shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-sm">
                    Cadastrar item
                </button>
                <button type="submit" name="submit_action" value="save_and_entry" class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 py-5 text-center font-black text-emerald-700 shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-sm">
                    Cadastrar item e registrar entrada inicial
                </button>
            </div>
            <p class="text-xs font-medium text-slate-500">A segunda opcao reaproveita o fluxo atual de entrada de estoque, sem gravar custo sem movimentacao.</p>
        @endif
    </form>

    <script>
        (function () {
            var tipoSelect   = document.getElementById('tipo_item_operacional');
            var tipoHelp     = document.getElementById('tipo-item-help');
            var unidadeCompra  = document.getElementById('unidade_compra');
            var qtdPorCompra   = document.getElementById('quantidade_por_compra');
            var unidadeMedida  = document.getElementById('unidade_medida');
            var preview        = document.getElementById('conversao-preview');
            var previewLinhas  = document.getElementById('conversao-linhas');

            var blocoQtdPorCompra = document.getElementById('bloco-qtd-por-compra');
            var blocoInterno   = document.getElementById('bloco-embalagem-interna');
            var chkDoisNiveis  = document.getElementById('tem_embalagem_interna');
            var camposDois     = document.getElementById('campos-dois-niveis');
            var qtdSub         = document.getElementById('qtd_sub');
            var unidadeSub     = document.getElementById('unidade_sub');
            var qtdConsumSub   = document.getElementById('qtd_consumo_sub');

            function atualizarAjudaTipo() {
                var msg = {
                    consumivel: 'Consumivel: entra no custo principal do produto.',
                    embalagem:  'Embalagem: item de acondicionamento ou apresentacao.',
                    componente: 'Componente: parte complementar de um conjunto.',
                    apoio:      'Apoio: item auxiliar que pode ou nao ser controlado.',
                    ignorado:   'Ignorado: nao entra no fluxo operacional principal.'
                };
                tipoHelp.textContent = msg[tipoSelect.value] || msg.consumivel;
            }

            function fmt(n) {
                return parseFloat(n).toLocaleString('pt-BR', { maximumFractionDigits: 6 });
            }

            function atualizarConversao() {
                var uc  = unidadeCompra.value;
                var qtd = parseFloat(qtdPorCompra.value) || 0;
                var um  = unidadeMedida.value;

                // Mostrar/ocultar bloco de embalagem interna
                if (uc) {
                    blocoInterno.classList.remove('hidden');
                } else {
                    blocoInterno.classList.add('hidden');
                    chkDoisNiveis.checked = false;
                    camposDois.classList.add('hidden');
                }

                if (!uc || qtd <= 0 || !um) {
                    preview.classList.add('hidden');
                    previewLinhas.innerHTML = '';
                    return;
                }

                var doisNiveis = chkDoisNiveis.checked;
                var sub        = parseFloat((qtdSub && qtdSub.value) || 0);
                var nomeSub    = (unidadeSub && unidadeSub.value.trim()) || '';
                var consumSub  = parseFloat((qtdConsumSub && qtdConsumSub.value) || 0);

                var html = '';

                function normalizarUnidadeToken(valor) {
                    if (!valor) return '';
                    var token = valor.toString().toLowerCase().trim();
                    token = token
                        .replace(/[áàâã]/g, 'a')
                        .replace(/[éê]/g, 'e')
                        .replace(/[í]/g, 'i')
                        .replace(/[óôõ]/g, 'o')
                        .replace(/[ú]/g, 'u')
                        .replace(/[ç]/g, 'c')
                        .replace(/\s+/g, '');

                    if (token.length > 3 && token.endsWith('s')) {
                        token = token.slice(0, -1);
                    }

                    return token;
                }

                if (doisNiveis && sub > 0 && nomeSub && consumSub > 0) {
                    if (normalizarUnidadeToken(uc) === normalizarUnidadeToken(nomeSub)) {
                        preview.classList.remove('hidden');
                        previewLinhas.innerHTML = '<p class="text-sm font-bold text-red-700">Configuracao invalida: a unidade de compra e a unidade intermediaria nao podem ter o mesmo nome.</p><p class="text-xs text-red-600">Se a compra vem em 4 frascos, troque a unidade de compra para caixa, kit ou outro nome equivalente.</p>';
                        return;
                    }

                    var fatorTotal = sub * consumSub;
                    html += '<p class="text-sm font-bold text-violet-800">Unidade de estoque: ' + um + '</p>';
                    html += '<p class="text-sm font-bold text-violet-800">Compra em: ' + uc + '</p>';
                    html += '<p class="text-sm font-bold text-violet-800">Cada ' + uc + ' possui: ' + fmt(sub) + ' ' + nomeSub + '</p>';
                    html += '<p class="text-sm font-bold text-violet-800">Cada ' + nomeSub + ' possui: ' + fmt(consumSub) + ' ' + um + '</p>';
                    html += '<p class="text-sm font-bold text-violet-800">Total por ' + uc + ': ' + fmt(fatorTotal) + ' ' + um + '</p>';
                } else {
                    html += '<p class="text-sm font-bold text-violet-800">Unidade de estoque: ' + um + '</p>';
                    html += '<p class="text-sm font-bold text-violet-800">Compra em: ' + uc + '</p>';
                    html += '<p class="text-sm font-bold text-violet-800">Cada ' + uc + ' equivale a: ' + fmt(qtd) + ' ' + um + '</p>';
                }

                preview.classList.remove('hidden');
                previewLinhas.innerHTML = html;
            }

            // Toggle campos dois niveis
            chkDoisNiveis.addEventListener('change', function () {
                if (this.checked) {
                    camposDois.classList.remove('hidden');
                    // Oculta o campo simples para evitar conflito visual e semantico
                    blocoQtdPorCompra.classList.add('hidden');
                } else {
                    camposDois.classList.add('hidden');
                    blocoQtdPorCompra.classList.remove('hidden');
                }
                atualizarConversao();
            });

            // Ao carregar: se dois niveis ja ativos (edit mode), ocultar campo simples
            if (chkDoisNiveis.checked) {
                blocoQtdPorCompra.classList.add('hidden');
            }

            tipoSelect.addEventListener('change', atualizarAjudaTipo);
            unidadeCompra.addEventListener('change', atualizarConversao);
            qtdPorCompra.addEventListener('input', atualizarConversao);
            unidadeMedida.addEventListener('change', atualizarConversao);
            if (qtdSub)       qtdSub.addEventListener('input', atualizarConversao);
            if (unidadeSub)   unidadeSub.addEventListener('input', atualizarConversao);
            if (qtdConsumSub) qtdConsumSub.addEventListener('input', atualizarConversao);

            atualizarAjudaTipo();
            atualizarConversao();
        })();
    </script>
</x-layouts.app>
