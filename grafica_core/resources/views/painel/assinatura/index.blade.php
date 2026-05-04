<x-layouts.app titulo="Minha Assinatura - Gráfica Vapt Vupt">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-brand-secondary tracking-tight">Gerenciamento de Assinatura</h1>
        <p class="text-slate-500 font-medium">Controle seu plano, faturamento e limites de uso do sistema.</p>
    </div>

    @if(!empty($alerts))
        <div class="mb-6 space-y-2">
            @foreach($alerts as $alert)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 font-semibold">
                    {{ $alert }}
                </div>
            @endforeach
        </div>
    @endif

    <!-- Status Atual -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm p-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-brand-primary/10 flex items-center justify-center text-brand-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-800">Plano {{ $assinatura->plano->nome }}</h2>
                    <p class="text-sm font-bold {{ $assinatura->ativa() ? 'text-emerald-600' : 'text-rose-600' }}">
                        Status: {{ strtoupper($assinatura->status) }} 
                        @if($assinatura->emTrial())
                            (Trial termina em {{ $assinatura->trial_ends_at->format('d/m/Y') }})
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex gap-3">
                @if($assinatura->stripe_customer_id)
                    <a href="{{ route('admin.billing.portal') }}" class="px-6 py-3 font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                        </svg>
                        Faturamento
                    </a>
                @endif
            </div>
        </div>

        <!-- Card de Resumo Financeiro -->
        <div class="bg-brand-secondary rounded-3xl p-8 text-white relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-white/60 font-bold text-xs uppercase tracking-widest mb-1">Próxima Cobrança</p>
                <h3 class="text-3xl font-black">R$ {{ number_format($assinatura->plano->preco_mensal, 2, ',', '.') }}</h3>
                <p class="text-sm text-white/40 mt-1">Vencimento: {{ ($assinatura->ends_at ?? $assinatura->trial_ends_at)?->format('d/m/Y') ?? 'N/A' }}</p>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/5 rounded-full blur-2xl"></div>
        </div>
    </div>

    <!-- Limites de Uso -->
    <div class="mb-12">
        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-100 pb-2">Utilização de Recursos</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            
            <!-- Produtos -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200">
                <div class="flex justify-between items-end mb-4">
                    <div>
                        <span class="text-xs font-black text-slate-400 uppercase">Catálogo de Produtos</span>
                        <h4 class="text-2xl font-black text-slate-800">{{ $usage['produtos']['usage'] }} <span class="text-slate-300 font-bold text-lg">/ {{ $usage['produtos']['limit'] ?? '∞' }}</span></h4>
                    </div>
                </div>
                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-primary transition-all duration-1000" style="width: {{ $usage['produtos']['percent'] }}%"></div>
                </div>
            </div>

            <!-- Funcionários -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200">
                <div class="flex justify-between items-end mb-4">
                    <div>
                        <span class="text-xs font-black text-slate-400 uppercase">Equipe Operacional</span>
                        <h4 class="text-2xl font-black text-slate-800">{{ $usage['funcionarios']['usage'] }} <span class="text-slate-300 font-bold text-lg">/ {{ $usage['funcionarios']['limit'] ?? '∞' }}</span></h4>
                    </div>
                </div>
                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 transition-all duration-1000" style="width: {{ $usage['funcionarios']['percent'] }}%"></div>
                </div>
            </div>

            <!-- Pedidos no ciclo -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200">
                <div class="flex justify-between items-end mb-4">
                    <div>
                        <span class="text-xs font-black text-slate-400 uppercase">Pedidos no Ciclo</span>
                        <h4 class="text-2xl font-black text-slate-800">{{ $usage['pedidos_mes']['usage'] }} <span class="text-slate-300 font-bold text-lg">/ {{ $usage['pedidos_mes']['limit'] ?? '∞' }}</span></h4>
                    </div>
                </div>
                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 transition-all duration-1000" style="width: {{ $usage['pedidos_mes']['percent'] }}%"></div>
                </div>
            </div>

            <!-- Storage -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200">
                <div class="flex justify-between items-end mb-4">
                    <div>
                        <span class="text-xs font-black text-slate-400 uppercase">Armazenamento</span>
                        <h4 class="text-2xl font-black text-slate-800">{{ $usage['storage_mb']['usage'] }}MB <span class="text-slate-300 font-bold text-lg">/ {{ $usage['storage_mb']['limit'] ?? '∞' }}MB</span></h4>
                    </div>
                </div>
                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full {{ ($usage['storage_policy']['level'] ?? 'normal') === 'critical' || ($usage['storage_policy']['level'] ?? 'normal') === 'blocked' ? 'bg-rose-500' : 'bg-amber-500' }} transition-all duration-1000" style="width: {{ $usage['storage_mb']['percent'] }}%"></div>
                </div>
            </div>
            
        </div>
    </div>

    @if($upgradeRecommended)
        <div class="mb-10 bg-gradient-to-r from-brand-secondary to-slate-900 text-white rounded-3xl p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-xl font-black">Seu uso indica potencial de upgrade</h3>
                <p class="text-white/80 font-medium">Evite bloqueios e continue escalando com um plano superior.</p>
            </div>
            <a href="{{ route('admin.billing.index') }}" class="px-6 py-3 bg-white text-slate-900 rounded-xl font-black hover:bg-slate-100 transition-colors">
                Ver opções de upgrade
            </a>
        </div>
    @endif

    <!-- Comparação de Planos -->
    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-100 pb-2">Planos Disponíveis</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-12">
        @foreach($planos as $plano)
            <div class="relative bg-white rounded-3xl border {{ $assinatura->plano_id === $plano->id ? 'border-brand-primary ring-4 ring-brand-primary/5' : 'border-slate-200' }} p-6 transition-all hover:shadow-xl">
                @if($assinatura->plano_id === $plano->id)
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-brand-primary text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg">Seu Plano</span>
                @endif
                
                <h4 class="text-xl font-black text-slate-800 mb-1">{{ $plano->nome }}</h4>
                <div class="flex items-baseline gap-1 mb-6">
                    <span class="text-2xl font-black text-slate-800">R$ {{ number_format($plano->preco_mensal, 2, ',', '.') }}</span>
                    <span class="text-xs font-bold text-slate-400">/mês</span>
                </div>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-2 text-sm text-slate-600">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Pedidos Ilimitados
                    </li>
                    <li class="flex items-center gap-2 text-sm text-slate-600">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ $plano->limite_produtos ?? 'Produtos Ilimitados' }} {{ $plano->temLimiteProdutos() ? 'Produtos' : '' }}
                    </li>
                    <li class="flex items-center gap-2 text-sm text-slate-600">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ $plano->limite_funcionarios ?? 'Equipe Ilimitada' }} {{ $plano->temLimiteFuncionarios() ? 'Funcionários' : '' }}
                    </li>
                </ul>

                @if($assinatura->plano_id !== $plano->id)
                    <a href="{{ route('admin.billing.subscribe', $plano) }}" class="block w-full text-center py-3 font-black text-white bg-brand-secondary hover:bg-slate-800 rounded-xl transition-all shadow-md">
                        Mudar para este
                    </a>
                @else
                    <button disabled class="w-full py-3 font-black text-slate-400 bg-slate-50 border border-slate-100 rounded-xl cursor-not-allowed">
                        Plano Atual
                    </button>
                @endif
            </div>
        @endforeach
    </div>
</x-layouts.app>

