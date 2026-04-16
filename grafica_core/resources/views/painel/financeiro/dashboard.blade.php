{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 18:45
Descrição: Dashboard Financeiro Profissional (Vapt Finance PRO).
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Vapt Finance <span class="text-brand-primary">PRO</span></h1>
            <p class="text-slate-500 font-medium">Gestão operacional de fluxo, títulos e previsibilidade.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
            <a href="{{ route('admin.finance.receivable') }}" class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-emerald-600 flex items-center gap-2">
                <span>💰</span> Contas a Receber
            </a>
            <a href="{{ route('admin.finance.payable') }}" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-red-600 flex items-center gap-2">
                <span>💸</span> Contas a Pagar
            </a>
        </div>
    </div>

    <!-- Filtro de Período -->
    <div class="mb-8 rounded-2xl bg-white p-4 shadow-sm border border-slate-100 flex flex-wrap items-center gap-4">
        <form class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase">Início:</label>
                <input type="date" name="inicio" value="{{ $inicio }}" class="rounded-lg border-slate-200 text-sm font-bold text-slate-700 bg-slate-50 focus:ring-brand-primary focus:border-brand-primary">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase">Fim:</label>
                <input type="date" name="fim" value="{{ $fim }}" class="rounded-lg border-slate-200 text-sm font-bold text-slate-700 bg-slate-50 focus:ring-brand-primary focus:border-brand-primary">
            </div>
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-bold text-white hover:bg-slate-700 transition">Filtrar</button>
        </form>
    </div>

    <!-- KPIs Principais -->
    <div class="mb-10 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <!-- Saldo em Contas -->
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/50">
            <div class="mb-4 flex items-center justify-between">
                <span class="rounded-xl bg-orange-100 p-3 text-2xl">🏦</span>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Saldo Disponível</span>
            </div>
            <p class="text-3xl font-black text-slate-800">R$ {{ number_format($saldoContas, 2, ',', '.') }}</p>
            <div class="mt-2 flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-bold text-slate-400">Total em todas as contas</span>
            </div>
        </div>

        <!-- Realizado (Entradas) -->
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/30 p-6 shadow-xl shadow-emerald-500/5">
            <div class="mb-4 flex items-center justify-between">
                <span class="rounded-xl bg-emerald-100 p-3 text-2xl">📈</span>
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Entradas (Realizado)</span>
            </div>
            <p class="text-3xl font-black text-emerald-700">R$ {{ number_format($entradas, 2, ',', '.') }}</p>
            <p class="mt-2 text-xs font-bold text-emerald-500/70">No período selecionado</p>
        </div>

        <!-- Realizado (Saídas) -->
        <div class="rounded-2xl border border-red-100 bg-red-50/30 p-6 shadow-xl shadow-red-500/5">
            <div class="mb-4 flex items-center justify-between">
                <span class="rounded-xl bg-red-100 p-3 text-2xl">📉</span>
                <span class="text-[10px] font-black uppercase tracking-widest text-red-600">Saídas (Realizado)</span>
            </div>
            <p class="text-3xl font-black text-red-700">R$ {{ number_format($saidas, 2, ',', '.') }}</p>
            <p class="mt-2 text-xs font-bold text-red-500/70">No período selecionado</p>
        </div>

        <!-- Resultado -->
        @php $resultado = $entradas - $saidas; @endphp
        <div class="rounded-2xl border border-brand-primary/20 bg-gradient-to-br from-brand-secondary to-slate-900 p-6 shadow-xl shadow-brand-primary/10">
            <div class="mb-4 flex items-center justify-between">
                <span class="rounded-xl bg-white/10 p-3 text-2xl">📊</span>
                <span class="text-[10px] font-black uppercase tracking-widest text-white/50">Saldo do Período</span>
            </div>
            <p class="text-3xl font-black text-white">R$ {{ number_format($resultado, 2, ',', '.') }}</p>
            <p class="mt-2 text-xs font-bold {{ $resultado >= 0 ? 'text-emerald-400' : 'text-orange-400' }}">
                {{ $resultado >= 0 ? 'Lucro Operacional' : 'Déficit no Período' }}
            </p>
        </div>
    </div>

    <!-- Previsibilidade (Painel PRO) -->
    <div class="mb-10 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Previsão de Entradas -->
        <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-black text-slate-800">Previsão de Recebíveis</h3>
                    <p class="text-sm text-slate-400 font-medium">O que resta entrar no caixa</p>
                </div>
                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-xs font-black uppercase">PRO</span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">A Vencer</p>
                    <p class="text-xl font-black text-slate-700">R$ {{ number_format($receberTotal - $receberVencido, 2, ',', '.') }}</p>
                </div>
                <div class="bg-red-50 rounded-xl p-4 border border-red-100">
                    <p class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-1">Vencido</p>
                    <p class="text-xl font-black text-red-700">R$ {{ number_format($receberVencido, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500">Total Previsto:</span>
                <span class="text-lg font-black text-brand-secondary">R$ {{ number_format($receberTotal, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Previsão de Saídas -->
        <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-black text-slate-800">Previsão de Pagamentos</h3>
                    <p class="text-sm text-slate-400 font-medium">O que resta sair do caixa</p>
                </div>
                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-lg text-xs font-black uppercase">PRO</span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">A Vencer</p>
                    <p class="text-xl font-black text-slate-700">R$ {{ number_format($pagarTotal - $pagarVencido, 2, ',', '.') }}</p>
                </div>
                <div class="bg-orange-50 rounded-xl p-4 border border-orange-100">
                    <p class="text-[10px] font-black text-orange-400 uppercase tracking-widest mb-1">Vencido</p>
                    <p class="text-xl font-black text-orange-700">R$ {{ number_format($pagarVencido, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between">
                <span class="text-sm font-bold text-slate-500">Total Previsto:</span>
                <span class="text-lg font-black text-red-600">R$ {{ number_format($pagarTotal, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Últimos Títulos Lançados -->
    <div class="rounded-2xl bg-white border border-slate-100 shadow-xl overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-black text-slate-800">Lançamentos Recentes</h3>
            <a href="{{ route('admin.finance.transactions.index') }}" class="text-xs font-bold text-brand-primary hover:underline">Ver todas as movimentações ↗</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase">Data</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase">Descrição</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase">Categoria</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase">Valor Total</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ultimosTitulos as $titulo)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-sm font-bold text-slate-600">{{ $titulo->data_vencimento->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-md px-2 py-1 text-[10px] font-black uppercase {{ $titulo->tipo === 'receber' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $titulo->tipo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-800 leading-tight">
                                {{ $titulo->descricao }}
                                @if($titulo->referencia_id)
                                    <span class="block text-[10px] text-slate-400">Origem: {{ $titulo->origem }} #{{ $titulo->referencia_id }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 font-medium">{{ $titulo->categoria?->nome ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm font-black text-slate-800">R$ {{ number_format($titulo->valor_total, 2, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase ring-1 
                                    @if($titulo->status === 'pago') bg-emerald-50 text-emerald-600 ring-emerald-200 
                                    @elseif($titulo->status === 'vencido') bg-red-50 text-red-600 ring-red-200
                                    @elseif($titulo->status === 'parcial') bg-orange-50 text-orange-600 ring-orange-200
                                    @else bg-slate-50 text-slate-600 ring-slate-200 @endif">
                                    {{ $titulo->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">Nenhum título lançado recentemente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
