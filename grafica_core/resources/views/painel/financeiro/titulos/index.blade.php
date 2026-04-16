{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 18:50
Descrição: Listagem de Títulos Financeiros (A Pagar / A Receber).
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Contas a {{ ucfirst($tipo) }}</h1>
            <p class="text-slate-500 font-medium">Gerencie e liquide seus compromissos financeiros.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button onclick="openModal('modal-novo-titulo')" class="rounded-xl bg-gradient-to-r from-brand-primary to-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:scale-105 flex items-center gap-2">
                <span>➕</span> Novo Título
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm border border-slate-100 flex flex-wrap items-center gap-4">
        <form class="flex flex-wrap items-center gap-4 w-full">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase">Status:</label>
                <select name="status" class="rounded-lg border-slate-200 text-sm font-bold text-slate-700 bg-slate-50 focus:ring-brand-primary">
                    <option value="">Todos</option>
                    <option value="aberto" {{ request('status') == 'aberto' ? 'selected' : '' }}>Aberto</option>
                    <option value="parcial" {{ request('status') == 'parcial' ? 'selected' : '' }}>Parcial</option>
                    <option value="vencido" {{ request('status') == 'vencido' ? 'selected' : '' }}>Vencido</option>
                    <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200 transition">Filtrar</button>
            <a href="{{ request()->url() }}" class="text-xs font-bold text-slate-400 hover:text-brand-primary">Limpar</a>
        </form>
    </div>

    <!-- Lista de Títulos -->
    <div class="rounded-2xl bg-white border border-slate-100 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Vencimento</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Descrição</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Categoria</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase text-right">Valor Total</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase text-right">Saldo Restante</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($titulos as $titulo)
                        <tr class="hover:bg-slate-50 transition {{ $titulo->status === 'vencido' ? 'bg-red-50/20' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black {{ $titulo->status === 'vencido' ? 'text-red-600' : 'text-slate-800' }}">
                                        {{ $titulo->data_vencimento->format('d/m/Y') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $titulo->data_vencimento->diffForHumans() }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-slate-800">{{ $titulo->descricao }}</p>
                                @if($titulo->pedido)
                                    <a href="{{ route('admin.sales.pedidos.show', $titulo->referencia_id) }}" class="text-[10px] font-black text-brand-primary hover:underline uppercase">Visualizar Pedido #{{ $titulo->pedido->numero }}</a>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded">{{ $titulo->categoria?->nome ?? 'S/ Categoria' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-800 text-right">R$ {{ number_format($titulo->valor_total, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm font-black text-right {{ $titulo->saldo_restante > 0 ? 'text-orange-600' : 'text-emerald-600' }}">
                                R$ {{ number_format($titulo->saldo_restante, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase ring-1 
                                    @if($titulo->status === 'pago') bg-emerald-50 text-emerald-600 ring-emerald-200 
                                    @elseif($titulo->status === 'vencido') bg-red-50 text-red-600 ring-red-200
                                    @elseif($titulo->status === 'parcial') bg-orange-50 text-orange-600 ring-orange-200
                                    @else bg-slate-50 text-slate-600 ring-slate-200 @endif">
                                    {{ $titulo->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($titulo->status !== 'pago')
                                    <button onclick="openPaymentModal({{ $titulo->id }}, {{ $titulo->saldo_restante }}, '{{ $titulo->descricao }}')" class="rounded-lg bg-brand-primary px-3 py-1.5 text-[10px] font-black text-white shadow-sm hover:scale-105 transition uppercase leading-none">
                                        Baixar
                                    </button>
                                @else
                                    <span class="text-emerald-500">✅</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center">
                                <span class="text-6xl block mb-4 opacity-20">📂</span>
                                <p class="text-lg font-black text-slate-600">Nenhum título localizado.</p>
                                <p class="text-sm text-slate-400">Experimente mudar os filtros ou criar um novo lançamento.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($titulos->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $titulos->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Novo Título -->
    <div id="modal-novo-titulo" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="bg-slate-800 px-6 py-4 flex items-center justify-between">
                <h3 class="font-black text-white uppercase tracking-widest text-sm">Novo Título a {{ ucfirst($tipo) }}</h3>
                <button onclick="closeModal('modal-novo-titulo')" class="text-white opacity-50 hover:opacity-100 transition">✕</button>
            </div>
            <form action="{{ route('admin.finance.titles.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="tipo" value="{{ $tipo }}">
                
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-1">Descrição do Lançamento</label>
                    <input type="text" name="descricao" required placeholder="Ex: Aluguel, Compra de Papel, etc." class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase mb-1">Valor Total</label>
                        <input type="number" step="0.01" name="valor_total" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase mb-1">Vencimento</label>
                        <input type="date" name="data_vencimento" required value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-1">Categoria Financeira</label>
                    <select name="categoria_id" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                        <option value="">Sem Categoria</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full rounded-xl bg-slate-800 py-4 text-center font-black text-white shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-sm mt-4">
                    Salvar Título
                </button>
            </form>
        </div>
    </div>

    <!-- Modal de Baixa (Pagamento) -->
    <div id="modal-pagamento" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="bg-emerald-600 px-6 py-4 flex items-center justify-between">
                <h3 class="font-black text-white uppercase tracking-widest text-sm">Registrar Baixa / Pagamento</h3>
                <button onclick="closeModal('modal-pagamento')" class="text-white opacity-50 hover:opacity-100 transition">✕</button>
            </div>
            <form id="form-pagamento" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 mb-4">
                    <p id="label-titulo-pagamento" class="text-xs font-black text-slate-800 uppercase mb-1"></p>
                    <p id="label-valor-pendente" class="text-sm font-medium text-slate-500"></p>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-1">Valor a Ser Pago</label>
                    <input type="number" step="0.01" name="valor" id="input-valor-pagar" required class="w-full rounded-xl border-slate-200 bg-slate-50 text-xl font-black text-emerald-600 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase mb-1">Data</label>
                        <input type="date" name="data_pagamento" required value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase mb-1">Meio</label>
                        <select name="forma_pagamento" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-emerald-500">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="PIX">PIX</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Cartão de Débito">Cartão de Débito</option>
                            <option value="Boleto">Boleto</option>
                            <option value="Transferência">Transferência</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-1">Conta de Destino</label>
                    <select name="account_id" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-emerald-500">
                        <option value="">Selecione a Conta</option>
                        @foreach($contas as $cta)
                            <option value="{{ $cta->id }}">{{ $cta->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full rounded-xl bg-emerald-600 py-4 text-center font-black text-white shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-sm mt-4">
                    Confirmar Recebimento 💸
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
        function openPaymentModal(titleId, saldo, descricao) {
            const form = document.getElementById('form-pagamento');
            form.action = `/painel/gestao-financeira/titulos/${titleId}/pagar`;
            document.getElementById('label-titulo-pagamento').innerText = descricao;
            document.getElementById('label-valor-pendente').innerText = 'Saldo Pendente: R$ ' + saldo.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            document.getElementById('input-valor-pagar').value = saldo;
            openModal('modal-pagamento');
        }
    </script>
</x-layouts.app>
