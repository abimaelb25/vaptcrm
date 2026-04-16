<x-layouts.super-admin>
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gerenciamento de Planos SaaS</h1>
            <p class="text-sm text-gray-500 mt-1">Configure os pacotes, limites e valores ofertados aos tenants.</p>
        </div>
        <div>
            <button onclick="document.getElementById('modal-plano').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i> Novo Plano
            </button>
        </div>
    </div>

    <!-- Tabela de Planos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs uppercase text-gray-500 bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-4 font-semibold">Nome do Plano</th>
                        <th class="px-6 py-4 font-semibold text-center">Mensalidade</th>
                        <th class="px-6 py-4 font-semibold text-center">Limites (Usu/Prod)</th>
                        <th class="px-6 py-4 font-semibold text-center">Assinaturas Ativas</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($planos as $plano)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $plano->nome }}</div>
                                <div class="text-xs text-gray-400 font-mono">Stripe ID: {{ $plano->stripe_price_id ?? 'Nenhum' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-medium text-gray-900">
                                R$ {{ number_format($plano->preco_mensal, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-gray-600">
                                {{ $plano->limite_funcionarios ?? '∞' }} / {{ $plano->limite_produtos ?? '∞' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $plano->assinaturas_count }} Lojas
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($plano->ativo)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Ativo</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Inativo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <button onclick="editarPlano({{ $plano->toJson() }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                                
                                @if($plano->assinaturas_count === 0)
                                    <form action="{{ route('superadmin.planos.destroy', $plano->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza que deseja excluir este plano?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                                    </form>
                                @else
                                    <span class="text-gray-400 cursor-not-allowed" title="Não é possível excluir plano com assinantes">Excluir</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Nenhum plano cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Novo/Edição Plano -->
    <div id="modal-plano" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="fecharModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
                <form id="form-plano" action="{{ route('superadmin.planos.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Novo Plano SaaS</h3>
                            <button type="button" onclick="fecharModal()" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times"></i></button>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Nome do Plano</label>
                                    <input type="text" name="nome" id="input_nome" required class="mt-1 border border-gray-300 block w-full rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Preço Mensal (R$)</label>
                                    <input type="number" step="0.01" name="preco_mensal" id="input_preco" required class="mt-1 border border-gray-300 block w-full rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stripe Price ID (Opcional)</label>
                                    <input type="text" name="stripe_price_id" id="input_stripe" class="mt-1 border border-gray-300 block w-full rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="price_1NXXXXXX">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Limite de Usuários (Vazio = ∞)</label>
                                    <input type="number" name="limite_funcionarios" id="input_limite_usu" min="1" class="mt-1 border border-gray-300 block w-full rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Limite de Produtos (Vazio = ∞)</label>
                                    <input type="number" name="limite_produtos" id="input_limite_prod" min="1" class="mt-1 border border-gray-300 block w-full rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>
                            
                            <div id="container-ativo" class="hidden mt-4 pt-4 border-t border-gray-100 flex items-center">
                                <input type="checkbox" name="ativo" id="input_ativo" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="input_ativo" class="ml-2 block text-sm text-gray-900">
                                    Plano Ativo (Visível para venda)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar Plano
                        </button>
                        <button type="button" onclick="fecharModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function fecharModal() {
            document.getElementById('modal-plano').classList.add('hidden');
            // reset form
            document.getElementById('form-plano').action = "{{ route('superadmin.planos.store') }}";
            document.getElementById('form-method').value = "POST";
            document.getElementById('modal-title').innerText = "Novo Plano SaaS";
            document.getElementById('form-plano').reset();
            document.getElementById('container-ativo').classList.add('hidden');
        }

        function editarPlano(plano) {
            document.getElementById('form-plano').action = `/super-admin/planos/${plano.id}`;
            document.getElementById('form-method').value = "PUT";
            document.getElementById('modal-title').innerText = "Editar Plano SaaS";
            
            document.getElementById('input_nome').value = plano.nome;
            document.getElementById('input_preco').value = plano.preco_mensal;
            document.getElementById('input_stripe').value = plano.stripe_price_id || '';
            document.getElementById('input_limite_usu').value = plano.limite_funcionarios || '';
            document.getElementById('input_limite_prod').value = plano.limite_produtos || '';
            
            document.getElementById('container-ativo').classList.remove('hidden');
            document.getElementById('input_ativo').checked = plano.ativo == 1;

            document.getElementById('modal-plano').classList.remove('hidden');
        }
    </script>
</x-layouts.super-admin>
