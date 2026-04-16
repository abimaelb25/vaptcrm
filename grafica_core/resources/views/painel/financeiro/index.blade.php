{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-10
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-4xl font-black tracking-tight text-brand-secondary">Gestão Financeira</h1>
            <p class="text-slate-500 font-medium">Controle de fluxo de caixa e saúde financeira da operação</p>
        </div>
        
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.finance.index') }}" method="GET" class="flex items-center gap-2 bg-white p-1.5 rounded-2xl border border-slate-200 shadow-sm">
                <input type="date" name="inicio" value="{{ $inicio }}" class="bg-transparent border-none text-sm font-bold text-slate-600 focus:ring-0">
                <span class="text-slate-300">até</span>
                <input type="date" name="fim" value="{{ $fim }}" class="bg-transparent border-none text-sm font-bold text-slate-600 focus:ring-0">
                <button type="submit" class="bg-slate-100 p-2 rounded-xl hover:bg-slate-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </button>
            </form>

            <button onclick="document.getElementById('modalLancamento').showModal()" class="group relative overflow-hidden rounded-2xl bg-brand-primary px-6 py-3 text-sm font-black text-white shadow-lg transition-all hover:scale-105">
                <span class="relative z-10 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Novo Lançamento
                </span>
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
            </button>
        </div>
    </div>

    <!-- Indicadores Principais -->
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Saldo Geral -->
        <div class="group relative overflow-hidden rounded-3xl bg-white p-8 shadow-xl border border-slate-100">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-slate-50 transition-transform duration-700 group-hover:scale-150"></div>
            <p class="text-xs font-black uppercase tracking-widest text-slate-400">Saldo Geral Disponível</p>
            <p class="mt-4 text-4xl font-black {{ $saldoAtual >= 0 ? 'text-brand-secondary' : 'text-status-error' }}">
                R$ {{ number_format($saldoAtual, 2, ',', '.') }}
            </p>
            <div class="mt-4 flex items-center gap-2 text-xs font-bold {{ $saldoAtual >= 0 ? 'text-status-success' : 'text-status-error' }}">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-current/10">
                    {!! $saldoAtual >= 0 ? '↑' : '↓' !!}
                </span>
                Saldo Real em Caixa
            </div>
        </div>

        <!-- Entradas -->
        <div class="group relative overflow-hidden rounded-3xl bg-emerald-500 p-8 shadow-[0_20px_40px_rgba(16,185,129,0.3)] text-white">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-white/20 transition-transform duration-700 group-hover:scale-150"></div>
            <p class="text-xs font-black uppercase tracking-widest text-emerald-100">Entradas (Período)</p>
            <p class="mt-4 text-4xl font-black drop-shadow-md">
                R$ {{ number_format($entradasMes, 2, ',', '.') }}
            </p>
            <p class="mt-4 text-xs font-bold opacity-80 uppercase tracking-widest">Receita Bruta Confirmada</p>
        </div>

        <!-- Saídas -->
        <div class="group relative overflow-hidden rounded-3xl bg-slate-800 p-8 shadow-xl text-white">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-white/5 transition-transform duration-700 group-hover:scale-150"></div>
            <p class="text-xs font-black uppercase tracking-widest text-slate-400">Saídas (Período)</p>
            <p class="mt-4 text-4xl font-black text-rose-400">
                R$ {{ number_format($saidasMes, 2, ',', '.') }}
            </p>
            <p class="mt-4 text-xs font-bold opacity-60 uppercase tracking-widest">Custos e Despesas Pagas</p>
        </div>

        <!-- Pendentes -->
        <div class="group relative overflow-hidden rounded-3xl bg-white p-8 shadow-xl border border-slate-100">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-amber-50 transition-transform duration-700 group-hover:scale-150"></div>
            <p class="text-xs font-black uppercase tracking-widest text-amber-500">Saldo Pendente</p>
            <p class="mt-4 text-4xl font-black text-slate-700">
                R$ {{ number_format($contasPendentes, 2, ',', '.') }}
            </p>
            <p class="mt-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Aguardando Liquidação</p>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-amber-400"></div>
        </div>
    </div>

    <!-- Tabela de Movimentações Recentes -->
    <div class="mt-12 rounded-3xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between p-8 border-b border-slate-50">
            <h3 class="text-xl font-black text-brand-secondary uppercase tracking-tight">Movimentações Recentes</h3>
            <a href="{{ route('admin.finance.index') }}" class="text-sm font-bold text-brand-primary hover:underline">Ver Extrato Completo →</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-xs font-black uppercase tracking-widest text-slate-400">
                        <th class="px-8 py-4">Data</th>
                        <th class="px-4 py-4">Categoria/Descrição</th>
                        <th class="px-4 py-4">Vínculo</th>
                        <th class="px-4 py-4">Forma</th>
                        <th class="px-4 py-4">Status</th>
                        <th class="px-8 py-4 text-right">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm font-medium">
                    @forelse($recentes as $mov)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-8 py-5 text-slate-500">{{ $mov->data_movimentacao->format('d/m/Y') }}</td>
                            <td class="px-4 py-5">
                                <span class="block font-bold text-slate-800">{{ $mov->categoria }}</span>
                                <span class="text-xs text-slate-400 line-clamp-1 italic">{{ $mov->descricao }}</span>
                            </td>
                            <td class="px-4 py-5 text-xs">
                                @if($mov->pedido)
                                    <span class="rounded-md bg-brand-secondary/10 px-2 py-1 font-black text-brand-secondary">#{{ $mov->pedido->numero }}</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-5 text-slate-500 uppercase font-black text-[10px] tracking-widest">{{ $mov->forma_pagamento }}</td>
                            <td class="px-4 py-5">
                                <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-tighter {{ $mov->status === 'pago' ? 'bg-emerald-100 text-emerald-700' : ($mov->status === 'pendente' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                    {{ $mov->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right font-black text-lg {{ $mov->tipo === 'entrada' ? 'text-emerald-600' : 'text-slate-800' }}">
                                {{ $mov->tipo === 'entrada' ? '+' : '-' }} R$ {{ number_format($mov->valor, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center text-slate-400 font-bold italic">Nenhuma movimentação registrada no período.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Lançamento Manual -->
    <dialog id="modalLancamento" class="rounded-3xl border-none p-0 backdrop:bg-slate-900/60 shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-gradient-to-r from-brand-secondary to-slate-700 p-6 flex items-center justify-between">
            <h3 class="font-black text-white uppercase tracking-widest">Novo Lançamento</h3>
            <button onclick="this.closest('dialog').close()" class="text-white/60 hover:text-white transition-colors">✕</button>
        </div>
        
        <form action="{{ route('admin.finance.store') }}" method="POST" class="p-8 space-y-5">
            @csrf
            <div>
                <label class="block mb-1 text-xs font-black uppercase text-slate-400 tracking-widest">Tipo de Movimentação</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="has-[:checked]:bg-emerald-500 has-[:checked]:text-white flex items-center justify-center p-3 rounded-xl border border-slate-200 cursor-pointer transition-all font-bold text-slate-500 bg-white">
                        <input type="radio" name="tipo" value="entrada" class="hidden" checked>
                        Entrada (+)
                    </label>
                    <label class="has-[:checked]:bg-rose-500 has-[:checked]:text-white flex items-center justify-center p-3 rounded-xl border border-slate-200 cursor-pointer transition-all font-bold text-slate-500 bg-white">
                        <input type="radio" name="tipo" value="saida" class="hidden">
                        Saída (-)
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 text-xs font-black uppercase text-slate-400 tracking-widest">Valor (R$)</label>
                    <input type="number" step="0.01" name="valor" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-primary">
                </div>
                <div>
                    <label class="block mb-1 text-xs font-black uppercase text-slate-400 tracking-widest">Data</label>
                    <input type="date" name="data_movimentacao" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-600">
                </div>
            </div>

            <div>
                <label class="block mb-1 text-xs font-black uppercase text-slate-400 tracking-widest">Categoria</label>
                <input type="text" name="categoria" placeholder="Ex: Aluguel, Papel, Venda Extra..." required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 text-xs font-black uppercase text-slate-400 tracking-widest">Forma Pgto</label>
                    <select name="forma_pagamento" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                        <option value="pix">PIX</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="cartao_credito">Cartão de Crédito</option>
                        <option value="cartao_debito">Cartão de Débito</option>
                        <option value="boleto">Boleto</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-black uppercase text-slate-400 tracking-widest">Status Inicial</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                        <option value="pago">Quitado (Pago)</option>
                        <option value="pendente" selected>Pendente</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block mb-1 text-xs font-black uppercase text-slate-400 tracking-widest">Observação</label>
                <textarea name="descricao" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-600"></textarea>
            </div>

            <button type="submit" class="w-full rounded-2xl bg-brand-secondary py-4 font-black uppercase tracking-widest text-white shadow-lg transition-all hover:bg-slate-900 active:scale-95">
                Confirmar Lançamento
            </button>
        </form>
    </dialog>
</x-layouts.app>

