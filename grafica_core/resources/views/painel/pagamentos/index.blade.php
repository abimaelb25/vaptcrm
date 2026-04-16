{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-11
--}}
<x-layouts.app>
    <div class="mb-8">
        <h1 class="text-3xl font-black tracking-tight text-brand-secondary">Pagamentos</h1>
        <p class="text-slate-500 font-medium">Configure integrações de pagamento e cupons de desconto para seu catálogo</p>
    </div>

    {{-- Frete Fixo --}}
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white overflow-hidden" data-accordion>
        <button type="button" data-accordion-toggle class="w-full flex items-center justify-between p-5 hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"/><path d="m12 15 5 5"/><path d="m17 15-5 5"/></svg>
                </div>
                <div class="text-left">
                    <h3 class="font-bold text-slate-800">Frete Fixo</h3>
                    <p class="text-sm text-slate-500">Defina um valor fixo de entrega para todos os pedidos</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($freteConfig['ativo'])
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Ativo</span>
                @else
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Inativo</span>
                @endif
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform" data-chevron><path d="m6 9 6 6 6-6"/></svg>
            </div>
        </button>
        <div data-accordion-content class="hidden border-t border-slate-100">
            <form action="{{ route('admin.finance.pagamentos.frete') }}" method="POST" class="p-5 space-y-5">
                @csrf
                @method('PUT')

                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-bold text-slate-700">Habilitar frete fixo</p>
                            <p class="text-xs text-slate-500">Cobrar um valor fixo de entrega em todos os pedidos</p>
                        </div>
                        <label class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-200 transition-colors has-[:checked]:bg-brand-primary">
                            <input type="checkbox" name="ativo" value="1" {{ $freteConfig['ativo'] ? 'checked' : '' }} class="peer sr-only">
                            <span class="inline-block h-4 w-4 translate-x-1 rounded-full bg-white transition-transform peer-checked:translate-x-6"></span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-bold text-slate-700">Valor do Frete (R$)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">R$</span>
                        <input type="number" name="valor" step="0.01" min="0" value="{{ $freteConfig['valor'] }}" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <label class="relative inline-flex h-5 w-9 items-center rounded-full bg-slate-200 transition-colors has-[:checked]:bg-brand-primary">
                        <input type="checkbox" name="obrigatorio" value="1" {{ $freteConfig['obrigatorio'] ? 'checked' : '' }} class="peer sr-only">
                        <span class="inline-block h-3 w-3 translate-x-1 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                    </label>
                    <span class="text-sm font-medium text-slate-700">Aplicar em todos os pedidos (mesmo retirada)</span>
                </div>

                <div class="rounded-xl bg-amber-50 p-4 flex gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600 shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    <p class="text-sm text-amber-800">O frete fixo será aplicado em <strong>todos os pedidos</strong>, independente da forma de finalização (Mercado Pago ou WhatsApp). O cliente não pode alterar esse valor.</p>
                </div>

                <button type="submit" class="rounded-xl bg-brand-primary px-6 py-2.5 font-bold text-white hover:bg-orange-600 transition-colors">Salvar Configurações de Frete</button>
            </form>
        </div>
    </div>

    {{-- Mercado Pago --}}
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white overflow-hidden" data-accordion>
        <button type="button" data-accordion-toggle class="w-full flex items-center justify-between p-5 hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                </div>
                <div class="text-left">
                    <h3 class="font-bold text-slate-800">Mercado Pago</h3>
                    <p class="text-sm text-slate-500">Receba pagamentos via Pix, cartão e boleto</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($mercadoPago && $mercadoPago->ativo)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Ativo</span>
                @else
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Inativo</span>
                @endif
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform" data-chevron><path d="m6 9 6 6 6-6"/></svg>
            </div>
        </button>
        <div data-accordion-content class="hidden border-t border-slate-100">
            <form action="{{ route('admin.finance.pagamentos.mercado-pago') }}" method="POST" class="p-5 space-y-5">
                @csrf
                @method('PUT')

                @if(!$mercadoPago || !$mercadoPago->ativo)
                    <div class="rounded-xl bg-red-50 p-4 flex gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600 shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        <div class="text-sm text-red-800">
                            <p class="font-bold">O pagamento online só será disponibilizado para seu catálogo com o tema Card.</p>
                            <p class="mt-1">Ative o tema em Aparência para habilitar essa funcionalidade.</p>
                        </div>
                    </div>
                @endif

                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-bold text-slate-700">Habilitar pagamentos online</p>
                            <p class="text-xs text-slate-500">Quando ativo, os clientes poderão pagar diretamente pelo catálogo</p>
                        </div>
                        <label class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-200 transition-colors has-[:checked]:bg-brand-primary">
                            <input type="checkbox" name="ativo" value="1" {{ ($mercadoPago->ativo ?? false) ? 'checked' : '' }} class="peer sr-only">
                            <span class="inline-block h-4 w-4 translate-x-1 rounded-full bg-white transition-transform peer-checked:translate-x-6"></span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-bold text-slate-700">Ambiente</label>
                    <select name="ambiente" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                        <option value="sandbox" {{ ($mercadoPago->ambiente ?? '') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                        <option value="producao" {{ ($mercadoPago->ambiente ?? '') === 'producao' ? 'selected' : '' }}>Produção (Pagamentos Reais)</option>
                    </select>
                </div>

                <div class="space-y-4">
                    <p class="font-bold text-slate-700">Credenciais do Mercado Pago</p>
                    <p class="text-xs text-slate-500">Obtenha suas credenciais em <a href="https://www.mercadopago.com.br/developers" target="_blank" class="text-brand-primary hover:underline">Painel do Desenvolvedor</a></p>

                    <div class="rounded-xl bg-blue-50 p-3 text-xs text-blue-800 flex gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                        <p>Use as credenciais de <strong>Produção</strong> para receber pagamentos reais. Nunca compartilhe seu Access Token com terceiros.</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-700">Access Token</label>
                        <input type="password" name="access_token" value="{{ data_get($mercadoPago?->credenciais, 'access_token') }}" placeholder="APP_USR-..." class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary font-mono text-sm">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-bold text-slate-700">Public Key</label>
                        <input type="text" name="public_key" value="{{ data_get($mercadoPago?->credenciais, 'public_key') }}" placeholder="APP_USR-..." class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary font-mono text-sm">
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="font-bold text-slate-700">Meios de Pagamento Habilitados</p>
                    @php $config = $mercadoPago?->config_json ?? [] @endphp
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex h-5 w-9 items-center rounded-full bg-slate-200 transition-colors has-[:checked]:bg-brand-primary">
                            <input type="checkbox" name="pix" value="1" {{ ($config['pix'] ?? true) ? 'checked' : '' }} class="peer sr-only">
                            <span class="inline-block h-3 w-3 translate-x-1 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                        </label>
                        <span class="text-sm font-medium text-slate-700">Pix</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex h-5 w-9 items-center rounded-full bg-slate-200 transition-colors has-[:checked]:bg-brand-primary">
                            <input type="checkbox" name="cartao" value="1" {{ ($config['cartao'] ?? true) ? 'checked' : '' }} class="peer sr-only">
                            <span class="inline-block h-3 w-3 translate-x-1 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                        </label>
                        <span class="text-sm font-medium text-slate-700">Cartão de Crédito/Débito</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex h-5 w-9 items-center rounded-full bg-slate-200 transition-colors has-[:checked]:bg-brand-primary">
                            <input type="checkbox" name="boleto" value="1" {{ ($config['boleto'] ?? true) ? 'checked' : '' }} class="peer sr-only">
                            <span class="inline-block h-3 w-3 translate-x-1 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                        </label>
                        <span class="text-sm font-medium text-slate-700">Boleto</span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="rounded-xl bg-brand-primary px-6 py-2.5 font-bold text-white hover:bg-orange-600 transition-colors">Salvar Credenciais</button>
                    <a href="{{ route('admin.finance.pagamentos.mercado-pago.testar') }}" class="rounded-xl border border-slate-200 px-6 py-2.5 font-bold text-slate-700 hover:bg-slate-50 transition-colors inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        Testar Conexão
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Cupons de Desconto --}}
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white overflow-hidden" data-accordion>
        <button type="button" data-accordion-toggle class="w-full flex items-center justify-between p-5 hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-yellow-100 text-yellow-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9.5a2.5 2.5 0 0 1 2.5-2.5h15a2.5 2.5 0 0 1 2.5 2.5v5a2.5 2.5 0 0 1-2.5 2.5h-15A2.5 2.5 0 0 1 2 14.5z"/><path d="M6 7v10"/><path d="M6 12h12"/></svg>
                </div>
                <div class="text-left">
                    <h3 class="font-bold text-slate-800">Cupons de Desconto</h3>
                    <p class="text-sm text-slate-500">Crie cupons promocionais para seus clientes</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">{{ $cuponsAtivos }} cupons ativos</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform" data-chevron><path d="m6 9 6 6 6-6"/></svg>
            </div>
        </button>
        <div data-accordion-content class="hidden border-t border-slate-100">
            <div class="p-5">
                <div class="mb-4 flex justify-end">
                    <button type="button" onclick="document.getElementById('modalCupom').showModal()" class="rounded-xl bg-brand-primary px-4 py-2 font-bold text-white hover:bg-orange-600 transition-colors text-sm">+ Novo Cupom</button>
                </div>

                @if($cupons->count() > 0)
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 font-bold text-slate-700">Código</th>
                                    <th class="px-4 py-3 font-bold text-slate-700">Desconto</th>
                                    <th class="px-4 py-3 font-bold text-slate-700">Validade</th>
                                    <th class="px-4 py-3 font-bold text-slate-700">Usos</th>
                                    <th class="px-4 py-3 font-bold text-slate-700">Status</th>
                                    <th class="px-4 py-3 font-bold text-slate-700 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($cupons as $cupom)
                                    <tr>
                                        <td class="px-4 py-3 font-mono font-bold text-slate-800">{{ $cupom->codigo }}</td>
                                        <td class="px-4 py-3">
                                            @if($cupom->tipo === 'percentual')
                                                {{ $cupom->valor }}%
                                            @else
                                                R$ {{ number_format($cupom->valor, 2, ',', '.') }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">
                                            @if($cupom->data_inicio || $cupom->data_fim)
                                                {{ $cupom->data_inicio?->format('d/m') }} - {{ $cupom->data_fim?->format('d/m') }}
                                            @else
                                                <span class="text-slate-400">Sem limite</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $cupom->quantidade_utilizada }}{{ $cupom->limite_uso ? '/'.$cupom->limite_uso : '' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($cupom->ativo && $cupom->isValid())
                                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">Ativo</span>
                                            @else
                                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <form action="{{ route('admin.sales.cupons.toggle', $cupom) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs font-bold text-brand-primary hover:underline">{{ $cupom->ativo ? 'Desativar' : 'Ativar' }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl bg-slate-50 p-8 text-center">
                        <p class="text-slate-500">Nenhum cupom cadastrado ainda.</p>
                        <p class="text-sm text-slate-400 mt-1">Clique em "Novo Cupom" para criar seu primeiro cupom de desconto.</p>
                    </div>
                @endif

                <div class="mt-4 text-right">
                    <a href="{{ route('admin.sales.cupons.index') }}" class="text-sm font-bold text-brand-primary hover:underline">Ver todos os cupons →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- PIX Direto (PDV / Frente de Caixa) --}}
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white overflow-hidden" data-accordion>
        <button type="button" data-accordion-toggle class="w-full flex items-center justify-between p-5 hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10"/><path d="M16 8h4V4"/><path d="m21 3-5 5"/><path d="M12 12v4"/><path d="M8 12h4"/></svg>
                </div>
                <div class="text-left">
                    <h3 class="font-bold text-slate-800">PIX Direto — PDV / Frente de Caixa</h3>
                    <p class="text-sm text-slate-500">Configure a chave PIX exibida na frente de caixa para recebimentos manuais</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if(!empty($pixConfig['chave']))
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Configurado</span>
                @else
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">Não configurado</span>
                @endif
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform" data-chevron><path d="m6 9 6 6 6-6"/></svg>
            </div>
        </button>
        <div data-accordion-content class="{{ empty($pixConfig['chave']) ? '' : 'hidden' }} border-t border-slate-100">
            <form action="{{ route('admin.finance.pagamentos.pix') }}" method="POST" class="p-5 space-y-5">
                @csrf
                @method('PUT')

                {{-- Aviso informativo --}}
                <div class="rounded-xl bg-emerald-50 p-4 flex gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600 shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    <p class="text-sm text-emerald-800">A chave PIX cadastrada aqui será exibida automaticamente na <strong>frente de caixa (PDV)</strong> durante a finalização de vendas presenciais, permitindo que o cliente faça o pagamento por PIX.</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    {{-- Tipo de chave --}}
                    <div>
                        <label for="pix_tipo" class="mb-1 block text-sm font-bold text-slate-700">Tipo de Chave PIX</label>
                        <select id="pix_tipo" name="pix_tipo" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                            <option value="">Selecione o tipo</option>
                            <option value="cpf"       {{ ($pixConfig['tipo'] ?? '') === 'cpf'       ? 'selected' : '' }}>CPF</option>
                            <option value="cnpj"      {{ ($pixConfig['tipo'] ?? '') === 'cnpj'      ? 'selected' : '' }}>CNPJ</option>
                            <option value="email"     {{ ($pixConfig['tipo'] ?? '') === 'email'     ? 'selected' : '' }}>E-mail</option>
                            <option value="telefone"  {{ ($pixConfig['tipo'] ?? '') === 'telefone'  ? 'selected' : '' }}>Telefone</option>
                            <option value="aleatoria" {{ ($pixConfig['tipo'] ?? '') === 'aleatoria' ? 'selected' : '' }}>Chave Aleatória</option>
                        </select>
                    </div>

                    {{-- Chave PIX --}}
                    <div>
                        <label for="pix_chave" class="mb-1 block text-sm font-bold text-slate-700">Chave PIX</label>
                        <input
                            type="text"
                            id="pix_chave"
                            name="pix_chave"
                            value="{{ $pixConfig['chave'] ?? '' }}"
                            placeholder="email@empresa.com.br, CPF, CNPJ ou chave aleatória"
                            maxlength="150"
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary font-mono text-sm"
                        >
                    </div>

                    {{-- Nome do Beneficiário --}}
                    <div>
                        <label for="pix_beneficiario" class="mb-1 block text-sm font-bold text-slate-700">Nome do Beneficiário (Para QR Code)</label>
                        <input
                            type="text"
                            id="pix_beneficiario"
                            name="pix_beneficiario"
                            value="{{ $pixConfig['beneficiario'] ?? '' }}"
                            placeholder="Ex: Grafica Vapt Vupt"
                            maxlength="25"
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary text-sm"
                        >
                        <p class="mt-1 text-[10px] text-slate-400">Obrigatório p/ QR Code Dinâmico. Máx 25 caracteres.</p>
                    </div>

                    {{-- Cidade --}}
                    <div>
                        <label for="pix_cidade" class="mb-1 block text-sm font-bold text-slate-700">Cidade (Para QR Code)</label>
                        <input
                            type="text"
                            id="pix_cidade"
                            name="pix_cidade"
                            value="{{ $pixConfig['cidade'] ?? '' }}"
                            placeholder="Ex: SAO PAULO"
                            maxlength="15"
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary text-sm"
                        >
                        <p class="mt-1 text-[10px] text-slate-400">Sem acentos, ex: SAO PAULO.</p>
                    </div>
                </div>

                {{-- Chave atual --}}
                @if(!empty($pixConfig['chave']))
                    <div class="rounded-xl bg-slate-50 p-4 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500 shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Chave PIX atual no PDV</p>
                            <p class="font-mono font-bold text-slate-700 text-sm">{{ $pixConfig['chave'] }}</p>
                        </div>
                        @if(!empty($pixConfig['tipo']))
                            <span class="ml-auto rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700 uppercase">{{ $pixConfig['tipo'] }}</span>
                        @endif
                    </div>
                @endif

                <button type="submit" id="btn-salvar-pix" class="rounded-xl bg-emerald-600 px-6 py-2.5 font-bold text-white hover:bg-emerald-700 transition-colors">
                    Salvar Configurações PIX
                </button>
            </form>
        </div>
    </div>

    {{-- Outras Integrações --}}

    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden" data-accordion>
        <button type="button" data-accordion-toggle class="w-full flex items-center justify-between p-5 hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.5 3 1.5 3-3-1.5L7.5 9 9 6 6 7.5 3 6l3 1.5L4.5 12 6 15 3 13.5 6 12l-3-1.5L6 9 4.5 6 7.5 7.5 9 4.5l-3 1.5 3-3 1.5 3L12 3Z"/><path d="m12 21 1.5-3-1.5-3 3 1.5 1.5-1.5-1.5 3 3 1.5-3-1.5 1.5 3 1.5-3-1.5 1.5 3 3 1.5-3-1.5L19.5 15 18 12l3 1.5-3-1.5 3 3-1.5-1.5L15 16.5 13.5 19.5l3-1.5-3 3-1.5-3Z"/></svg>
                </div>
                <div class="text-left">
                    <h3 class="font-bold text-slate-800">Outras Integrações</h3>
                    <p class="text-sm text-slate-500">Mais opções de pagamento em breve</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Em breve</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform" data-chevron><path d="m6 9 6 6 6-6"/></svg>
            </div>
        </button>
        <div data-accordion-content class="hidden border-t border-slate-100">
            <div class="p-5 grid sm:grid-cols-2 gap-4">
                @foreach([
                    ['PayPal', 'Pagamentos internacionais', 'paypal'],
                    ['Stripe', 'Cartões e assinaturas', 'stripe'],
                    ['Asaas', 'Boleto e PIX', 'asaas'],
                    ['PIX Direto', 'Chave PIX manual', 'pix'],
                ] as [$nome, $desc, $icone])
                    <div class="flex items-center gap-4 rounded-xl border border-slate-200 p-4 opacity-60">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-600">{{ $nome }}</p>
                            <p class="text-xs text-slate-400">{{ $desc }}</p>
                        </div>
                        <span class="ml-auto rounded-full bg-slate-200 px-2 py-0.5 text-xs font-bold text-slate-500">Em breve</span>
                    </div>
                @endforeach
            </div>
            <div class="px-5 pb-5 text-center">
                <p class="text-sm text-slate-400">Tem sugestões de integrações? Entre em contato com nosso suporte.</p>
            </div>
        </div>
    </div>

    {{-- Modal Novo Cupom --}}
    <dialog id="modalCupom" class="rounded-2xl border-none p-0 backdrop:bg-slate-900/60 shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-gradient-to-r from-brand-secondary to-slate-700 p-4 flex items-center justify-between">
            <h3 class="font-bold text-white">Novo Cupom</h3>
            <button type="button" onclick="document.getElementById('modalCupom').close()" class="text-white/60 hover:text-white">✕</button>
        </div>
        <form action="{{ route('admin.sales.cupons.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">Código do Cupom *</label>
                <input type="text" name="codigo" required placeholder="Ex: PROMO10" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 uppercase focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                <p class="mt-1 text-xs text-slate-500">Sem espaços, será convertido para maiúsculas</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-bold text-slate-700">Tipo de Desconto *</label>
                    <select name="tipo" required class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                        <option value="percentual">% Percentual (%)</option>
                        <option value="fixo">R$ Valor Fixo</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-bold text-slate-700">Valor do Desconto *</label>
                    <input type="number" name="valor" step="0.01" min="0" required class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">Valor Mínimo do Pedido</label>
                <input type="number" name="valor_minimo_pedido" step="0.01" min="0" placeholder="0,00" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-bold text-slate-700">Limite de Uso</label>
                    <input type="number" name="limite_uso" min="1" placeholder="Ilimitado" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-bold text-slate-700">Ativo</label>
                    <label class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-200 transition-colors has-[:checked]:bg-brand-primary">
                        <input type="checkbox" name="ativo" value="1" checked class="peer sr-only">
                        <span class="inline-block h-4 w-4 translate-x-1 rounded-full bg-white transition-transform peer-checked:translate-x-6"></span>
                    </label>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-bold text-slate-700">Data Início</label>
                    <input type="datetime-local" name="data_inicio" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-bold text-slate-700">Data Fim</label>
                    <input type="datetime-local" name="data_fim" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 text-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalCupom').close()" class="flex-1 rounded-xl border border-slate-200 py-2.5 font-bold text-slate-700 hover:bg-slate-50">Cancelar</button>
                <button type="submit" class="flex-1 rounded-xl bg-brand-primary py-2.5 font-bold text-white hover:bg-orange-600">Criar Cupom</button>
            </div>
        </form>
    </dialog>

    <script>
        document.querySelectorAll('[data-accordion-toggle]').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const content = toggle.nextElementSibling;
                const chevron = toggle.querySelector('[data-chevron]');
                content.classList.toggle('hidden');
                if (chevron) {
                    chevron.style.transform = content.classList.contains('hidden') ? '' : 'rotate(180deg)';
                }
            });
        });
    </script>
</x-layouts.app>
