{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-10
--}}
<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-3xl font-black text-brand-secondary">Calculadora e Precificação</h1>
        <p class="text-slate-500 mt-1 font-semibold">Simulador ágil de orçamentos para balcão, lona, adesivos e catálogo.</p>
    </div>

    @if(session('sucesso'))
        <div class="mb-6 rounded-xl bg-status-success/10 border border-status-success/20 p-4 text-status-success font-bold flex items-center gap-3">
            <span class="text-2xl">✅</span> {{ session('sucesso') }}
        </div>
    @endif
    @if(session('erro'))
        <div class="mb-6 rounded-xl bg-red-100 border border-red-200 p-4 text-red-600 font-bold flex items-center gap-3">
            <span class="text-2xl">⚠️</span> {{ session('erro') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- ÁREA ESQUERDA: CALCULADORA -->
        <div class="lg:col-span-8 bg-white border border-slate-100 rounded-2xl shadow-lg p-6 lg:p-8 relative overflow-hidden">
            <!-- Efeito Blur Top Right -->
            <div class="absolute -top-16 -right-16 w-48 h-48 bg-brand-primary opacity-5 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Abas Nativas -->
            <div class="flex gap-2 border-b border-slate-100 pb-4 mb-6">
                <button type="button" id="tab-dinamico" onclick="switchMode('dinamico')" class="px-6 py-2.5 rounded-full font-bold text-sm bg-brand-primary text-white shadow shadow-orange-200 transition-all">
                    📏 Área de M² (Lonas, Adesivos)
                </button>
                <button type="button" id="tab-fixo" onclick="switchMode('fixo')" class="px-6 py-2.5 rounded-full font-bold text-sm text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-all">
                    📦 Itens Fixos (Cartões, Outros)
                </button>
            </div>

            <!-- FORMULÁRIO INTERATIVO -->
            <form id="form-calculadora" class="space-y-6 relative z-10" oninput="calcular()">
                
                <!-- Secão Fixo (Inicia Oculta) -->
                <div id="secao-fixo" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-xl border border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Custo Fixo Total (O Milheiro/CX)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-bold">R$</span>
                            <input type="number" id="custo-fixo" step="0.01" min="0" class="w-full rounded border-slate-200 pl-10 h-11 focus:ring-brand-primary focus:border-brand-primary transition shadow-sm font-bold text-slate-700" placeholder="0.00" value="0.00">
                        </div>
                    </div>
                </div>

                <!-- Secão Dinâmica (M2) -->
                <div id="secao-dinamico" class="space-y-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- Largura -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Largura (m)</label>
                            <input type="number" id="dim-largura" step="0.01" min="0" class="w-full rounded border-slate-200 h-11 focus:ring-brand-primary focus:border-brand-primary transition shadow-sm font-bold text-slate-700 text-center" placeholder="1.00" value="1.00">
                        </div>
                        <!-- Altura -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Altura (m)</label>
                            <input type="number" id="dim-altura" step="0.01" min="0" class="w-full rounded border-slate-200 h-11 focus:ring-brand-primary focus:border-brand-primary transition shadow-sm font-bold text-slate-700 text-center" placeholder="1.00" value="1.00">
                        </div>
                        <!-- Qtd -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Quantidade (un)</label>
                            <input type="number" id="dim-qtd" step="1" min="1" class="w-full rounded border-slate-200 h-11 focus:ring-brand-primary focus:border-brand-primary transition shadow-sm font-black text-brand-primary text-center bg-orange-50/50" placeholder="1" value="1">
                        </div>
                        <!-- Custo Base -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">R$ Base Substrato</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-slate-400 font-bold">R$</span>
                                <input type="number" id="custo-m2" step="0.01" min="0" class="w-full rounded border-slate-200 pl-9 h-11 focus:ring-brand-primary focus:border-brand-primary transition shadow-sm font-bold text-slate-700" placeholder="15.00" value="15.00">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/50 p-5 rounded-xl border border-slate-100">
                        <!-- Quebra e Desperdício -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-2 flex items-center gap-1">
                                <span>✂️</span> Margem de Perda / Setup (%)
                            </label>
                            <input type="number" id="taxa-perda" step="1" min="0" class="w-full rounded border-slate-200 h-10 focus:ring-brand-primary transition shadow-sm font-bold text-slate-600" placeholder="10" value="10">
                        </div>
                        <!-- Acabamentos Adicionais Brutos -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-2 flex items-center gap-1">
                                <span>🔗</span> Acabamentos e Insumos (R$)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-slate-400 font-bold">R$</span>
                                <input type="number" id="taxa-acabamento" step="0.01" min="0" class="w-full rounded border-slate-200 pl-9 h-10 focus:ring-brand-primary transition shadow-sm font-bold text-slate-600" placeholder="0.00" value="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100 my-2">

                <!-- Margem e Lucro -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 p-6 rounded-xl">
                    <label class="block text-sm font-black text-indigo-900 mb-3 flex items-center gap-2">
                        <span>💰</span> Mark-up: Lucro e Precificação (%)
                    </label>
                    <div class="flex items-center gap-4">
                        <input type="range" id="markup-slide" min="0" max="300" step="1" value="150" class="w-full h-2 bg-indigo-200 rounded-lg appearance-none cursor-pointer accent-indigo-600" oninput="document.getElementById('markup-input').value = this.value; calcular();">
                        <div class="relative w-24 shrink-0">
                            <input type="number" id="markup-input" step="1" min="0" value="150" class="w-full rounded-lg border-indigo-200 font-black text-indigo-700 text-center focus:ring-indigo-500 h-10" oninput="document.getElementById('markup-slide').value = this.value; calcular();">
                            <span class="absolute right-3 top-2.5 font-bold text-indigo-300">%</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ÁREA DIREITA: SCORECARD E ACTIONS -->
        <div class="lg:col-span-4 flex flex-col gap-5">
            
            <div class="bg-slate-900 rounded-2xl shadow-2xl p-6 text-white relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-emerald-500 opacity-20 blur-3xl rounded-full"></div>
                
                <h3 class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mb-4">Resumo do Preço de Venda</h3>
                
                <div class="flex flex-col gap-4">
                    <!-- Custo -->
                    <div class="flex justify-between items-end border-b border-slate-800 pb-3">
                        <span class="text-slate-400 text-sm font-semibold">Custo Base (Total)</span>
                        <div class="text-right">
                            <span class="text-slate-500 text-xs mr-1">R$</span>
                            <span id="label-custo" class="text-xl font-bold text-slate-300">0,00</span>
                        </div>
                    </div>

                    <!-- Lucro -->
                    <div class="flex justify-between items-end border-b border-slate-800 pb-3">
                        <span class="text-emerald-400/80 text-sm font-semibold flex items-center gap-1">Lucro Estimado</span>
                        <div class="text-right">
                            <span class="text-emerald-500/50 text-xs mr-1">R$</span>
                            <span id="label-lucro" class="text-xl font-bold text-emerald-400">0,00</span>
                        </div>
                    </div>

                    <!-- Venda -->
                    <div class="flex justify-between items-end pt-2">
                        <span class="text-white text-base font-black">Cobrar (Sugerido)</span>
                        <div class="text-right">
                            <span class="text-brand-primary text-sm mr-1 block sm:inline-block">R$</span>
                            <span id="label-venda" class="text-4xl font-black text-white">0,00</span>
                        </div>
                    </div>
                </div>

                <!-- Input oculto para carregar JSON e valor final pro Backend -->
                <input type="hidden" id="payload-valor-venda" value="0.00">
                <input type="hidden" id="payload-memoria" value="">
                <input type="hidden" id="payload-qtd" value="1">
            </div>

            <!-- Botões de Ação para o Servidor -->
            <div class="flex flex-col gap-3">
                <button type="button" onclick="abrirModalProduto()" class="w-full bg-white rounded-xl border border-slate-200 p-4 text-center hover:bg-slate-50 hover:border-brand-primary/50 transition-all shadow cursor-pointer group flex items-center justify-center gap-2">
                    <span class="text-xl group-hover:scale-110 transition-transform">🏷️</span> 
                    <span class="font-bold text-slate-700">Salvar como Produto Padrão</span>
                </button>

                <button type="button" onclick="abrirModalOrcamento()" class="w-full bg-brand-primary rounded-xl p-4 text-center hover:bg-orange-600 transition-all shadow-lg hover:shadow-orange-500/30 cursor-pointer group flex items-center justify-center gap-2">
                    <span class="text-xl text-white group-hover:rotate-12 transition-transform">➕</span> 
                    <span class="font-black text-white tracking-wide">Injetar no Orçamento/Painel</span>
                </button>
            </div>

        </div>
    </div>

    {{-- MODAL: SALVAR PRODUTO --}}
    <div id="modal-produto" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <form method="POST" action="{{ route('admin.catalog.pricing.save_product') }}" class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 animate-bounce-in">
            @csrf
            <!-- Payload injetado pelo js -->
            <input type="hidden" name="preco_venda" id="form-prod-preco" value="0">
            <input type="hidden" name="descricao" id="form-prod-desc" value="">

            <h2 class="text-xl font-black text-slate-800 mb-1">Cadastrar Sub-Produto</h2>
            <p class="text-xs text-slate-500 mb-5 border-b border-slate-100 pb-3">O preço sugerido será injetado automaticamente como preço base.</p>
            
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nome do Produto</label>
                    <input type="text" name="nome" required class="w-full rounded border-slate-200 h-10 focus:ring-brand-primary" placeholder="Ex: Lona Fosca 440g (Refinada)">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Categoria Pai</label>
                    <select name="categoria" class="w-full rounded border-slate-200 h-10 focus:ring-brand-primary">
                        <option value="lonas">Lonas</option>
                        <option value="adesivos">Adesivos</option>
                        <option value="grafica_rapida">Gráfica Rápida</option>
                        <option value="diversos">Diversos / Kits</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="fecharModais()" class="flex-1 py-2 text-sm text-slate-500 font-bold hover:bg-slate-50 rounded-lg">Cancelar</button>
                <button type="submit" class="flex-1 py-2 text-sm text-white font-bold bg-brand-primary hover:bg-orange-600 rounded-lg shadow">Confirmar Cadastro</button>
            </div>
        </form>
    </div>

    {{-- MODAL: SALVAR ORÇAMENTO (LINKAR AO PEDIDO) --}}
    <div id="modal-orcamento" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <form method="POST" action="{{ route('admin.catalog.pricing.save_order') }}" class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 animate-bounce-in">
            @csrf
            
            <!-- Payload injetado pelo js -->
            <input type="hidden" name="preco_venda_unitario" id="form-orc-preco" value="0">
            <input type="hidden" name="quantidade" id="form-orc-qtd" value="1">
            <input type="hidden" name="memoria_calculo" id="form-orc-memoria" value="">

            <h2 class="text-xl font-black text-slate-800 mb-1">Injetar na Esteira</h2>
            <p class="text-xs text-slate-500 mb-5 border-b border-slate-100 pb-3">Anexe esse cálculo matemático em um protocolo de atendimento como Item de Balcão Customizado.</p>
            
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Selecione o Protocolo (Rascunhos)</label>
                    @if($pedidosAbertos->isEmpty())
                        <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm font-semibold border border-red-100">
                            Não existem Orçamentos ou Rascunhos abertos no CRM. Precisamos de um para vincular!
                        </div>
                    @else
                        <select name="pedido_id" required class="w-full rounded border-slate-200 h-10 focus:ring-brand-primary text-sm font-bold text-slate-700">
                            @foreach($pedidosAbertos as $pedido)
                                <option value="{{ $pedido->id }}">{{ $pedido->numero }} - {{ $pedido->cliente->nome }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="fecharModais()" class="flex-1 py-2 text-sm text-slate-500 font-bold hover:bg-slate-50 rounded-lg transition">Desistir</button>
                <button type="submit" @if($pedidosAbertos->isEmpty()) disabled @endif class="flex-1 py-2 text-sm text-white font-bold bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition disabled:opacity-50 disabled:cursor-not-allowed">Anexar Sub-Item</button>
            </div>
        </form>
    </div>

    <script>
        let modoAtual = 'dinamico';

        function switchMode(mode) {
            modoAtual = mode;
            
            const tabDin = document.getElementById('tab-dinamico');
            const tabFix = document.getElementById('tab-fixo');
            const divDin = document.getElementById('secao-dinamico');
            const divFix = document.getElementById('secao-fixo');

            if(mode === 'dinamico') {
                tabDin.className = "px-6 py-2.5 rounded-full font-bold text-sm bg-brand-primary text-white shadow shadow-orange-200 transition-all";
                tabFix.className = "px-6 py-2.5 rounded-full font-bold text-sm text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-all";
                divDin.classList.remove('hidden');
                divFix.classList.add('hidden');
            } else {
                tabFix.className = "px-6 py-2.5 rounded-full font-bold text-sm bg-slate-800 text-white shadow shadow-slate-200 transition-all";
                tabDin.className = "px-6 py-2.5 rounded-full font-bold text-sm text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-all";
                divFix.classList.remove('hidden');
                divDin.classList.add('hidden');
            }

            calcular();
        }

        function formatBRL(valor) {
            return parseFloat(valor).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function calcular() {
            let custoTotalBruto = 0;
            let precoUnitario = 0;
            let precoVendaGlobal = 0;
            let memoriaText = '';
            
            const markup = parseFloat(document.getElementById('markup-input').value) || 0;
            let qtd = 1;

            if (modoAtual === 'fixo') {
                const custoFixo = parseFloat(document.getElementById('custo-fixo').value) || 0;
                custoTotalBruto = custoFixo;
                
                // Em "Fixo", trataremos qtd = 1 globalmente (pois é bloco de cartoes)
                memoriaText = \`Precificação de Fixo: Custo R$ \${custoTotalBruto.toFixed(2)}\`;
            } else {
                const L = parseFloat(document.getElementById('dim-largura').value) || 0;
                const A = parseFloat(document.getElementById('dim-altura').value) || 0;
                qtd = parseInt(document.getElementById('dim-qtd').value) || 1;
                const C = parseFloat(document.getElementById('custo-m2').value) || 0;
                
                const perda = parseFloat(document.getElementById('taxa-perda').value) || 0;
                const acabamento = parseFloat(document.getElementById('taxa-acabamento').value) || 0;

                // Custos base: (Area Total * Custo do M2)
                const areaTotal = L * A * qtd;
                const custoBase = areaTotal * C;
                const adicionalPerda = custoBase * (perda/100);
                
                // Custo Bruto contempla Perdas e Acabamentos R$ Adicionais
                custoTotalBruto = custoBase + adicionalPerda + acabamento;

                memoriaText = \`Calculadora: \${L}m x \${A}m (Qtd: \${qtd}). AreaTotal: \${areaTotal.toFixed(2)}m2. Base M2: R$\${C}. Adicionais e Setup da Impressora: R$\${(adicionalPerda + acabamento).toFixed(2)}.\`;
            }

            // Matemática Comercial Final
            const lucroEstimado = (custoTotalBruto * (markup / 100));
            precoVendaGlobal = custoTotalBruto + lucroEstimado;

            // Se for dinâmico e tem quantidade, achamos o Unitário que vai para o Banco
            precoUnitario = precoVendaGlobal / qtd;

            // Aplica no DOM
            document.getElementById('label-custo').textContent = formatBRL(custoTotalBruto);
            document.getElementById('label-lucro').textContent = formatBRL(lucroEstimado);
            document.getElementById('label-venda').textContent = formatBRL(precoVendaGlobal);

            // Exportação Interna pro Backend preenchendo Type Hiddens
            document.getElementById('payload-valor-venda').value = precoVendaGlobal.toFixed(2);
            document.getElementById('payload-qtd').value = qtd;
            document.getElementById('form-orc-preco').value = precoUnitario.toFixed(2); // Pra enviar o uni!
            
            document.getElementById('payload-memoria').value = memoriaText;
        }

        // Funções de Modal
        function abrirModalProduto() {
            document.getElementById('form-prod-preco').value = document.getElementById('payload-valor-venda').value;
            document.getElementById('form-prod-desc').value = document.getElementById('payload-memoria').value;
            
            const mod = document.getElementById('modal-produto');
            mod.classList.remove('hidden');
            mod.classList.add('flex');
        }

        function abrirModalOrcamento() {
            document.getElementById('form-orc-qtd').value = document.getElementById('payload-qtd').value;
            document.getElementById('form-orc-memoria').value = document.getElementById('payload-memoria').value;
            
            const mod = document.getElementById('modal-orcamento');
            mod.classList.remove('hidden');
            mod.classList.add('flex');
        }

        function fecharModais() {
            document.getElementById('modal-produto').classList.add('hidden');
            document.getElementById('modal-produto').classList.remove('flex');
            document.getElementById('modal-orcamento').classList.add('hidden');
            document.getElementById('modal-orcamento').classList.remove('flex');
        }

        // Start Initial Call
        document.addEventListener('DOMContentLoaded', calcular);
    </script>
</x-layouts.app>
