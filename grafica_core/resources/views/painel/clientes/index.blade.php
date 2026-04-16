{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-06 00:00 -03:00
--}}
<x-layouts.app>
    <div class="mb-6 flex flex-col sm:flex-row items-center justify-between">
        <h1 class="text-3xl font-black text-brand-secondary">Gestão de Clientes</h1>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <span class="h-2 w-8 rounded-full bg-brand-primary shadow-[0_0_8px_rgba(255,122,0,0.6)]"></span>
            <span class="h-2 w-8 rounded-full bg-brand-accent shadow-[0_0_8px_rgba(25,118,210,0.6)]"></span>
        </div>
    </div>

    <!-- Filtros de Busca -->
    <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm border border-slate-100 flex items-center">
        <form method="GET" class="w-full flex flex-col sm:flex-row gap-3">
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
                <input name="busca" value="{{ $busca }}" placeholder="Buscar por Nome, Telefone, E-mail ou Empresa..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 shadow-inner focus:border-brand-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-primary">
            </div>
            <button class="shrink-0 rounded-xl bg-slate-800 px-6 py-2.5 text-sm font-bold text-white shadow transition-all hover:bg-slate-900">Pesquisar</button>
            @if($busca)
                <a href="{{ route('admin.sales.clientes.index') }}" class="shrink-0 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-600 border border-slate-200 transition-all hover:bg-slate-200 flex items-center justify-center">Limpar</a>
            @endif
        </form>
    </div>

    <!-- Form Container -->
    <div class="rounded-2xl border border-slate-100 bg-white/80 p-6 shadow-lg backdrop-blur-md mb-8">
        <h2 id="formTitle" class="text-xl font-bold text-slate-700 mb-4 border-b pb-2">Cadastrar Novo Cliente</h2>
        <form id="formCliente" method="POST" action="{{ route('admin.sales.clientes.store') }}" enctype="multipart/form-data" class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @csrf
            
            <div class="lg:col-span-2">
                <label class="mb-1 block text-sm font-bold text-slate-600">Nome Completo <span class="text-status-error">*</span></label>
                <input name="nome" value="{{ old('nome') }}" placeholder="Ex: João da Silva" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 shadow-inner focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary" required>
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold text-slate-600">Tipo de Pessoa <span class="text-status-error">*</span></label>
                <div class="flex gap-2 h-11 items-center">
                    <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-3 py-2 rounded-lg border border-slate-200 w-full justify-center transition-colors hover:border-brand-primary">
                        <input type="radio" name="tipo_pessoa" value="F" class="text-brand-primary focus:ring-brand-primary h-4 w-4" checked onchange="toggleMask()"> 
                        <span class="text-sm font-bold text-slate-700">Física</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-3 py-2 rounded-lg border border-slate-200 w-full justify-center transition-colors hover:border-brand-primary">
                        <input type="radio" name="tipo_pessoa" value="J" class="text-brand-primary focus:ring-brand-primary h-4 w-4" onchange="toggleMask()"> 
                        <span class="text-sm font-bold text-slate-700">Jurídica</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold text-slate-600">CPF / CNPJ</label>
                <input name="cpf_cnpj" id="cpfCnpjInput" value="{{ old('cpf_cnpj') }}" placeholder="000.000.000-00" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 shadow-inner focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold text-slate-600">Data de Nascimento / Fundação</label>
                <input type="date" name="data_nascimento" value="{{ old('data_nascimento') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 shadow-inner text-slate-600 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold text-slate-600">Telefone / Celular</label>
                <input name="telefone" value="{{ old('telefone') }}" placeholder="(11) 90000-0000" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 shadow-inner focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
            </div>
            
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-600">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="cliente@provedor.com" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 shadow-inner focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold text-slate-600">Empresa (Opcional)</label>
                <input name="empresa" value="{{ old('empresa') }}" placeholder="Nome da Loja/Agência..." class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 shadow-inner focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
            </div>

            <div>
                <label class="mb-1 block text-sm font-bold text-slate-600">Origem Principal</label>
                <select name="origem_lead" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 shadow-inner text-slate-600 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                    <option value="">Selecione...</option>
                    <option value="WhatsApp">WhatsApp</option>
                    <option value="Site">Site Principal</option>
                    <option value="Instagram">Instagram</option>
                    <option value="Google">Google / Ads</option>
                    <option value="Indicação">Indicação</option>
                    <option value="Balcão">Loja Física / Balcão</option>
                </select>
            </div>

            <div class="md:col-span-2 lg:col-span-3 rounded-xl border-dashed border-2 border-slate-200 bg-slate-50 p-4 transition-all hover:border-brand-primary/40 flex items-center gap-4">
                <div class="h-16 w-16 shrink-0 rounded-full bg-slate-200 border-2 border-white shadow-sm flex items-center justify-center text-slate-400 text-2xl overflow-hidden" id="avatarPreviewContainer">
                    👤
                </div>
                <div class="w-full">
                    <label class="mb-1 block text-sm font-bold text-slate-700">Foto de Perfil / Logo (Opcional)</label>
                    <input type="file" name="avatar_upload" id="avatar_upload" accept="image/jpeg,image/jpg,image/png,image/webp" class="w-full text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-slate-200 file:px-4 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-300">
                </div>
            </div>
            
            <div class="md:col-span-2 lg:col-span-3 flex justify-end gap-3 pt-2">
                <button type="button" onclick="cancelarEdicao()" class="rounded-xl bg-slate-100 px-6 py-3 text-sm font-bold text-slate-600 shadow-sm transition-all duration-300 hover:bg-slate-200 hidden" id="btnCancel">Cancelar</button>
                <button type="submit" id="btnSubmit" class="rounded-xl bg-gradient-to-r from-brand-primary to-orange-500 px-8 py-3 text-sm font-bold text-white shadow-md transition-transform duration-300 hover:scale-105 hover:shadow-lg">Salvar Cadastro</button>
            </div>
        </form>
    </div>

    <!-- Tabela de Clientes -->
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-brand-secondary text-left text-white">
                <tr>
                    <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs">Identificação</th>
                    <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs">Empresa / Documento</th>
                    <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs">Contatos</th>
                    <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs w-52 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($clientes as $cliente)
                    <tr class="transition-colors hover:bg-slate-50 group">
                        <td class="px-5 py-4 w-64">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 shrink-0 rounded-full bg-brand-primary/10 border border-brand-primary/20 flex items-center justify-center text-brand-primary font-bold overflow-hidden">
                                    @if($cliente->avatar)
                                        <img src="{{ asset('storage/' . $cliente->avatar) }}" alt="Avatar" class="h-full w-full object-cover">
                                    @else
                                        {{ substr(mb_strtoupper($cliente->nome, 'UTF-8'), 0, 1) }}
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800">{{ $cliente->nome }}</span>
                                    <span class="text-xs text-slate-400">ID: #{{ $cliente->id }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-slate-600">
                            @if($cliente->empresa)
                                <div class="font-bold text-slate-700">{{ $cliente->empresa }}</div>
                            @endif
                            @if($cliente->cpf_cnpj)
                                <div class="text-xs font-mono bg-slate-100 inline-block px-1.5 py-0.5 rounded mt-0.5">
                                    <span class="font-bold text-slate-400">{{ $cliente->tipo_pessoa == 'J' ? 'CNPJ' : 'CPF' }}:</span> {{ $cliente->cpf_cnpj }}
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Sem documento</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $telefoneLimpo = preg_replace('/[^0-9]/', '', $cliente->telefone);
                            @endphp
                            @if($cliente->telefone)
                                <a href="https://wa.me/55{{ $telefoneLimpo }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-full bg-status-success/10 px-2.5 py-1 text-xs font-bold text-status-success transition-colors hover:bg-status-success hover:text-white" title="Chamar no WhatsApp">
                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.372-12 12 0 2.19.593 4.24 1.621 6L0 24l6.196-1.583c1.716.945 3.69 1.488 5.804 1.488 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                    {{ $cliente->telefone }}
                                </a>
                            @else
                                <span class="text-xs text-slate-400">Sem telefone</span>
                            @endif
                            <div class="text-xs text-slate-500 mt-1">{{ $cliente->email }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.sales.clientes.show', $cliente->id) }}" class="rounded-lg bg-blue-50 px-3 py-2 text-sm font-bold text-brand-accent transition-colors hover:bg-blue-100 ring-1 ring-blue-200" title="Ver Perfil Completo">
                                    Perfil
                                </a>
                                <button onclick="editarCliente({{ trim(json_encode($cliente, JSON_HEX_APOS | JSON_HEX_QUOT)) }})" class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-bold text-status-success transition-colors hover:bg-emerald-100 ring-1 ring-emerald-200" title="Editar Dados Rápidos">
                                    Edição
                                </button>
                                @if(auth()->user()->temPermissao('apagar_cliente'))
                                <form method="POST" action="{{ route('admin.sales.clientes.destroy', $cliente->id) }}" onsubmit="return confirm('Deseja realmente remover os dados visíveis deste cliente?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-red-50 px-3 py-2 text-sm font-bold text-red-600 transition-colors hover:bg-red-100 ring-1 ring-red-200" title="Excluir">
                                        Lixo
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center">
                                <span class="text-4xl mb-3">👥</span>
                                <span class="font-semibold text-lg">Nenhum cliente encotrado!</span>
                                <span class="text-sm">Preencha o formulário acima para inserir alguém.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-5 py-3 bg-white">
            {{ $clientes->links() }}
        </div>
    </div>

    <!-- Scripts da Lógica Cliente -->
    <script>
        function toggleMask() {
            const input = document.getElementById('cpfCnpjInput');
            const ehJuridica = document.querySelector('input[name="tipo_pessoa"][value="J"]').checked;
            if(ehJuridica) {
                input.placeholder = "00.000.000/0000-00";
            } else {
                input.placeholder = "000.000.000-00";
            }
        }

        function editarCliente(cliente) {
            const form = document.getElementById('formCliente');
            form.action = `/painel/vendas/clientes/${cliente.id}`;
            
            let methodInput = form.querySelector('input[name="_method"]');
            if(!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                form.appendChild(methodInput);
            }
            methodInput.value = 'PATCH';
            
            // Popula form
            form.querySelector('input[name="nome"]').value = cliente.nome || '';
            form.querySelector('input[name="cpf_cnpj"]').value = cliente.cpf_cnpj || '';
            form.querySelector('input[name="telefone"]').value = cliente.telefone || '';
            form.querySelector('input[name="email"]').value = cliente.email || '';
            form.querySelector('input[name="empresa"]').value = cliente.empresa || '';
            if (cliente.data_nascimento) {
                form.querySelector('input[name="data_nascimento"]').value = cliente.data_nascimento.split('T')[0];
            } else {
                form.querySelector('input[name="data_nascimento"]').value = '';
            }
            
            if(cliente.tipo_pessoa === 'J') {
                form.querySelector('input[name="tipo_pessoa"][value="J"]').checked = true;
            } else {
                form.querySelector('input[name="tipo_pessoa"][value="F"]').checked = true;
            }
            toggleMask();

            if(cliente.origem_lead) {
                const slc = form.querySelector('select[name="origem_lead"]');
                for(let opt of slc.options) {
                    if(opt.value === cliente.origem_lead) {
                        opt.selected = true;
                        break;
                    }
                }
            }

            document.getElementById('formTitle').innerText = `Atualizando Cliente: #${cliente.id} - ${cliente.nome}`;
            document.getElementById('btnSubmit').innerText = 'Salvar Alterações';
            document.getElementById('btnCancel').classList.remove('hidden');
            form.scrollIntoView({behavior: "smooth"});
        }

        function cancelarEdicao() {
            const form = document.getElementById('formCliente');
            form.action = `{{ route('admin.sales.clientes.store') }}`;
            let methodInput = form.querySelector('input[name="_method"]');
            if(methodInput) methodInput.remove();
            form.reset();
            document.getElementById('formTitle').innerText = 'Cadastrar Novo Cliente';
            document.getElementById('btnSubmit').innerText = 'Cadastrar Cliente';
            document.getElementById('btnCancel').classList.add('hidden');
        }
    </script>
</x-layouts.app>
