<x-layouts.app>
    <div class="mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Conferencia <span class="text-brand-primary">NF-e XML</span></h1>
            @if($importacao->status === 'confirmada')
                <p class="text-slate-500 font-medium">Esta nota ja foi confirmada. Reabra para editar os mapeamentos e reimportar.</p>
            @else
                <p class="text-slate-500 font-medium">Classifique cada item com clareza antes de confirmar o impacto no estoque e no custo.</p>
            @endif
        </div>
        <a href="{{ route('admin.inventory.nfe-importacao.index') }}" class="text-sm font-bold text-slate-400 hover:text-brand-primary transition">&larr; Voltar para historico</a>
    </div>

    @if($importacao->status === 'confirmada')
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase text-emerald-700 mb-1">Nota ja confirmada</p>
                <p class="text-sm text-emerald-800">Esta NF-e foi importada em <strong>{{ $importacao->confirmada_em?->format('d/m/Y H:i') }}</strong>. O estoque e os custos ja foram atualizados.</p>
                <p class="text-xs text-emerald-700 mt-1">Ao reabrir, as movimentacoes de estoque geradas por esta nota serao revertidas e voce podera confirmar novamente com novas definicoes.</p>
            </div>
            <form method="POST" action="{{ route('admin.inventory.nfe-importacao.reabrir', $importacao) }}" onsubmit="return confirm('Confirma a reabertura desta nota? As movimentacoes de estoque geradas serao revertidas.')">
                @csrf
                <button type="submit" class="whitespace-nowrap rounded-2xl bg-amber-500 px-6 py-3 text-center font-black text-white shadow hover:-translate-y-0.5 transition uppercase tracking-widest text-xs">
                    Reabrir nota
                </button>
            </form>
        </div>
    @endif

    @if(!empty($alertas))
        <div class="mb-6 rounded-2xl border border-orange-200 bg-orange-50 p-4">
            <p class="text-xs font-black uppercase text-orange-700 mb-2">Atencao</p>
            <ul class="space-y-1 text-sm text-orange-800">
                @foreach($alertas as $alerta)
                    <li>{{ $alerta }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($importacao->status !== 'confirmada')
    <div class="mb-6 rounded-2xl border border-sky-200 bg-sky-50 p-4">
        <p class="text-xs font-black uppercase text-sky-700 mb-2">Como preencher unidades nesta conferencia</p>
        <ul class="space-y-1 text-sm text-sky-800">
            <li>A unidade fiscal da NF-e, como UNID, UN ou CX, descreve como o fornecedor vendeu o item e deve alimentar Compra em.</li>
            <li>Unidade de estoque/consumo e a unidade real usada internamente na grafica, como folha, ml, m2, pacote ou resma.</li>
            <li>O custo da nota sera aplicado nesta importacao usando a unidade fiscal de compra; aqui voce deve apenas estruturar corretamente o cadastro.</li>
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-100 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-400 uppercase mb-4">Cabecalho da nota</p>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400">Fornecedor</p>
                    <p class="text-sm font-bold text-slate-700">{{ $payload['fornecedor']['nome'] ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400">Numero / Serie</p>
                    <p class="text-sm font-bold text-slate-700">{{ $payload['numero'] ?? '-' }} / {{ $payload['serie'] ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400">Data</p>
                    <p class="text-sm font-bold text-slate-700">{{ $payload['data_emissao'] ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400">Valor total</p>
                    <p class="text-sm font-black text-slate-800">R$ {{ number_format((float) ($payload['valor_total'] ?? 0), 2, ',', '.') }}</p>
                </div>
                <div class="md:col-span-4">
                    <p class="text-[10px] font-black uppercase text-slate-400">Chave NF-e</p>
                    <p class="text-xs font-bold text-slate-700 break-all">{{ $payload['chave_nfe'] ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-100 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-400 uppercase mb-3">Resumo inicial</p>
            <div class="space-y-1 text-sm font-bold text-slate-700">
                <p>Total de itens: <span id="sum-itens">0</span></p>
                <p>Consumiveis: <span id="sum-consumiveis">0</span></p>
                <p>Embalagem/Componente: <span id="sum-embalagens">0</span></p>
                <p>Ignorados: <span id="sum-ignorados">0</span></p>
                <p>Pendentes: <span id="sum-pendentes">0</span></p>
            </div>
            @if($fornecedorExistente)
                <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-100 p-3 text-xs text-emerald-700">
                    Fornecedor ja cadastrado: <strong>{{ $fornecedorExistente->nome }}</strong>
                </div>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('admin.inventory.nfe-importacao.confirmar', $importacao) }}" class="space-y-6{{ $importacao->status === 'confirmada' ? ' opacity-50 pointer-events-none select-none' : '' }}" id="form-nfe-confirmacao" @if($importacao->status === 'confirmada') inert @endif>
        @csrf
        <input type="hidden" name="valor_total_nota" value="{{ (float) ($payload['valor_total'] ?? 0) }}">

        <div class="rounded-2xl bg-white border border-slate-100 p-5 shadow-sm">
            <p class="text-xs font-black uppercase text-slate-400 mb-3">Consumiveis sugeridos</p>
            <div id="grupo-consumiveis" class="space-y-4"></div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-100 p-5 shadow-sm">
            <p class="text-xs font-black uppercase text-slate-400 mb-3">Embalagens / Componentes sugeridos</p>
            <div id="grupo-embalagens" class="space-y-4"></div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-100 p-5 shadow-sm">
            <p class="text-xs font-black uppercase text-slate-400 mb-3">Itens sem definicao</p>
            <div id="grupo-pendentes" class="space-y-4"></div>
        </div>

        @foreach($itens as $idx => $item)
            @php
                $acaoDefault = ($item['sugestao_insumo_id'] ?? null) ? 'vincular' : 'criar';
                $acaoAtual = old("items.$idx.acao", $acaoDefault);
                $tipoSugerido = $item['sugestao_tipo_operacional'] ?? 'consumivel';
                $tipoAtual = old("items.$idx.tipo_item_operacional", $tipoSugerido);
                $tratamentoAtual = old("items.$idx.tratamento_financeiro", $tipoAtual === 'consumivel' ? 'custo_proprio' : 'ratear_consumiveis');
                $valorAlocado = old("items.$idx.valor_financeiro_alocado", (float) ($item['valor_total'] ?? 0));
            @endphp
            <div class="nfe-item-card hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                 data-item-index="{{ $idx }}"
                 data-suggested-tipo="{{ $tipoSugerido }}">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-3">
                    <div>
                        <p class="text-sm font-black text-slate-800">#{{ $item['numero_item'] ?? ($idx + 1) }} - {{ $item['descricao'] ?? '-' }}</p>
                        <p class="text-xs text-slate-500">Cod fornecedor: {{ $item['codigo_fornecedor'] ?? '-' }} | NCM: {{ $item['ncm'] ?? '-' }} | CFOP: {{ $item['cfop'] ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-500">Qtd {{ number_format((float) ($item['quantidade'] ?? 0), 4, ',', '.') }} {{ $item['unidade'] ?? '-' }}</p>
                        <p class="text-xs font-bold text-slate-500">Unit R$ {{ number_format((float) ($item['valor_unitario'] ?? 0), 6, ',', '.') }}</p>
                        <p class="text-sm font-black text-slate-800">Total R$ {{ number_format((float) ($item['valor_total'] ?? 0), 2, ',', '.') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Tipo operacional</label>
                        <select name="items[{{ $idx }}][tipo_item_operacional]" class="tipo-operacional-select w-full rounded-lg border-slate-200 text-xs font-bold" data-row="{{ $idx }}">
                            <option value="consumivel" {{ $tipoAtual === 'consumivel' ? 'selected' : '' }}>Consumivel</option>
                            <option value="embalagem" {{ $tipoAtual === 'embalagem' ? 'selected' : '' }}>Embalagem</option>
                            <option value="componente" {{ $tipoAtual === 'componente' ? 'selected' : '' }}>Componente</option>
                            <option value="apoio" {{ $tipoAtual === 'apoio' ? 'selected' : '' }}>Apoio</option>
                            <option value="ignorado" {{ $tipoAtual === 'ignorado' ? 'selected' : '' }}>Ignorado operacionalmente</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Acao</label>
                        <select name="items[{{ $idx }}][acao]" data-row="{{ $idx }}" class="acao-select w-full rounded-lg border-slate-200 text-xs font-bold">
                            <option value="criar" {{ $acaoAtual === 'criar' ? 'selected' : '' }}>Criar novo item</option>
                            <option value="vincular" {{ $acaoAtual === 'vincular' ? 'selected' : '' }}>Vincular existente</option>
                            <option value="ignorar" {{ $acaoAtual === 'ignorar' ? 'selected' : '' }}>Ignorar operacionalmente</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Tratamento financeiro</label>
                        <select name="items[{{ $idx }}][tratamento_financeiro]" class="tratamento-select w-full rounded-lg border-slate-200 text-xs" data-row="{{ $idx }}">
                            <option value="custo_proprio" {{ $tratamentoAtual === 'custo_proprio' ? 'selected' : '' }}>Custo proprio do item</option>
                            <option value="ratear_consumiveis" {{ $tratamentoAtual === 'ratear_consumiveis' ? 'selected' : '' }}>Ratear entre consumiveis</option>
                            <option value="custo_agregado" {{ $tratamentoAtual === 'custo_agregado' ? 'selected' : '' }}>Compor custo agregado</option>
                            <option value="desconsiderar" {{ $tratamentoAtual === 'desconsiderar' ? 'selected' : '' }}>Desconsiderar no custo</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Valor financeiro alocado</label>
                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][valor_financeiro_alocado]" value="{{ $valorAlocado }}" class="valor-alocado w-full rounded-lg border-slate-200 text-xs" data-row="{{ $idx }}">
                    </div>
                    <label class="desconsiderar-box hidden items-center gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-xs font-bold text-red-700">
                        <input type="checkbox" name="items[{{ $idx }}][confirmacao_desconsideracao]" value="1" class="rounded border-red-300 text-red-600 focus:ring-red-500">
                        Confirmo que este valor pode ser desconsiderado no custo.
                    </label>
                </div>

                <div id="vincular-{{ $idx }}" class="acao-bloco {{ $acaoAtual === 'vincular' ? '' : 'hidden' }} mb-3">
                    <select name="items[{{ $idx }}][insumo_id]" class="w-full rounded-lg border-slate-200 text-xs">
                        <option value="">Selecione um item existente...</option>
                        @foreach($insumosAtivos as $insumoOpt)
                            <option value="{{ $insumoOpt->id }}" {{ (string) old("items.$idx.insumo_id", $item['sugestao_insumo_id'] ?? null) === (string) $insumoOpt->id ? 'selected' : '' }}>
                                {{ $insumoOpt->nome }} ({{ $insumoOpt->tipo_item_operacional_label ?? ucfirst($insumoOpt->tipo_item_operacional ?? 'consumivel') }})
                            </option>
                        @endforeach
                    </select>
                    @error("items.$idx.insumo_id")
                        <p class="mt-1 text-[11px] font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="criar-{{ $idx }}" class="acao-bloco {{ $acaoAtual === 'criar' ? '' : 'hidden' }} rounded-lg border border-slate-200 bg-slate-50 p-3 space-y-2">
                    @php
                        $unidadeFiscal = trim((string) ($item['unidade'] ?? ''));
                        $unidadeFiscalUpper = strtoupper($unidadeFiscal);
                        $unidadeCompraSugerida = match ($unidadeFiscalUpper) {
                            'UN', 'UND', 'UNID', 'UNIDADE' => 'unidade',
                            default => mb_strtolower($unidadeFiscal),
                        };
                    @endphp
                    <input type="text" name="items[{{ $idx }}][novo_nome]" value="{{ old("items.$idx.novo_nome", $item['descricao'] ?? '') }}" placeholder="Nome interno do item" class="w-full rounded-lg border-slate-200 text-xs">
                    <div class="rounded-lg border border-sky-200 bg-sky-50 p-3 text-xs text-sky-800">
                        <p class="font-black uppercase mb-1">Unidade fiscal da nota</p>
                        <p>{{ $unidadeFiscalUpper !== '' ? $unidadeFiscalUpper : '-' }}. Use esse valor como referencia para <strong>Compra em</strong>. Informe abaixo a unidade real de estoque/consumo usada internamente.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="items[{{ $idx }}][categoria]" value="{{ old("items.$idx.categoria") }}" placeholder="Categoria" class="rounded-lg border-slate-200 text-xs">
                        <input type="text" name="items[{{ $idx }}][unidade_medida]" value="{{ old("items.$idx.unidade_medida", '') }}" placeholder="Unidade de estoque/consumo (ex: folha, ml, m2)" class="rounded-lg border-slate-200 text-xs">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="items[{{ $idx }}][unidade_compra]" value="{{ old("items.$idx.unidade_compra", $unidadeCompraSugerida) }}" placeholder="Compra em (unidade fiscal da nota)" class="rounded-lg border-slate-200 text-xs">
                        <input type="number" min="0.0001" step="0.0001" name="items[{{ $idx }}][quantidade_por_compra]" value="{{ old("items.$idx.quantidade_por_compra", 1) }}" placeholder="Quantas unidades de estoque ha em 1 compra" class="rounded-lg border-slate-200 text-xs">
                    </div>
                    <p class="text-[11px] text-slate-500">Exemplo: a nota veio em UNID ou pacote, mas o controle interno e em folha. Entao use Compra em = unidade/pacote e informe quantas folhas existem em cada compra.</p>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-600">
                            <input type="checkbox" name="items[{{ $idx }}][controlar_estoque]" value="1" {{ old("items.$idx.controlar_estoque", true) ? 'checked' : '' }} class="rounded text-brand-primary focus:ring-brand-primary">
                            Controlar estoque
                        </label>
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-600">
                            <input type="checkbox" name="items[{{ $idx }}][usar_na_precificacao]" value="1" {{ old("items.$idx.usar_na_precificacao", ($tipoAtual === 'consumivel')) ? 'checked' : '' }} class="rounded text-brand-primary focus:ring-brand-primary">
                            Usar na precificacao
                        </label>
                    </div>
                    @error("items.$idx.novo_nome")
                        <p class="text-[11px] font-bold text-red-600">{{ $message }}</p>
                    @enderror
                    @error("items.$idx.unidade_medida")
                        <p class="text-[11px] font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endforeach

        <div class="rounded-2xl bg-white border border-slate-100 p-5 shadow-sm space-y-3">
            <p class="text-sm font-black text-slate-700">Resumo final antes da confirmacao</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm font-bold text-slate-700">
                <p>Valor total da nota: <span id="resumo-valor-total">R$ 0,00</span></p>
                <p>Valor alocado a consumiveis: <span id="resumo-valor-consumiveis">R$ 0,00</span></p>
                <p>Valor alocado a embalagem/componente/apoio: <span id="resumo-valor-outros">R$ 0,00</span></p>
                <p>Valor desconsiderado: <span id="resumo-valor-desconsiderado">R$ 0,00</span></p>
                <p class="md:col-span-2 text-red-700">Valor ainda nao distribuido: <span id="resumo-valor-nao-alocado">R$ 0,00</span></p>
            </div>
            <label class="flex items-center gap-2 rounded-lg border border-orange-200 bg-orange-50 p-3 text-xs font-bold text-orange-700">
                <input type="checkbox" name="confirmar_custo_nao_alocado" value="1" class="rounded border-orange-300 text-orange-600 focus:ring-orange-500">
                Confirmo explicitamente a diferenca de custo nao alocado (quando houver).
            </label>
            <button type="submit" class="rounded-2xl bg-emerald-500 px-8 py-4 text-center font-black text-white shadow-lg hover:-translate-y-0.5 transition uppercase tracking-widest text-xs @if($importacao->status === 'confirmada') opacity-40 cursor-not-allowed @endif" @if($importacao->status === 'confirmada') disabled @endif>
                @if($importacao->status === 'confirmada')
                    Nota ja confirmada
                @else
                    Confirmar importacao
                @endif
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var cards = Array.from(document.querySelectorAll('.nfe-item-card'));
            var grupoConsumiveis = document.getElementById('grupo-consumiveis');
            var grupoEmbalagens = document.getElementById('grupo-embalagens');
            var grupoPendentes = document.getElementById('grupo-pendentes');
            var valorTotalNota = parseFloat('{{ (float) ($payload['valor_total'] ?? 0) }}') || 0;

            function moeda(v) {
                return 'R$ ' + (v || 0).toFixed(2).replace('.', ',');
            }

            function refreshAcoes() {
                cards.forEach(function (card) {
                    var idx = card.getAttribute('data-item-index');
                    var acao = card.querySelector('.acao-select').value;
                    var tipo = card.querySelector('.tipo-operacional-select').value;
                    var tratamento = card.querySelector('.tratamento-select').value;

                    var blocoCriar = document.getElementById('criar-' + idx);
                    var blocoVincular = document.getElementById('vincular-' + idx);
                    var boxDesconsiderar = card.querySelector('.desconsiderar-box');

                    blocoCriar.classList.toggle('hidden', acao !== 'criar');
                    blocoVincular.classList.toggle('hidden', acao !== 'vincular');

                    if (tipo === 'ignorado') {
                        card.querySelector('.acao-select').value = 'ignorar';
                    }

                    boxDesconsiderar.classList.toggle('hidden', tratamento !== 'desconsiderar');
                    boxDesconsiderar.classList.toggle('flex', tratamento === 'desconsiderar');
                });
            }

            function distribuirCards() {
                grupoConsumiveis.innerHTML = '';
                grupoEmbalagens.innerHTML = '';
                grupoPendentes.innerHTML = '';

                cards.forEach(function (card) {
                    card.classList.remove('hidden');
                    var tipo = card.querySelector('.tipo-operacional-select').value;
                    if (tipo === 'consumivel') {
                        grupoConsumiveis.appendChild(card);
                    } else if (tipo === 'embalagem' || tipo === 'componente' || tipo === 'apoio') {
                        grupoEmbalagens.appendChild(card);
                    } else {
                        grupoPendentes.appendChild(card);
                    }
                });
            }

            function atualizarResumos() {
                var total = cards.length;
                var consumiveis = 0;
                var embalagens = 0;
                var ignorados = 0;
                var pendentes = 0;

                var valorConsumiveis = 0;
                var valorOutros = 0;
                var valorDesconsiderado = 0;

                cards.forEach(function (card) {
                    var tipo = card.querySelector('.tipo-operacional-select').value;
                    var acao = card.querySelector('.acao-select').value;
                    var tratamento = card.querySelector('.tratamento-select').value;
                    var valor = parseFloat(card.querySelector('.valor-alocado').value) || 0;

                    if (tipo === 'consumivel') consumiveis++;
                    if (tipo === 'embalagem' || tipo === 'componente' || tipo === 'apoio') embalagens++;
                    if (tipo === 'ignorado' || acao === 'ignorar') ignorados++;
                    if ((acao === 'criar' && !card.querySelector('input[name$="[novo_nome]"]').value.trim()) || (acao === 'vincular' && !card.querySelector('select[name$="[insumo_id]"]').value)) {
                        pendentes++;
                    }

                    if (tratamento === 'desconsiderar') {
                        valorDesconsiderado += valor;
                    } else if (tipo === 'consumivel') {
                        valorConsumiveis += valor;
                    } else {
                        valorOutros += valor;
                    }
                });

                var valorNaoAlocado = valorTotalNota - valorConsumiveis - valorOutros - valorDesconsiderado;

                document.getElementById('sum-itens').textContent = total;
                document.getElementById('sum-consumiveis').textContent = consumiveis;
                document.getElementById('sum-embalagens').textContent = embalagens;
                document.getElementById('sum-ignorados').textContent = ignorados;
                document.getElementById('sum-pendentes').textContent = pendentes;

                document.getElementById('resumo-valor-total').textContent = moeda(valorTotalNota);
                document.getElementById('resumo-valor-consumiveis').textContent = moeda(valorConsumiveis);
                document.getElementById('resumo-valor-outros').textContent = moeda(valorOutros);
                document.getElementById('resumo-valor-desconsiderado').textContent = moeda(valorDesconsiderado);
                document.getElementById('resumo-valor-nao-alocado').textContent = moeda(valorNaoAlocado);
            }

            cards.forEach(function (card) {
                card.querySelectorAll('select, input').forEach(function (el) {
                    el.addEventListener('change', function () {
                        refreshAcoes();
                        distribuirCards();
                        atualizarResumos();
                    });
                    el.addEventListener('input', atualizarResumos);
                });
            });

            refreshAcoes();
            distribuirCards();
            atualizarResumos();
        });
    </script>
</x-layouts.app>
