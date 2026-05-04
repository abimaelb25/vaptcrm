{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:15
--}}
<x-layouts.app>
    @php
        $saldoAtual = (float) $insumo->estoque_atual;
        $temConversao = $insumo->temConversaoUnidade();

        if ($insumo->temDoisNiveisConversao()) {
            $unidadeReferenciaContagem = (string) $insumo->unidade_subunidade;
            $quantidadeBaseReferencia = (float) $insumo->quantidade_consumo_por_subunidade;
        } elseif ($temConversao) {
            $unidadeReferenciaContagem = (string) $insumo->unidade_compra;
            $quantidadeBaseReferencia = (float) $insumo->quantidade_por_compra;
        } else {
            $unidadeReferenciaContagem = (string) $insumo->unidade_medida;
            $quantidadeBaseReferencia = 1.0;
        }

        if ($insumo->temDoisNiveisConversao()) {
            $referenciaCompra = sprintf(
                '%s com %s %s de %s %s',
                $insumo->unidade_compra,
                rtrim(rtrim(number_format((float) $insumo->quantidade_subunidades_por_compra, 4, '.', ''), '0'), '.'),
                $insumo->unidade_subunidade,
                rtrim(rtrim(number_format((float) $insumo->quantidade_consumo_por_subunidade, 4, '.', ''), '0'), '.'),
                $insumo->unidade_medida
            );
        } elseif ($insumo->temConversaoUnidade()) {
            $referenciaCompra = sprintf(
                '%s com %s %s',
                $insumo->unidade_compra,
                rtrim(rtrim(number_format((float) $insumo->quantidade_por_compra, 4, '.', ''), '0'), '.'),
                $insumo->unidade_medida
            );
        } else {
            $referenciaCompra = null;
        }
    @endphp

    <div class="mb-8 max-w-lg mx-auto">
        <h1 class="text-3xl font-black text-brand-secondary">Ajustar <span class="text-orange-500">Saldo Fisico</span></h1>
        <p class="text-slate-500 font-medium">Corrija saldo apos contagem fisica. Esta tela nao representa compra de fornecedor.</p>
    </div>

    <div class="max-w-lg mx-auto rounded-3xl bg-white p-8 shadow-2xl border border-orange-100">
        <div class="mb-5 rounded-xl border border-orange-100 bg-orange-50 p-4">
            <p class="text-xs font-black uppercase text-orange-700">Proposito da tela</p>
            <p class="text-sm font-semibold text-orange-800">Use para ajuste inventarial. Para compras e custo de fornecedor, use Registrar entrada.</p>
        </div>

        <div class="mb-6 p-4 bg-orange-50 rounded-xl border border-orange-100 flex items-center gap-4">
            <span class="text-3xl">⚖️</span>
            <div>
                <p class="text-xs font-black text-orange-600 uppercase">Ajustando Item:</p>
                <p class="text-xl font-black text-slate-800">{{ $insumo->nome }}</p>
                <p class="text-sm font-bold text-slate-500">Saldo Atual no Sistema: {{ number_format($saldoAtual, 2, ',', '.') }} {{ $insumo->unidade_medida }}</p>
                <p class="text-xs font-semibold text-slate-500 mt-1">Unidade operacional: {{ $insumo->unidade_medida }}</p>
            </div>
        </div>

        <form action="{{ route('admin.inventory.insumos.processar-ajuste', $insumo) }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                @if($temConversao)
                    <div class="mb-4 rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                        <p class="text-xs font-black text-indigo-700 uppercase mb-2">Modo de ajuste</p>
                        <div class="flex flex-col gap-2 text-sm font-semibold text-indigo-800">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="modo_ajuste" value="direto" id="modo_direto" {{ old('modo_ajuste', 'direto') !== 'guiado' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                <span>Direto: informar novo saldo em {{ $insumo->unidade_medida }}</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="modo_ajuste" value="guiado" id="modo_guiado" {{ old('modo_ajuste') === 'guiado' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                <span>Guiado: contar {{ $unidadeReferenciaContagem }}s e saldo por {{ $unidadeReferenciaContagem }}</span>
                            </label>
                        </div>

                        <div id="bloco_modo_guiado" class="mt-3 {{ old('modo_ajuste') === 'guiado' ? '' : 'hidden' }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-black text-indigo-700 uppercase mb-1">Quantidade de {{ $unidadeReferenciaContagem }}s contados</label>
                                    <input type="number" step="0.0001" min="0" name="qtd_embalagens_contadas" id="qtd_embalagens_contadas" value="{{ old('qtd_embalagens_contadas') }}" class="w-full rounded-xl border-indigo-200 bg-white focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black text-indigo-700 uppercase mb-1">Saldo por {{ $unidadeReferenciaContagem }} ({{ $insumo->unidade_medida }})</label>
                                    <input type="number" step="0.0001" min="0" name="volume_por_embalagem" id="volume_por_embalagem" value="{{ old('volume_por_embalagem') }}" placeholder="Ex: 700" class="w-full rounded-xl border-indigo-200 bg-white focus:ring-indigo-500">
                                </div>
                            </div>
                            <p class="text-[11px] text-indigo-700 mt-2">Exemplo: 4 {{ $unidadeReferenciaContagem }}s com 700 {{ $insumo->unidade_medida }} cada = 2800 {{ $insumo->unidade_medida }}.</p>
                            <p class="text-[11px] text-indigo-600">Referencia da conversao: 1 {{ $unidadeReferenciaContagem }} = {{ rtrim(rtrim(number_format($quantidadeBaseReferencia, 4, '.', ''), '0'), '.') }} {{ $insumo->unidade_medida }}.</p>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="modo_ajuste" value="direto">
                @endif

                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Novo Saldo Real (Contagem Física) <span class="text-red-500">*</span></label>
                <input id="novo_saldo" type="number" step="0.0001" min="0" name="quantidade" required value="{{ old('quantidade', number_format($saldoAtual, 4, '.', '')) }}" class="w-full rounded-xl border-slate-200 bg-orange-50/20 focus:ring-orange-500 text-3xl font-black text-slate-800">
                <p class="mt-2 text-[11px] text-slate-400 font-medium italic">Informe a contagem física real encontrada.</p>
            </div>

            <div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-black text-slate-500 uppercase mb-3">Resumo dinâmico do impacto</p>
                    <div class="space-y-1 text-sm font-semibold text-slate-700">
                        <p>Saldo atual: <span id="resumo_saldo_atual">{{ number_format($saldoAtual, 2, ',', '.') }}</span> {{ $insumo->unidade_medida }}</p>
                        <p>Novo saldo informado: <span id="resumo_novo_saldo">{{ number_format($saldoAtual, 2, ',', '.') }}</span> {{ $insumo->unidade_medida }}</p>
                        <p>Diferenca que sera lancada: <span id="resumo_diferenca" class="font-black text-slate-700">0,00</span> {{ $insumo->unidade_medida }}</p>
                        <p>Tipo do ajuste: <span id="resumo_tipo" class="font-black text-slate-700">Sem alteracao</span></p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4 space-y-1">
                <p class="text-xs font-black text-indigo-700 uppercase">Referencia operacional</p>
                <p class="text-sm font-semibold text-indigo-800">Voce esta ajustando este item na unidade: {{ $insumo->unidade_medida }}</p>
                @if($referenciaCompra)
                    <p class="text-xs font-semibold text-indigo-700">Referencia de compra: {{ $referenciaCompra }}</p>
                @endif
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Motivo do ajuste <span class="text-red-500">*</span></label>
                <select id="motivo_rapido" name="motivo_rapido" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-orange-500">
                    <option value="">Selecione...</option>
                    <option value="balanco_mensal" {{ old('motivo_rapido') === 'balanco_mensal' ? 'selected' : '' }}>Balanco mensal</option>
                    <option value="correcao_lancamento" {{ old('motivo_rapido') === 'correcao_lancamento' ? 'selected' : '' }}>Correcao de lancamento</option>
                    <option value="perda_vazamento" {{ old('motivo_rapido') === 'perda_vazamento' ? 'selected' : '' }}>Perda / vazamento</option>
                    <option value="inventario_fisico" {{ old('motivo_rapido') === 'inventario_fisico' ? 'selected' : '' }}>Inventario fisico</option>
                    <option value="ajuste_manual" {{ old('motivo_rapido') === 'ajuste_manual' ? 'selected' : '' }}>Ajuste manual</option>
                    <option value="outro" {{ old('motivo_rapido') === 'outro' ? 'selected' : '' }}>Outro</option>
                </select>
                <div class="mt-3">
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Detalhe complementar <span id="detalhe_obrigatorio" class="text-red-500 hidden">*</span></label>
                    <input id="detalhe_motivo" type="text" name="detalhe_motivo" value="{{ old('detalhe_motivo') }}" placeholder="Ex: Divergencia encontrada no inventario do setor A" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-orange-500">
                    <p class="mt-1 text-[11px] text-slate-500">Use para detalhar a justificativa quando necessario.</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold text-slate-600">Esta acao gera uma movimentacao de ajuste e ficara registrada no historico do estoque.</p>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full rounded-2xl bg-orange-500 py-5 text-center font-black text-white shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-sm">
                    Confirmar Ajuste de Saldo
                </button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var saldoAtual = {{ number_format($saldoAtual, 4, '.', '') }};

            var inputNovoSaldo = document.getElementById('novo_saldo');
            var modoDireto = document.getElementById('modo_direto');
            var modoGuiado = document.getElementById('modo_guiado');
            var blocoModoGuiado = document.getElementById('bloco_modo_guiado');
            var qtdEmbalagens = document.getElementById('qtd_embalagens_contadas');
            var volumePorEmbalagem = document.getElementById('volume_por_embalagem');
            var resumoAtual = document.getElementById('resumo_saldo_atual');
            var resumoNovo = document.getElementById('resumo_novo_saldo');
            var resumoDiferenca = document.getElementById('resumo_diferenca');
            var resumoTipo = document.getElementById('resumo_tipo');
            var motivoRapido = document.getElementById('motivo_rapido');
            var detalheMotivo = document.getElementById('detalhe_motivo');
            var detalheObrigatorio = document.getElementById('detalhe_obrigatorio');

            function formatarNumero(valor) {
                return Number(valor || 0).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 4
                });
            }

            function atualizarResumo() {
                if (modoGuiado && modoGuiado.checked && qtdEmbalagens && volumePorEmbalagem) {
                    var qtd = parseFloat(qtdEmbalagens.value);
                    var volume = parseFloat(volumePorEmbalagem.value);

                    if (!Number.isNaN(qtd) && !Number.isNaN(volume)) {
                        inputNovoSaldo.value = (qtd * volume).toFixed(4);
                    }
                }

                var novoSaldo = parseFloat(inputNovoSaldo.value);
                if (Number.isNaN(novoSaldo)) {
                    novoSaldo = 0;
                }

                var diferenca = novoSaldo - saldoAtual;

                resumoAtual.textContent = formatarNumero(saldoAtual);
                resumoNovo.textContent = formatarNumero(novoSaldo);
                resumoDiferenca.textContent = (diferenca > 0 ? '+' : '') + formatarNumero(diferenca);

                resumoDiferenca.classList.remove('text-emerald-600', 'text-red-600', 'text-slate-700');
                resumoTipo.classList.remove('text-emerald-600', 'text-red-600', 'text-slate-700');

                if (diferenca > 0) {
                    resumoTipo.textContent = 'Entrada por ajuste';
                    resumoDiferenca.classList.add('text-emerald-600');
                    resumoTipo.classList.add('text-emerald-600');
                } else if (diferenca < 0) {
                    resumoTipo.textContent = 'Saida por ajuste';
                    resumoDiferenca.classList.add('text-red-600');
                    resumoTipo.classList.add('text-red-600');
                } else {
                    resumoTipo.textContent = 'Sem alteracao';
                    resumoDiferenca.classList.add('text-slate-700');
                    resumoTipo.classList.add('text-slate-700');
                }
            }

            function atualizarRegraDetalhe() {
                var obrigatorio = motivoRapido.value === 'outro';
                detalheObrigatorio.classList.toggle('hidden', !obrigatorio);

                if (obrigatorio) {
                    detalheMotivo.setAttribute('required', 'required');
                } else {
                    detalheMotivo.removeAttribute('required');
                }
            }

            function atualizarModoAjuste() {
                if (!modoGuiado || !modoDireto || !blocoModoGuiado) {
                    atualizarResumo();
                    return;
                }

                var guiadoAtivo = modoGuiado.checked;
                blocoModoGuiado.classList.toggle('hidden', !guiadoAtivo);

                if (qtdEmbalagens) {
                    qtdEmbalagens.toggleAttribute('required', guiadoAtivo);
                }

                if (volumePorEmbalagem) {
                    volumePorEmbalagem.toggleAttribute('required', guiadoAtivo);
                }

                inputNovoSaldo.readOnly = guiadoAtivo;
                inputNovoSaldo.classList.toggle('bg-indigo-50', guiadoAtivo);

                atualizarResumo();
            }

            inputNovoSaldo.addEventListener('input', atualizarResumo);
            motivoRapido.addEventListener('change', atualizarRegraDetalhe);
            if (modoDireto) modoDireto.addEventListener('change', atualizarModoAjuste);
            if (modoGuiado) modoGuiado.addEventListener('change', atualizarModoAjuste);
            if (qtdEmbalagens) qtdEmbalagens.addEventListener('input', atualizarResumo);
            if (volumePorEmbalagem) volumePorEmbalagem.addEventListener('input', atualizarResumo);

            atualizarResumo();
            atualizarRegraDetalhe();
            atualizarModoAjuste();
        })();
    </script>
</x-layouts.app>
