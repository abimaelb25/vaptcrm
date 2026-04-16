<x-layouts.super-admin>
    <!-- Header with Back Button -->
    <div class="mb-6 flex space-x-4 items-center">
        <a href="{{ route('superadmin.lojas.index') }}" class="text-gray-400 hover:text-gray-600 bg-white border border-gray-200 rounded-lg p-2 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $loja->nome_fantasia }}</h1>
            <p class="text-sm text-gray-500 mt-1">Detalhes completos do Tenant</p>
        </div>
        
        <div class="ml-auto flex space-x-3">
            @if($loja->estaBloqueada())
                <form action="{{ route('superadmin.lojas.desbloquear', $loja->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none">
                        <i class="fas fa-unlock mr-2"></i> Desbloquear Loja
                    </button>
                </form>
            @else
                <!-- Modal Trigger -->
                <button onclick="document.getElementById('modal-bloqueio').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none">
                    <i class="fas fa-lock mr-2"></i> Bloquear Loja
                </button>
            @endif
        </div>
    </div>

    @if($loja->estaBloqueada())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-r-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Loja Bloqueada</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p><strong>Bloqueada em:</strong> {{ $loja->bloqueada_em->format('d/m/Y H:i') }}</p>
                        <p><strong>Motivo:</strong> {{ $loja->motivo_bloqueio }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Info & Stats -->
        <div class="space-y-8">
            <!-- Card: Loja Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900">Informações Basais</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4 text-sm">
                        <div class="grid grid-cols-3">
                            <dt class="font-medium text-gray-500">ID / UUID</dt>
                            <dd class="col-span-2 text-gray-900">{{ $loja->id }}</dd>
                        </div>
                        <div class="grid grid-cols-3">
                            <dt class="font-medium text-gray-500">Subdomínio</dt>
                            <dd class="col-span-2 text-gray-900 font-mono text-indigo-600">{{ $loja->subdominio }}</dd>
                        </div>
                        <div class="grid grid-cols-3">
                            <dt class="font-medium text-gray-500">Responsável</dt>
                            <dd class="col-span-2 text-gray-900">{{ $loja->responsavel_nome }}</dd>
                        </div>
                        <div class="grid grid-cols-3">
                            <dt class="font-medium text-gray-500">Email</dt>
                            <dd class="col-span-2 text-gray-900">{{ $loja->responsavel_email }}</dd>
                        </div>
                        <div class="grid grid-cols-3">
                            <dt class="font-medium text-gray-500">WhatsApp</dt>
                            <dd class="col-span-2 text-gray-900">{{ $loja->responsavel_whatsapp }}</dd>
                        </div>
                        <div class="grid grid-cols-3 pt-4 border-t border-gray-100">
                            <dt class="font-medium text-gray-500">Cadastro</dt>
                            <dd class="col-span-2 text-gray-900">{{ $loja->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Card: Limits / Stats -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900">Consumo em Tempo Real</h3>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Usuários -->
                    <div>
                        <div class="flex justify-between items-center mb-1 text-sm">
                            <span class="font-medium text-gray-700">Usuários</span>
                            <span class="text-gray-500">{{ $totalUsuarios }} / {{ $loja->plano->limite_funcionarios ?? 'Ilimitado' }}</span>
                        </div>
                        @php $pctUsuarios = $loja->plano && $loja->plano->limite_funcionarios ? min(100, ($totalUsuarios / $loja->plano->limite_funcionarios) * 100) : 0; @endphp
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $pctUsuarios }}%"></div>
                        </div>
                    </div>
                    
                    <!-- Produtos -->
                    <div>
                        <div class="flex justify-between items-center mb-1 text-sm">
                            <span class="font-medium text-gray-700">Produtos</span>
                            <span class="text-gray-500">{{ $totalProdutos }} / {{ $loja->plano->limite_produtos ?? 'Ilimitado' }}</span>
                        </div>
                        @php $pctProdutos = $loja->plano && $loja->plano->limite_produtos ? min(100, ($totalProdutos / $loja->plano->limite_produtos) * 100) : 0; @endphp
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $pctProdutos }}%"></div>
                        </div>
                    </div>

                    <!-- Pedidos -->
                    <div>
                        <div class="flex justify-between items-center mb-1 text-sm">
                            <span class="font-medium text-gray-700">Pedidos Totais</span>
                            <span class="text-gray-900 font-bold">{{ $totalPedidos }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Tabs -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Assinatura Ativa -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-base font-semibold text-gray-900">Estado da Assinatura</h3>
                </div>
                <div class="p-6">
                    @if($assinaturaAtual)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Plano</p>
                                <p class="mt-1 font-semibold text-gray-900">{{ $assinaturaAtual->plano->nome }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Status</p>
                                <p class="mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ strtoupper($assinaturaAtual->status) }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Customer Stripe</p>
                                <p class="mt-1 font-mono text-sm text-gray-600 truncate">{{ $assinaturaAtual->stripe_customer_id ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Sub Stripe</p>
                                <p class="mt-1 font-mono text-sm text-gray-600 truncate">{{ $assinaturaAtual->stripe_subscription_id ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="mt-6 border-t border-gray-100 pt-6 grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Fim do Trial</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $assinaturaAtual->trial_ends_at ? $assinaturaAtual->trial_ends_at->format('d/m/Y H:i') : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Renovação / Vencimento</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $assinaturaAtual->ends_at ? $assinaturaAtual->ends_at->format('d/m/Y H:i') : '-' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm text-center py-4">Nenhuma assinatura SaaS vinculada a esta loja.</p>
                    @endif
                </div>
            </div>

            <!-- Pagamentos SaaS -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900">Histórico de Pagamentos SaaS</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs uppercase text-gray-500 border-b border-gray-200">
                                <th class="px-6 py-3 font-semibold">Data</th>
                                <th class="px-6 py-3 font-semibold">Valor</th>
                                <th class="px-6 py-3 font-semibold">Status</th>
                                <th class="px-6 py-3 font-semibold">Fatura / PI (Stripe)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($loja->pagamentosSaaS as $pagamento)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $pagamento->vencimento_em ? $pagamento->vencimento_em->format('d/m/Y') : $pagamento->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        R$ {{ number_format($pagamento->valor, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $pagamento->status === 'pago' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $pagamento->status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $pagamento->status === 'falhou' ? 'bg-red-100 text-red-800' : '' }}
                                        ">
                                            {{ ucfirst($pagamento->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-mono text-gray-500">
                                        {{ $pagamento->stripe_invoice_id ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        Nenhum pagamento registrado ainda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notificações de Inadimplência -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900">Notificações Administrativas</h3>
                </div>
                <div class="p-0">
                    <ul class="divide-y divide-gray-100">
                        @forelse($loja->notificacoesInadimplencia as $notificacao)
                            <li class="p-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-0.5">
                                        @if($notificacao->tipo === 'bloqueio')
                                            <i class="fas fa-ban text-red-500"></i>
                                        @elseif($notificacao->tipo === 'aviso_vencimento')
                                            <i class="fas fa-bell text-yellow-500"></i>
                                        @else
                                            <i class="fas fa-info-circle text-blue-500"></i>
                                        @endif
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900 border-b border-gray-100 pb-1 w-full flex justify-between">
                                                <span>{{ strtoupper(str_replace('_', ' ', $notificacao->tipo)) }}</span>
                                                <span class="text-xs text-gray-500 font-normal">{{ $notificacao->enviado_em->format('d/m/Y H:i') }}</span>
                                            </h4>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-600 border border-gray-100 bg-gray-50 p-3 rounded italic">{{ $notificacao->mensagem }}</p>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="px-6 py-8 text-center text-gray-500 text-sm">
                                Nenhuma notificação de inadimplência foi enviada para esta loja.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Bloqueio -->
    <div id="modal-bloqueio" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('modal-bloqueio').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('superadmin.lojas.bloquear', $loja->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Bloquear Loja</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">Esta ação impedirá completamente o acesso da loja ao painel e as vendas pelo catálogo público.</p>
                                    
                                    <div class="mb-4">
                                        <label for="motivo" class="block text-sm font-medium text-gray-700">Motivo do Bloqueio</label>
                                        <input type="text" name="motivo" id="motivo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm p-2 border" placeholder="Ex: Inadimplência > 5 dias" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Confirmar Bloqueio
                        </button>
                        <button type="button" onclick="document.getElementById('modal-bloqueio').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.super-admin>
