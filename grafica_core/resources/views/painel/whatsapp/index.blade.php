<x-layouts.app titulo="WhatsApp por Loja - VaptCRM">
    @php
        $mappingData = $settings->event_mappings ?? [];
        $automationFlags = $settings->automations ?? [];
    @endphp

    <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-800 p-8 text-white mb-8 shadow-xl">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight">Conecte seu WhatsApp e automatize mensagens para seus clientes.</h1>
                <p class="mt-2 text-white/80 font-medium">Ative mensagens automáticas, teste em segundos e acompanhe suas conversas em um só lugar.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="#connection-form" class="px-5 py-3 rounded-2xl bg-emerald-500 text-white font-black shadow-sm hover:bg-emerald-400 transition-colors">Conectar WhatsApp</a>
                <a href="#automations" class="px-5 py-3 rounded-2xl bg-white text-slate-900 font-black shadow-sm hover:bg-slate-100 transition-colors">Configurar automações</a>
                <a href="{{ route('admin.whatsapp.page.inbox') }}" class="px-5 py-3 rounded-2xl border border-white/30 text-white font-black hover:bg-white/10 transition-colors">Abrir conversas</a>
                <a href="{{ route('admin.whatsapp.campaigns.index') }}" class="px-5 py-3 rounded-2xl border border-white/30 text-white font-black hover:bg-white/10 transition-colors">Campanhas</a>
                <a href="{{ route('admin.whatsapp.dashboard') }}" class="px-5 py-3 rounded-2xl border border-white/30 text-white font-black hover:bg-white/10 transition-colors">Dashboard</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-black uppercase tracking-wide text-slate-400">Mensagens no mês</p>
            <p class="text-3xl font-black text-slate-800 mt-1">{{ $messagesUsed }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-black uppercase tracking-wide text-slate-400">Mensagens restantes</p>
            <p class="text-3xl font-black text-slate-800 mt-1">{{ $monthlyRemaining === null ? '∞' : $monthlyRemaining }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-black uppercase tracking-wide text-slate-400">Conversas abertas</p>
            <p class="text-3xl font-black text-slate-800 mt-1">{{ $openConversations }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-black uppercase tracking-wide text-slate-400">Taxa de resposta</p>
            <p class="text-3xl font-black text-slate-800 mt-1">Em breve</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 mb-8">
        <div class="flex items-center justify-between gap-4 mb-3">
            <h2 class="text-xl font-black text-slate-800">Uso do plano</h2>
            <span class="text-sm font-bold text-slate-500">{{ $messageLimit === null ? 'Ilimitado' : ($messagesUsed . '/' . $messageLimit) }}</span>
        </div>
        <div class="w-full h-3 rounded-full bg-slate-100 overflow-hidden">
            <div class="h-3 rounded-full {{ $isNearLimit ? 'bg-amber-500' : 'bg-emerald-500' }}" style="width: {{ $usagePercent }}%"></div>
        </div>
        @if($isNearLimit)
            <p class="mt-3 text-sm font-bold text-amber-700">Você está próximo do limite de mensagens do plano.</p>
        @endif
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 mb-8">
        <h2 class="text-xl font-black text-slate-800 mb-4">Onboarding guiado</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($progressSteps as $step)
                <div class="rounded-2xl border p-4 {{ $step['done'] ? 'border-emerald-200 bg-emerald-50/60' : 'border-slate-200 bg-slate-50/70' }}">
                    <p class="text-sm font-black {{ $step['done'] ? 'text-emerald-700' : 'text-slate-500' }}">{{ $step['done'] ? '[✔]' : '[ ]' }} {{ $step['title'] }}</p>
                    <p class="text-xs text-slate-500 mt-2">{{ $step['description'] }}</p>
                    @if(str_starts_with($step['anchor'], '#'))
                        <a href="{{ $step['anchor'] }}" class="inline-block mt-3 text-xs font-black text-brand-primary hover:underline">{{ $step['cta'] }}</a>
                    @else
                        <a href="{{ $step['anchor'] }}" class="inline-block mt-3 text-xs font-black text-brand-primary hover:underline">{{ $step['cta'] }}</a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <div class="xl:col-span-2 space-y-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-slate-800">Conexão do WhatsApp</h2>
                        <p class="text-sm text-slate-500">Veja rapidamente o número conectado, status da conta e qualidade.</p>
                    </div>
                    <a href="#connection-form" class="px-4 py-2 rounded-xl bg-brand-primary text-white text-sm font-black shadow-sm hover:opacity-90">Reconectar</a>
                </div>
                <div class="p-8 space-y-4">
                    @forelse($accounts as $account)
                        @php
                            $statusText = match($account->status) {
                                'active' => 'Conectado',
                                'pending' => 'Pendente',
                                'suspended', 'banned', 'disconnected' => 'Com erro',
                                default => ucfirst((string) $account->status),
                            };
                            $statusClass = match($account->status) {
                                'active' => 'bg-emerald-100 text-emerald-700',
                                'pending' => 'bg-amber-100 text-amber-700',
                                default => 'bg-rose-100 text-rose-700',
                            };
                        @endphp
                        <div class="rounded-2xl border border-slate-200 p-5 bg-slate-50/70">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-lg font-black text-slate-800">{{ $account->display_name ?: 'Número sem nome' }}</h3>
                                        @if($account->is_primary)
                                            <span class="px-2.5 py-1 rounded-full bg-brand-primary/10 text-brand-primary text-[11px] font-black uppercase tracking-wide">Principal</span>
                                        @endif
                                        <span class="px-2.5 py-1 rounded-full {{ $statusClass }} text-[11px] font-black uppercase tracking-wide">{{ $statusText }}</span>
                                    </div>
                                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                                        <div>
                                            <p class="text-slate-400 font-black uppercase text-[10px]">Número conectado</p>
                                            <p class="font-bold text-slate-700">{{ $account->phone_number ?: 'Não informado' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-400 font-black uppercase text-[10px]">Provider</p>
                                            <p class="font-bold text-slate-700">{{ $account->provider }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-400 font-black uppercase text-[10px]">Qualidade</p>
                                            <p class="font-bold text-slate-700">{{ $account->quality_rating ?: 'Sem dados' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-400 font-black uppercase text-[10px]">Templates prontos</p>
                                            <p class="font-bold text-slate-700">{{ $account->templates->where('status', 'approved')->count() }}</p>
                                        </div>
                                    </div>
                                </div>

                                <form action="{{ route('admin.whatsapp.accounts.sync-ui', $account) }}" method="POST" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="px-4 py-3 rounded-2xl border border-slate-200 bg-white font-black text-slate-700 hover:border-brand-primary transition-colors w-full lg:w-auto">
                                        Sincronizar templates
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center bg-slate-50/70">
                            <p class="text-lg font-black text-slate-700">Você ainda não conectou seu WhatsApp</p>
                            <p class="text-sm text-slate-500 mt-2">Conecte agora para começar a automatizar mensagens e abrir conversas.</p>
                            <a href="#connection-form" class="inline-flex mt-4 px-5 py-3 rounded-2xl bg-brand-primary text-white font-black shadow-sm hover:opacity-90">Conectar agora</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <form id="automations" action="{{ route('admin.whatsapp.settings.update') }}" method="POST" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                @csrf
                <div class="px-8 py-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-800">Automações por evento</h2>
                    <p class="text-sm text-slate-500 mt-1">Escolha o que enviar automaticamente para seu cliente em cada etapa do pedido.</p>
                </div>

                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-black text-slate-700 mb-2">Modo de envio</label>
                            <select name="send_mode" class="w-full rounded-xl border-slate-200 focus:border-brand-primary font-bold">
                                @foreach($sendModeOptions as $modeKey => $modeLabel)
                                    <option value="{{ $modeKey }}" @selected(($settings->send_mode ?? \App\Models\WhatsApp\WhatsAppStoreSetting::SEND_MODE_API) === $modeKey)>
                                        {{ $modeLabel }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 mt-2">
                                @if($effectiveSendMode === \App\Models\WhatsApp\WhatsAppStoreSetting::SEND_MODE_MANUAL)
                                    Modo efetivo atual: Manual por link (wa.me).
                                @else
                                    Modo efetivo atual: Automático via API oficial.
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-slate-700 mb-2">Conta padrão da loja <span title="Conta usada por padrão para automações.">ⓘ</span></label>
                            <select name="default_account_id" class="w-full rounded-xl border-slate-200 focus:border-brand-primary font-bold">
                                <option value="">Selecionar automaticamente</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" @selected((string) $settings->default_account_id === (string) $account->id)>
                                        {{ $account->display_name ?: $account->phone_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-slate-700 mb-2">Link do catálogo</label>
                            <input type="url" name="catalog_link" value="{{ old('catalog_link', $settings->catalog_link) }}" class="w-full rounded-xl border-slate-200 focus:border-brand-primary" placeholder="https://.../catalogo">
                        </div>
                    </div>

                    <div class="space-y-6">
                        @foreach($eventOptions as $eventKey => $eventLabel)
                            @php
                                $eventMapping = $mappingData[$eventKey] ?? [];
                                $selectedTemplateId = $eventMapping['template_id'] ?? null;
                                $selectedVariables = $eventMapping['variables'] ?? [];
                            @endphp
                            <div class="rounded-2xl border border-slate-200 p-6">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-5">
                                    <div>
                                        <h3 class="text-lg font-black text-slate-800">Quando {{ strtolower($eventLabel) }}</h3>
                                        <p class="text-sm text-slate-500">Escolha o template e as variáveis que entram automaticamente nessa mensagem.</p>
                                    </div>
                                    <label class="inline-flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-2 bg-slate-50">
                                        <input type="checkbox" name="automations[{{ $eventKey }}]" value="1" class="rounded border-slate-300 text-brand-primary focus:ring-brand-primary" @checked($automationFlags[$eventKey] ?? false)>
                                        <span class="text-sm font-black text-slate-700">Enviar mensagem automaticamente</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-black text-slate-700 mb-2">Mensagem</label>
                                        <select name="mappings[{{ $eventKey }}][template_id]" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                                            <option value="">Selecionar template</option>
                                            @foreach($accounts as $account)
                                                @php $approvedTemplates = $account->templates->where('status', 'approved'); @endphp
                                                @if($approvedTemplates->isNotEmpty())
                                                    <optgroup label="{{ $account->display_name ?: $account->phone_number }}">
                                                        @foreach($approvedTemplates as $template)
                                                            <option value="{{ $template->id }}" @selected((string) $selectedTemplateId === (string) $template->id)>
                                                                {{ $template->name }} ({{ $template->language }})
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-black text-slate-700 mb-2">Variáveis da mensagem</label>
                                        <div class="flex flex-wrap gap-2 mb-3">
                                            @foreach($variableOptions as $variableKey => $variableLabel)
                                                <button type="button" onclick="addVariable('{{ $eventKey }}', '{{ $variableKey }}')" class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">{{ $variableLabel }}</button>
                                            @endforeach
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="vars-{{ $eventKey }}">
                                            @for($index = 0; $index < 5; $index++)
                                                <select name="mappings[{{ $eventKey }}][variables][]" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                                                    <option value="">Selecionar variável</option>
                                                    @foreach($variableOptions as $variableKey => $variableLabel)
                                                        <option value="{{ $variableKey }}" @selected(($selectedVariables[$index] ?? null) === $variableKey)>{{ $variableLabel }}</option>
                                                    @endforeach
                                                </select>
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 bg-slate-50 border border-slate-200 rounded-2xl p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-black text-slate-700">Pré-visualização</p>
                                        <button type="button" onclick="togglePreview('preview-{{ $eventKey }}')" class="text-xs font-black text-brand-primary hover:underline">Visualizar mensagem</button>
                                    </div>
                                    <div id="preview-{{ $eventKey }}" class="hidden mt-3 rounded-xl bg-white border border-slate-200 p-4 text-sm text-slate-700 leading-relaxed">
                                        {{ $eventPreviews[$eventKey] ?? 'Preview indisponível.' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-8 py-4 rounded-2xl bg-brand-primary text-white font-black shadow-sm hover:opacity-90">Salvar automações</button>
                </div>
            </form>
        </div>

        <div class="space-y-8">
            <div id="connection-form" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <h2 class="text-xl font-black text-slate-800 mb-2">Passo 1: Conectar WhatsApp</h2>
                <p class="text-sm text-slate-500 mb-6">Preencha os dados oficiais da Meta. Esse processo costuma levar cerca de 2 minutos.</p>
                <form action="{{ route('admin.whatsapp.connect') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Nome exibido do número</label>
                        <input type="text" name="display_name" value="{{ old('display_name') }}" class="w-full rounded-xl border-slate-200 focus:border-brand-primary" title="Nome que sua equipe identifica no painel.">
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Número</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" class="w-full rounded-xl border-slate-200 focus:border-brand-primary" placeholder="+5511999999999" title="Use DDI e DDD.">
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Phone Number ID</label>
                        <input type="text" name="phone_number_id" value="{{ old('phone_number_id') }}" class="w-full rounded-xl border-slate-200 focus:border-brand-primary" title="Identificador técnico da Meta.">
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">WABA ID</label>
                        <input type="text" name="waba_id" value="{{ old('waba_id') }}" class="w-full rounded-xl border-slate-200 focus:border-brand-primary" title="ID da conta WhatsApp Business.">
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Business ID (opcional)</label>
                        <input type="text" name="business_id" value="{{ old('business_id') }}" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Access token oficial</label>
                        <textarea name="access_token" rows="4" class="w-full rounded-xl border-slate-200 focus:border-brand-primary" placeholder="Token oficial da Meta">{{ old('access_token') }}</textarea>
                        <p class="text-xs text-slate-400 mt-2">Seu token é criptografado e nunca aparece para outros usuários.</p>
                    </div>
                    <button type="submit" class="w-full py-4 rounded-2xl bg-slate-900 text-white font-black hover:bg-black transition-colors">Conectar WhatsApp</button>
                </form>
            </div>

            <div id="test-send" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <h2 class="text-xl font-black text-slate-800 mb-2">Passo 3: Testar envio</h2>
                <p class="text-sm text-slate-500 mb-6">Valide sua configuração com uma mensagem teste antes de ativar o uso diário.</p>
                <form action="{{ route('admin.whatsapp.settings.test-send') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Conta</label>
                        <select name="account_id" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->display_name ?: $account->phone_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Template</label>
                        <select name="template_id" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                            @foreach($accounts as $account)
                                @foreach($account->templates->where('status', 'approved') as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }} - {{ $account->display_name ?: $account->phone_number }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Evento (opcional)</label>
                        <select name="event_key" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                            <option value="">Sem evento</option>
                            @foreach($eventOptions as $eventKey => $eventLabel)
                                <option value="{{ $eventKey }}">{{ $eventLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Número para teste</label>
                        <input type="text" name="phone" value="{{ old('phone', $settings->last_test_phone) }}" class="w-full rounded-xl border-slate-200 focus:border-brand-primary" placeholder="+5511999999999">
                    </div>
                    <label class="flex items-start gap-3 text-sm text-slate-600">
                        <input type="checkbox" name="use_real_data" value="1" class="mt-1 rounded border-slate-300 text-brand-primary focus:ring-brand-primary">
                        <span class="font-medium">Usar dados reais de pedido nesta simulação.</span>
                    </label>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-1">Pedido real (opcional)</label>
                        <select name="order_id" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                            <option value="">Selecionar pedido</option>
                            @foreach($recentOrders as $order)
                                <option value="{{ $order->id }}">{{ $order->numero_exibicao }} - {{ $order->cliente?->nome ?: 'Cliente' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-start gap-3 text-sm text-slate-600">
                        <input type="checkbox" name="confirm_optin" value="1" class="mt-1 rounded border-slate-300 text-brand-primary focus:ring-brand-primary">
                        <span class="font-medium">Confirmo que este número autorizou o recebimento (opt-in).</span>
                    </label>
                    <button type="submit" class="w-full py-4 rounded-2xl bg-emerald-500 text-white font-black hover:bg-emerald-600 transition-colors">Enviar mensagem teste</button>
                </form>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <h2 class="text-xl font-black text-slate-800 mb-4">Ações rápidas</h2>
                <div class="space-y-3">
                    <a href="{{ route('admin.whatsapp.page.inbox') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 font-bold hover:border-brand-primary">
                        <span>📩 Abrir conversas</span><span>→</span>
                    </a>
                    <a href="{{ route('admin.whatsapp.campaigns.index') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 font-bold hover:border-brand-primary">
                        <span>📣 Campanhas</span><span>→</span>
                    </a>
                    <a href="{{ route('admin.whatsapp.dashboard') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 font-bold hover:border-brand-primary">
                        <span>📊 Dashboard de métricas</span><span>→</span>
                    </a>
                    <a href="{{ route('admin.whatsapp.logs') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 font-bold hover:border-brand-primary">
                        <span>🗂 Ver histórico de envios</span><span>→</span>
                    </a>
                </div>
            </div>

            {{-- AI Preparação --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-black text-slate-800">IA — Preparação</h2>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-violet-100 text-violet-700">Em breve</span>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/60 px-5 py-4 opacity-70 cursor-not-allowed">
                        <div>
                            <p class="font-black text-slate-700">Sugestões de resposta por IA</p>
                            <p class="text-xs text-slate-400 mt-0.5">IA sugere respostas baseadas no histórico de cada conversa.</p>
                        </div>
                        <input type="checkbox" disabled class="rounded border-slate-300 cursor-not-allowed">
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/60 px-5 py-4 opacity-70 cursor-not-allowed">
                        <div>
                            <p class="font-black text-slate-700">Classificação automática de intenção</p>
                            <p class="text-xs text-slate-400 mt-0.5">Categoriza mensagens recebidas automaticamente (compra, suporte, etc.).</p>
                        </div>
                        <input type="checkbox" disabled class="rounded border-slate-300 cursor-not-allowed">
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-emerald-50 px-5 py-4">
                        <div>
                            <p class="font-black text-slate-700">Handoff humano obrigatório</p>
                            <p class="text-xs text-slate-400 mt-0.5">A IA nunca fecha conversas sem aprovação humana.</p>
                        </div>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full">Sempre ativo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePreview(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.toggle('hidden');
        }

        function addVariable(eventKey, variableKey) {
            const container = document.getElementById('vars-' + eventKey);
            if (!container) return;

            const selects = container.querySelectorAll('select');
            for (const select of selects) {
                if (select.value === '') {
                    select.value = variableKey;
                    return;
                }
            }
        }
    </script>
</x-layouts.app>