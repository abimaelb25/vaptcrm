<x-layouts.super-admin>
    <!-- Header -->
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Visão geral do desempenho da plataforma SaaS</p>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- MRR -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between overflow-hidden relative">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-dollar-sign text-6xl text-green-500"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Receita Recorrente (MRR)</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">R$ {{ number_format($mrrAtual, 2, ',', '.') }}</p>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-500 font-medium flex items-center">
                    <i class="fas fa-arrow-up mr-1 text-xs"></i> Baseado nas ativas
                </span>
            </div>
        </div>

        <!-- Total Lojas -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-store text-6xl text-indigo-500"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total de Lojas</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalLojas }}</p>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-indigo-500 font-medium">+{{ $novasLojas30Dias }}</span>
                <span class="text-gray-500 ml-2">nos últimos 30 dias</span>
            </div>
        </div>

        <!-- Ativas / Trial -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-check-circle text-6xl text-blue-500"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Assinantes Ativos</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $lojasAtivas }}</p>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-blue-500 font-medium">{{ $lojasTrial }} em Trial</span>
                <span class="text-gray-500 ml-2">experimentando</span>
            </div>
        </div>

        <!-- Inadimplentes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-exclamation-triangle text-6xl text-red-500"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Lojas Bloqueadas</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ $lojasInadimplentes }}</p>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-red-500 font-medium flex items-center">
                    Por Inadimplência
                </span>
            </div>
        </div>
    </div>

    <!-- Charts and Tables Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Lojas Recentes -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-800">Últimas Lojas Cadastradas</h3>
                <a href="{{ route('superadmin.lojas.index') }}" class="text-sm text-indigo-600 font-medium hover:text-indigo-800">Ver todas</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 text-sm text-gray-500 bg-gray-50">
                            <th class="p-3 font-medium rounded-tl-lg">Loja</th>
                            <th class="p-3 font-medium">Responsável</th>
                            <th class="p-3 font-medium">Subdomínio</th>
                            <th class="p-3 font-medium">Plano</th>
                            <th class="p-3 font-medium">Cadastro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($lojasRecentes as $loja)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-3 text-sm">
                                    <a href="{{ route('superadmin.lojas.show', $loja->id) }}" class="font-medium text-indigo-600 hover:underline">{{ $loja->nome_fantasia }}</a>
                                </td>
                                <td class="p-3 text-sm text-gray-600">{{ $loja->responsavel_nome }}</td>
                                <td class="p-3 text-sm text-gray-600">{{ $loja->subdominio }}</td>
                                <td class="p-3 text-sm">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $loja->plano ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $loja->plano->nome ?? 'Nenhum' }}
                                    </span>
                                </td>
                                <td class="p-3 text-sm text-gray-500">{{ $loja->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-500">Nenhuma loja cadastrada ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Planos Distribution -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Assinaturas por Plano</h3>
            
            <div class="space-y-4">
                @forelse($assinaturasPorPlano as $plano)
                    @php
                        $totalAssinaturas = $assinaturasPorPlano->sum('assinaturas_count');
                        $percentual = $totalAssinaturas > 0 ? round(($plano->assinaturas_count / $totalAssinaturas) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1 text-sm">
                            <span class="font-medium text-gray-700">{{ $plano->nome }}</span>
                            <span class="text-gray-500">{{ $plano->assinaturas_count }} ({{ $percentual }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $percentual }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center py-4">Nenhum plano com assinantes.</p>
                @endforelse
            </div>
        </div>

    </div>

</x-layouts.super-admin>
