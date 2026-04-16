{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-06 00:00 -03:00
--}}
<x-layouts.app>
    <div class="mb-6 flex flex-col sm:flex-row items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sales.pedidos.index') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm border border-slate-200 text-slate-500 hover:text-brand-primary hover:bg-brand-primary/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h1 class="text-3xl font-black text-brand-secondary">Novo Orçamento Balcão</h1>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <span class="inline-flex rounded-full bg-orange-100 px-3 py-1 font-bold text-orange-600 border border-orange-200">
                1. Passo-a-passo
            </span>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-2xl bg-red-50 p-4 border border-red-200">
            <div class="font-bold text-red-600 mb-2">Atenção! Verifique os erros:</div>
            <ul class="list-disc pl-5 text-sm text-red-500">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.sales.pedidos.store') }}" id="formPedidoBalcao" class="grid lg:grid-cols-12 gap-6">
        @csrf

        <!-- Coluna Esquerda: Fluxo de Criação -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            
            <!-- Etapa 1: Cliente -->
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-primary text-xs font-black text-white">1</span>
                    Vincular Cliente
                </h2>
                <div class="grid gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-600">Busque o Cadastro Existente <span class="text-status-error">*</span></label>
                        <select name="cliente_id" id="cliente_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 shadow-inner focus:border-brand-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-primary" required>
                            <option value="">Selecione ou digite para procurar...</option>
                            @foreach($clientes as $cli)
                                <option value="{{ $cli->id }}" {{ old('cliente_id') == $cli->id ? 'selected' : '' }}>{{ $cli->nome }} {{ $cli->cpf_cnpj ? '('.$cli->cpf_cnpj.')' : '' }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('admin.sales.clientes.index') }}" target="_blank" class="mt-2 inline-block text-xs font-bold text-brand-primary hover:underline">Ou cadastre um novo cliente rápido numa nova aba</a>
                    </div>
                </div>
            </div>

            <!-- Etapa 2: Montagem do Carrinho -->
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-primary text-xs font-black text-white">2</span>
                    Montagem de Lotes / Itens
                </h2>
                
                <div class="rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-4 mb-4">
                    <div class="grid md:grid-cols-4 gap-3 items-end">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-bold text-slate-500 uppercase">Qual o Produto?</label>
                            <select id="draft_produto" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                                <option value="" data-base="0">Escolha...</option>
                                @foreach($produtos as $prod)
                                    <option value="{{ $prod->id }}" data-nome="{{ $prod->nome }}" data-base="{{ $prod->preco_base ?? 0 }}">{{ $prod->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500 uppercase">Quantia.</label>
                            <input type="number" id="draft_qtd" value="1" min="1" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary text-center">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500 uppercase">R$ Unitário</label>
                            <input type="number" id="draft_preco" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary text-right font-mono">
                        </div>
                    </div>
                    <div class="mt-3 grid md:grid-cols-4 gap-3 items-end">
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-bold text-slate-500 uppercase">Variações e Acabamentos da Arte</label>
                            <input type="text" id="draft_espec" placeholder="Ex: Verniz Localizado, Laminação Fosca" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                        </div>
                        <div class="md:col-span-1">
                            <button type="button" onclick="adicionarItem()" class="w-full rounded-lg bg-slate-800 px-4 py-2 font-bold text-white transition-opacity hover:bg-slate-700 text-sm">
                                + Inserir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Itens em Memória -->
                <div class="overflow-x-auto rounded-xl border border-slate-200 mb-2">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-100 text-left">
                            <tr>
                                <th class="px-4 py-2 font-bold text-slate-600 w-[45%]">Item Descrição</th>
                                <th class="px-4 py-2 font-bold text-slate-600 text-center w-[15%]">Qtd</th>
                                <th class="px-4 py-2 font-bold text-slate-600 text-right w-[15%]">R$ Unid.</th>
                                <th class="px-4 py-2 font-bold text-slate-600 text-right w-[15%]">Subtotal</th>
                                <th class="px-4 py-2 font-bold text-slate-600 text-center w-[10%]"></th>
                            </tr>
                        </thead>
                        <tbody id="cartBody" class="divide-y divide-slate-100">
                            <!-- JS injeta os itens aqui -->
                            <tr id="cartEmptyRow">
                                <td colspan="5" class="px-4 py-6 text-center text-slate-400 font-medium italic">Seu carrinho técnico está vazio. Adicione itens acima.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- DIV SECRETA DO FORM PARA REGISTRAR O ARRAY AO ENVIAR O POST -->
                <div id="hiddenFieldsContainer"></div>
            </div>

            <!-- Etapa 3: Entrega e Detalhes Base -->
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-primary text-xs font-black text-white">3</span>
                    Prazo e Entrega
                </h2>
                
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-600">Método de Entrega / Retirada <span class="text-status-error">*</span></label>
                        <select name="tipo_entrega" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-inner focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                            <option value="retirada">Retirar no Balcão</option>
                            <option value="entrega_local">Motoboy (Entrega Rápida)</option>
                            <option value="entrega_agendada">Logística/Correios/Transportadora</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-600">Previsão Acordada</label>
                        <input type="date" name="prazo_entrega" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-inner text-slate-600 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                    </div>

                    <!-- Cupom de Desconto -->
                    <div class="md:col-span-2 bg-slate-50 border border-slate-200 rounded-2xl p-4">
                        <label class="mb-1 block text-xs font-black uppercase text-slate-400 tracking-widest">Cupom Promocional (Opcional)</label>
                        <div class="flex gap-2">
                            <input type="text" name="cupom_codigo" id="calc_cupom_cod" placeholder="Ex: BEMVINDO10" class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-brand-secondary focus:border-brand-primary focus:outline-none uppercase">
                            <button type="button" onclick="aplicarCupomFake()" class="bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-slate-700">Validar</button>
                        </div>
                        <div id="cupomMsg" class="mt-1 text-[10px] font-bold hidden"></div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-bold text-slate-600">Observações Operacionais / Lembretes</label>
                        <textarea name="observacoes" rows="2" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-inner text-slate-600 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary" placeholder="Informação adicional de arte, prioridade de impressão..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Calculadora e Submissão -->
        <div class="lg:col-span-4">
            <div class="sticky top-6 rounded-3xl border-2 border-brand-secondary bg-slate-50 overflow-hidden shadow-xl">
                <div class="bg-brand-secondary text-white py-4 px-6 text-center shadow-inner">
                    <h2 class="text-sm tracking-widest uppercase font-black">Cálculo e Checkout</h2>
                </div>
                
                <div class="p-6 flex flex-col gap-5">
                    <!-- Display Base -->
                    <div class="flex justify-between items-center text-slate-500 font-medium">
                        <span>Valor dos Produtos</span>
                        <span id="displaySubtotal">R$ 0,00</span>
                    </div>

                    <!-- Manipuladores Financeiros -->
                    <div>
                        <label class="mb-1 flex justify-between text-sm font-bold text-slate-700">
                            (+ Frete Correios)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-bold text-slate-400">R$</span>
                            <input type="number" name="valor_frete" id="calc_frete" value="0.00" step="0.01" class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-right font-mono focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary" oninput="recalcular()">
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <button type="button" onclick="setFrete(0)" class="text-[10px] font-bold bg-slate-200 text-slate-600 px-2 py-1 rounded hover:bg-slate-300">Retirada</button>
                            <button type="button" onclick="setFrete(15)" class="text-[10px] font-bold bg-slate-200 text-slate-600 px-2 py-1 rounded hover:bg-slate-300">Motoboy R$15</button>
                            <button type="button" onclick="setFrete(30)" class="text-[10px] font-bold bg-slate-200 text-slate-600 px-2 py-1 rounded hover:bg-slate-300">Expresso R$30</button>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 flex justify-between text-sm font-bold text-slate-700">
                            (+ Taxas Extras Motoboy/Emb.)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-bold text-slate-400">R$</span>
                            <input type="number" name="taxas_adicionais" id="calc_taxas" value="0.00" step="0.01" class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-right font-mono focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary border-orange-200 focus:border-orange-500 focus:ring-orange-500" oninput="recalcular()">
                        </div>
                    </div>

                    <!-- Desconto de Cupom (Visual) -->
                    <div id="rowCupom" class="hidden justify-between items-center text-emerald-600 text-xs font-bold border-b border-slate-100 pb-2">
                        <span>Desconto de Cupom</span>
                        <span id="displayDescCupom">- R$ 0,00</span>
                    </div>

                    <div class="border-t border-slate-200 pt-3">
                        <label class="mb-1 flex justify-between text-sm font-bold text-emerald-600">
                            (- Descontos Autorizados)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-bold text-emerald-500">R$</span>
                            <input type="number" name="desconto" id="calc_desc" value="0.00" step="0.01" class="w-full rounded-lg border border-emerald-200 bg-emerald-50 py-2 pl-9 pr-3 text-right font-mono focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 text-emerald-700" oninput="recalcular()">
                        </div>
                    </div>

                    <!-- Lousa Mágica do Total Final -->
                    <div class="mt-2 rounded-2xl bg-white p-4 shadow-sm border border-slate-200">
                        <div class="text-xs font-black uppercase tracking-widest text-brand-secondary">Valor Líquido Cliente</div>
                        <div class="text-4xl font-black text-brand-primary mt-1" id="displayTotalLiq">R$ 0,00</div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <button type="submit" id="btnSalvarGeral" class="w-full rounded-2xl bg-brand-primary py-4 text-center text-lg font-black text-white shadow hover:scale-105 hover:bg-orange-600 transition-all">
                            Formalizar Orçamento
                        </button>
                        <p class="text-center text-xs text-slate-400 mt-3">*Todos os pedidos salvos no balcão iniciarão como EM ORÇAMENTO.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        let carrinhoItens = [];

        // Auto-preenche preço ao trocar o Select de produto base
        document.getElementById('draft_produto').addEventListener('change', function() {
            let opt = this.options[this.selectedIndex];
            document.getElementById('draft_preco').value = parseFloat(opt.getAttribute('data-base')).toFixed(2);
        });

        function formatBRL(valor) {
            return parseFloat(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        function adicionarItem() {
            const prodSelect = document.getElementById('draft_produto');
            if(!prodSelect.value) return alert('Selecione um produto.');
            
            const prodId = prodSelect.value;
            const nomeStr = prodSelect.options[prodSelect.selectedIndex].getAttribute('data-nome');
            const qty = parseInt(document.getElementById('draft_qtd').value) || 1;
            const price = parseFloat(document.getElementById('draft_preco').value) || 0.00;
            const espec = document.getElementById('draft_espec').value.trim();

            carrinhoItens.push({
                id_unico_tela: Date.now(),
                produto_id: prodId,
                nome: nomeStr,
                quantidade: qty,
                valor_unitario: price,
                especificacoes: espec,
                subtotal: qty * price
            });

            // reset drafts
            prodSelect.value = '';
            document.getElementById('draft_qtd').value = '1';
            document.getElementById('draft_preco').value = '';
            document.getElementById('draft_espec').value = '';

            renderCarrinho();
        }

        function removerItem(uid) {
            carrinhoItens = carrinhoItens.filter(i => i.id_unico_tela !== uid);
            renderCarrinho();
        }

        function renderCarrinho() {
            const tbody = document.getElementById('cartBody');
            const hContainer = document.getElementById('hiddenFieldsContainer');
            tbody.innerHTML = '';
            hContainer.innerHTML = '';

            if(carrinhoItens.length === 0) {
                tbody.innerHTML = `<tr id="cartEmptyRow"><td colspan="5" class="px-4 py-6 text-center text-slate-400 font-medium italic">Seu carrinho técnico está vazio. Adicione itens acima.</td></tr>`;
            } else {
                carrinhoItens.forEach((item, index) => {
                    // Preenche visual Tabela
                    let tr = document.createElement('tr');
                    tr.className = 'border-t border-slate-100 hover:bg-slate-50 transition-colors';
                    tr.innerHTML = `
                        <td class="px-4 py-3">
                            <div class="font-bold text-brand-secondary">${item.nome}</div>
                            <div class="text-xs text-slate-500">${item.especificacoes}</div>
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-slate-700">${item.quantidade} <span class="text-xs text-slate-400">UN</span></td>
                        <td class="px-4 py-3 text-right font-mono text-slate-600">${formatBRL(item.valor_unitario)}</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-brand-primary">${formatBRL(item.subtotal)}</td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" onclick="removerItem(${item.id_unico_tela})" class="text-red-500 hover:text-red-700 p-1 bg-red-50 rounded" title="Remover Vida">🗑️</button>
                        </td>
                    `;
                    tbody.appendChild(tr);

                    // Preenche inputs invisiveis para POST Laravel Array!
                    hContainer.innerHTML += `
                        <input type="hidden" name="itens[${index}][produto_id]" value="${item.produto_id}">
                        <input type="hidden" name="itens[${index}][quantidade]" value="${item.quantidade}">
                        <input type="hidden" name="itens[${index}][valor_unitario]" value="${item.valor_unitario}">
                        <input type="hidden" name="itens[${index}][especificacoes]" value="${item.especificacoes}">
                    `;
                });
            }

            recalcular();
        }

        function setFrete(valor) {
            document.getElementById('calc_frete').value = valor.toFixed(2);
            recalcular();
        }

        let descontoCupomAtivo = 0;

        function aplicarCupomFake() {
            const cod = document.getElementById('calc_cupom_cod').value.trim();
            const msg = document.getElementById('cupomMsg');
            
            if(!cod) {
                descontoCupomAtivo = 0;
                msg.classList.add('hidden');
                recalcular();
                return;
            }

            // Como a validação real é no PHP no Store, aqui fazemos um "check" visual simulado ou apenas avisamos que será validado no fechamento.
            // Para ser premium, o ideal seria um mini API. Mas seguindo a regra de simplicidade robusta:
            msg.innerText = "⏳ Cupom será validado ao finalizar o pedido.";
            msg.className = "mt-1 text-[10px] font-bold text-blue-500";
            msg.classList.remove('hidden');
        }

        function recalcular() {
            let somaItens = 0;
            carrinhoItens.forEach(i => somaItens += i.subtotal);
            
            document.getElementById('displaySubtotal').innerText = formatBRL(somaItens);

            let frete = parseFloat(document.getElementById('calc_frete').value) || 0;
            let taxas = parseFloat(document.getElementById('calc_taxas').value) || 0;
            let descontoManual = parseFloat(document.getElementById('calc_desc').value) || 0;

            // O total líquido real será calculado no servidor.
            // Aqui fazemos a estimativa visual corrigida.
            let liq = (somaItens + frete + taxas) - descontoManual;
            
            if(liq < 0) liq = 0;

            document.getElementById('displayTotalLiq').innerText = formatBRL(liq);
        }

        // Bloqueio do Form se carrinho Vazio
        document.getElementById('formPedidoBalcao').addEventListener('submit', function(e) {
            if(carrinhoItens.length === 0) {
                e.preventDefault();
                alert('Atenção Operador:\nNão é possível faturar um pedido oco. Insira ao menos o Produto Principal nas Etapas Base.');
            }
        });
    </script>
</x-layouts.app>
