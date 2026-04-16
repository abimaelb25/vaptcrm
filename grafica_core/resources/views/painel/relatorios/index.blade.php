{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-10
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Relatórios e Métricas</h1>
            <p class="text-slate-500 font-medium font-serif italic">Inteligência de negócio e acompanhamento de metas</p>
        </div>

        <form method="GET" action="{{ route('admin.bi.index') }}" class="flex items-center gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
            <input type="date" name="data_inicio" value="{{ $inicio->format('Y-m-d') }}" class="rounded-xl border-slate-200 text-xs font-bold text-slate-600 focus:ring-brand-primary">
            <span class="text-slate-400 font-bold">até</span>
            <input type="date" name="data_fim" value="{{ $fim->format('Y-m-d') }}" class="rounded-xl border-slate-200 text-xs font-bold text-slate-600 focus:ring-brand-primary">
            <button type="submit" class="bg-brand-secondary text-white p-2 rounded-xl hover:bg-slate-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </button>
        </form>
    </div>

    <!-- KPIs Rápidos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="rounded-3xl bg-white p-6 shadow-xl border border-slate-100">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Faturamento (Pago)</p>
            <h3 class="text-2xl font-black text-emerald-600">R$ {{ number_format($faturamento, 2, ',', '.') }}</h3>
            <div class="mt-2 text-[10px] text-slate-400 font-medium">No período selecionado</div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-xl border border-slate-100">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Total de Pedidos</p>
            <h3 class="text-2xl font-black text-brand-secondary">{{ $totalPedidos }}</h3>
            <div class="mt-2 text-[10px] text-slate-400 font-medium">Conversão de vendas</div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-xl border border-slate-100">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Orçamentos Gerados</p>
            <h3 class="text-2xl font-black text-amber-500">{{ $totalOrcamentos }}</h3>
            <div class="mt-2 text-[10px] text-slate-400 font-medium">Potencial de faturamento</div>
        </div>

        <div class="rounded-3xl bg-brand-primary p-6 shadow-xl border border-brand-primary/20">
            <p class="text-[10px] font-black uppercase tracking-widest text-white/70 mb-1">Ticket Médio</p>
            <h3 class="text-2xl font-black text-white">R$ {{ $totalPedidos > 0 ? number_format($faturamento / $totalPedidos, 2, ',', '.') : '0,00' }}</h3>
            <div class="mt-2 text-[10px] text-white/60 font-medium">Valor por venda</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Gráficos de Tráfego -->
        <div class="lg:col-span-2 space-y-8">
            <div class="rounded-3xl bg-white p-8 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-lg font-black text-brand-secondary">Engajamento do Catálogo</h4>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Visualizações por Tipo</span>
                </div>
                <div class="h-64">
                    <canvas id="chartAcessos"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="rounded-3xl bg-white p-8 shadow-2xl border border-slate-100">
                    <h4 class="text-sm font-black text-brand-secondary mb-4 uppercase tracking-tighter">Origens de Tráfego</h4>
                    <div class="space-y-4">
                        @foreach($origensTrafego as $origem)
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-600 truncate max-w-[150px]">{{ $origem->origem }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-24 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-primary" style="width: {{ ($origem->total / max(1, $origensTrafego->sum('total'))) * 100 }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-black text-slate-400">{{ $origem->total }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-8 shadow-2xl border border-slate-100">
                    <h4 class="text-sm font-black text-brand-secondary mb-4 uppercase tracking-tighter">Dispositivos</h4>
                    <div class="h-40">
                        <canvas id="chartDispositivos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabelas de Performance -->
        <div class="space-y-8">
            <div class="rounded-3xl bg-brand-secondary p-8 shadow-2xl text-white">
                <h4 class="text-sm font-black mb-6 uppercase tracking-wider text-amber-200">🏆 Top 5 Produtos</h4>
                <div class="space-y-5">
                    @foreach($topProdutos as $item)
                        <div class="flex items-center justify-between border-b border-white/10 pb-3 last:border-0">
                            <div>
                                <p class="text-xs font-black">{{ $item->produto?->nome }}</p>
                                <p class="text-[10px] text-white/50">{{ (int)$item->total_qtd }} unidades vendidas</p>
                            </div>
                            <span class="text-xs font-black text-amber-200">R$ {{ number_format($item->total_vendas, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl bg-white p-8 shadow-2xl border border-slate-100">
                <h4 class="text-sm font-black text-brand-secondary mb-6 uppercase tracking-wider">🔄 Clientes Recorrentes</h4>
                <div class="space-y-5">
                    @foreach($clientesRecorrentes as $feedback)
                        <div class="flex items-center justify-between border-b border-slate-50 pb-3 last:border-0">
                            <div>
                                <p class="text-xs font-black text-slate-700">{{ $feedback->cliente?->nome }}</p>
                                <p class="text-[10px] text-slate-400">{{ (int)$feedback->total_pedidos }} pedidos realizados</p>
                            </div>
                            <span class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-black text-brand-primary">
                                {{ $feedback->total_pedidos }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    <a href="{{ route('admin.bi.export.orders', ['data_init' => $inicio->format('Y-m-d'), 'data_end' => $fim->format('Y-m-d')]) }}" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 py-3 text-[10px] font-black uppercase text-slate-600 hover:bg-slate-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Exportar Pedidos (CSV)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Configurações Globais do Chart.js
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = '#94a3b8';

        // Gráfico de Acessos
        const ctxAcessos = document.getElementById('chartAcessos');
        new Chart(ctxAcessos, {
            type: 'bar',
            data: {
                labels: {!! json_encode($acessosPorTipo->pluck('entidade_tipo')->map(fn($t) => ucfirst($t))) !!},
                datasets: [{
                    label: 'Visualizações',
                    data: {!! json_encode($acessosPorTipo->pluck('total')) !!},
                    backgroundColor: '#FF7A00',
                    borderRadius: 10,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Gráfico de Dispositivos (Rosca)
        const ctxDisp = document.getElementById('chartDispositivos');
        new Chart(ctxDisp, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($dispositivos->pluck('dispositivo')->map(fn($t) => ucfirst($t))) !!},
                datasets: [{
                    data: {!! json_encode($dispositivos->pluck('total')) !!},
                    backgroundColor: ['#1E293B', '#FF7A00', '#94a3b8'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true, font: { size: 10, weight: 'bold' } } }
                }
            }
        });
    </script>
</x-layouts.app>

