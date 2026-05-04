{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-22 02:00 -03:00
--}}
@php
    $gruposVariacoes = $produto->variacoes->groupBy('tipo_variacao');
    $breadcrumbItems = [
        ['label' => 'Início', 'url' => \App\Support\PublicUrlHelper::inicio()],
        ['label' => 'Catálogo', 'url' => \App\Support\PublicUrlHelper::catalogo()],
    ];

    if ($produto->categoriaRel) {
        $breadcrumbItems[] = ['label' => $produto->categoriaRel->nome, 'url' => \App\Support\PublicUrlHelper::categoria($produto->categoriaRel)];
    }

    $breadcrumbItems[] = ['label' => $produto->nome];
@endphp
<x-layouts.publico>
    <x-public.breadcrumb :items="$breadcrumbItems" />
    @if(session('sucesso_checkout'))
        <div class="mb-8 rounded-2xl bg-status-success/10 border border-status-success/20 p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="text-4xl">🎉</div>
                <div>
                    <h2 class="text-xl font-bold text-status-success">Pedido Registrado!</h2>
                    <p class="mt-1 text-slate-700 font-medium">{{ session('sucesso_checkout') }}</p>
                    <p class="mt-3 text-sm text-slate-500">Nossa equipe comercial vai receber sua notificação imediatamente e entrará em contato para formalizar as artes finais e recolher o pagamento se necessário.</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('erro'))
        <div class="mb-8 rounded-2xl bg-red-50 border border-red-200 p-6 shadow-sm">
            <h2 class="text-xl font-bold text-red-600">Falha ao Solicitar</h2>
            <p class="mt-1 text-slate-700">{{ session('erro') }}</p>
        </div>
    @endif

    <div class="product-mobile-grid grid items-start gap-6 lg:grid-cols-12 lg:gap-12">
        
        <!-- COLUNA ESQUERDA: Galeria e Info --Substancial -->
        <div class="order-2 space-y-6 lg:order-1 lg:col-span-7 lg:space-y-8">
            
            {{-- Galeria de Imagens --}}
            <div class="product-gallery public-card overflow-hidden rounded-2xl p-2 sm:p-4 lg:rounded-[2.5rem] group">
                <div class="product-hero-image w-full bg-slate-50 overflow-hidden rounded-xl sm:rounded-2xl lg:rounded-[2rem] relative">
                    @if($produto->imagem_principal)
                        <img id="main-product-image" src="{{ asset('storage/' . $produto->imagem_principal) }}" alt="{{ $produto->nome }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-8xl text-slate-200">📦</div>
                    @endif

                    @if($produto->badge_comercial)
                        <div class="absolute left-4 top-4 sm:left-6 sm:top-6">
                            <span class="bg-brand-primary text-white text-[10px] sm:text-xs font-black px-3 py-1.5 rounded-full uppercase tracking-widest shadow-xl">
                                {{ $produto->badge_comercial }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Miniaturas/Thumnails --}}
                @if($produto->imagens->count() > 0)
                    <div class="gallery-thumbnails mt-3 flex gap-2 overflow-x-auto px-1 pb-2 scrollbar-hide sm:mt-6 sm:gap-4 sm:px-2">
                        <button onclick="changeImage('{{ asset('storage/' . $produto->imagem_principal) }}', this)" class="w-14 h-14 sm:w-20 sm:h-20 shrink-0 rounded-xl sm:rounded-2xl border-2 border-brand-primary overflow-hidden transition-all shadow-sm">
                            <img src="{{ asset('storage/' . $produto->imagem_principal) }}" class="w-full h-full object-cover">
                        </button>
                        @foreach($produto->imagens as $img)
                            <button onclick="changeImage('{{ asset('storage/' . $img->caminho) }}', this)" class="w-14 h-14 sm:w-20 sm:h-20 shrink-0 rounded-xl sm:rounded-2xl border-2 border-transparent hover:border-slate-300 overflow-hidden transition-all grayscale hover:grayscale-0">
                                <img src="{{ asset('storage/' . $img->caminho) }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Detalhes Acadêmicos / Descrição --}}
            <div class="product-description public-card rounded-xl sm:rounded-2xl p-4 sm:p-7 md:p-10 lg:rounded-[2.5rem]">
                <div class="desc-header mb-4 flex items-center gap-2 sm:mb-8 sm:gap-4">
                    <span class="w-1.5 h-6 sm:h-8 bg-brand-primary rounded-full"></span>
                    <h2 class="text-base sm:text-2xl font-black text-slate-800 tracking-tight">Descrição do Produto</h2>
                </div>
                
                <div class="prose prose-slate max-w-none prose-p:text-slate-500 prose-p:leading-relaxed prose-strong:text-slate-800">
                    {!! nl2br(e($produto->descricao_completa ?: $produto->descricao_curta)) !!}
                </div>

                @if($produto->prazo_estimado)
                    <div class="desc-info-cards mt-5 grid grid-cols-1 gap-3 sm:mt-10 md:grid-cols-2 sm:gap-6">
                        <div class="desc-info-card flex items-center gap-3 p-3 sm:p-5 rounded-xl sm:rounded-3xl bg-slate-50 border border-slate-100/50">
                            <div class="info-icon w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-white flex items-center justify-center text-lg sm:text-2xl shadow-sm">🕒</div>
                            <div>
                                <p class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-0.5 sm:mb-1">Produção</p>
                                <p class="text-xs sm:text-sm font-bold text-slate-700 italic">{{ $produto->prazo_estimado }}</p>
                            </div>
                        </div>
                        <div class="desc-info-card flex items-center gap-3 p-3 sm:p-5 rounded-xl sm:rounded-3xl bg-slate-50 border border-slate-100/50">
                            <div class="info-icon w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-white flex items-center justify-center text-lg sm:text-2xl shadow-sm">📦</div>
                            <div>
                                <p class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-0.5 sm:mb-1">Embalagem</p>
                                <p class="text-xs sm:text-sm font-bold text-slate-700 italic">Proteção Reforçada</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Selos de Confiança / Roadmap Visual --}}
            <div class="trust-seals-grid grid grid-cols-3 gap-2 sm:gap-4">
                <div class="text-center p-2.5 sm:p-6 bg-slate-50 rounded-xl sm:rounded-[2rem] border border-slate-100/50 grayscale-0 sm:grayscale opacity-100 sm:opacity-60 hover:grayscale-0 hover:opacity-100 transition-all cursor-default">
                    <x-icon name="shield-check" class="w-5 h-5 sm:w-8 sm:h-8 mx-auto mb-1.5 sm:mb-3 text-brand-secondary" />
                    <p class="text-[8px] sm:text-[10px] font-black uppercase text-slate-800 leading-tight">Compra Segura</p>
                </div>
                <div class="text-center p-2.5 sm:p-6 bg-slate-50 rounded-xl sm:rounded-[2rem] border border-slate-100/50 grayscale-0 sm:grayscale opacity-100 sm:opacity-60 hover:grayscale-0 hover:opacity-100 transition-all cursor-default">
                    <x-icon name="check-badge" class="w-5 h-5 sm:w-8 sm:h-8 mx-auto mb-1.5 sm:mb-3 text-brand-secondary" />
                    <p class="text-[8px] sm:text-[10px] font-black uppercase text-slate-800 leading-tight">Premium</p>
                </div>
                <div class="text-center p-2.5 sm:p-6 bg-slate-50 rounded-xl sm:rounded-[2rem] border border-slate-100/50 grayscale-0 sm:grayscale opacity-100 sm:opacity-60 hover:grayscale-0 hover:opacity-100 transition-all cursor-default">
                    <x-icon name="chat-bubble-left-right" class="w-5 h-5 sm:w-8 sm:h-8 mx-auto mb-1.5 sm:mb-3 text-brand-secondary" />
                    <p class="text-[8px] sm:text-[10px] font-black uppercase text-slate-800 leading-tight">Suporte</p>
                </div>
            </div>
        </div>

        <!-- COLUNA DIREIT: Comercial e Personalização -->
        <aside class="order-1 lg:order-2 lg:col-span-5 lg:sticky lg:top-24">
            <div class="product-configurator relative overflow-hidden rounded-xl bg-slate-900 p-4 text-white shadow-2xl sm:p-7 md:p-8 lg:rounded-[3rem] lg:p-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-brand-primary/10 rounded-full blur-3xl -mr-32 -mt-32"></div>

                {{-- Header Comercial --}}
                <div class="config-header relative z-10 mb-4 border-b border-white/10 pb-4 sm:mb-8 sm:pb-8">
                    <div class="flex items-center gap-2 sm:gap-3 mb-2 sm:mb-4">
                        <span class="bg-brand-primary h-px flex-1"></span>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] sm:tracking-[0.3em] text-brand-primary">Configurador</span>
                        <span class="bg-brand-primary h-px flex-1"></span>
                    </div>
                    <h1 class="text-lg font-black tracking-tight mb-1 sm:text-3xl sm:mb-2 md:text-4xl">{{ $produto->nome }}</h1>
                    <p class="mb-3 text-xs sm:text-sm font-medium italic text-white/70">{{ $produto->descricao_curta }}</p>

                    <div class="mb-3 sm:mb-5 grid grid-cols-1 gap-1.5 sm:gap-2.5 text-[10px] sm:text-xs font-bold text-white/80 sm:grid-cols-2">
                        @if($produto->prazo_estimado)
                            <div class="config-info-badge rounded-lg sm:rounded-xl border border-white/10 bg-white/5 px-2.5 py-1.5 sm:px-3 sm:py-2">Prazo: {{ $produto->prazo_estimado }}</div>
                        @endif
                        <div class="config-info-badge rounded-lg sm:rounded-xl border border-white/10 bg-white/5 px-2.5 py-1.5 sm:px-3 sm:py-2">Aprovação final humana</div>
                    </div>

                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-[9px] sm:text-[10px] font-black text-white/40 uppercase tracking-widest mb-0.5 sm:mb-1">Preço Estimado</p>
                            <h3 class="config-price text-2xl sm:text-5xl font-black text-brand-primary tracking-tighter" id="preco-final-display">
                                R$ {{ number_format($produto->preco_base, 2, ',', '.') }}
                            </h3>
                        </div>
                        <div class="text-right pb-0.5 sm:pb-1">
                            @if($produto->unidade_venda)
                                <span class="bg-white/10 text-white/70 text-[9px] sm:text-[10px] font-black px-2 sm:px-3 py-0.5 sm:py-1 rounded-lg uppercase tracking-wider">
                                    {{ $produto->unidade_venda }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Formulário de Adicionar ao Carrinho --}}
                <form id="form-produto" class="config-form relative z-10 flex flex-col gap-3 sm:gap-6">
                    @csrf
                    <input type="hidden" name="produto_id" value="{{ $produto->id }}">
                    
                    {{-- Quantidade --}}
                    <div class="config-block rounded-xl sm:rounded-2xl border border-white/5 bg-white/5 p-3 sm:p-4 sm:rounded-[2rem]">
                        <label class="config-label block mb-1.5 sm:mb-3 text-[9px] sm:text-[10px] font-black text-white/40 uppercase tracking-widest ml-1 sm:ml-2">Quantidade</label>
                        <div class="flex items-center gap-3 sm:gap-4">
                            <button type="button" onclick="adjustQty(-1)" class="public-touch w-10 h-10 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-white/10 flex items-center justify-center text-lg sm:text-xl font-black hover:bg-brand-primary transition-colors">-</button>
                            <input type="number" name="quantidade" id="input-quantidade" value="1" min="1" required 
                                   class="flex-1 bg-transparent border-none text-center text-xl sm:text-2xl font-black text-white focus:ring-0 p-0">
                            <button type="button" onclick="adjustQty(1)" class="public-touch w-10 h-10 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-white/10 flex items-center justify-center text-lg sm:text-xl font-black hover:bg-brand-primary transition-colors">+</button>
                        </div>
                    </div>

                    {{-- Variações Dinâmicas --}}
                    @if($gruposVariacoes->isNotEmpty())
                        <div class="space-y-4 sm:space-y-6 pt-1 sm:pt-2">
                            @foreach($gruposVariacoes as $tipo => $opcoes)
                                <div>
                                    <label class="config-label block mb-1.5 sm:mb-3 text-[9px] sm:text-[10px] font-black text-white/40 uppercase tracking-widest ml-1 sm:ml-2">{{ $tipo }}</label>
                                    <div class="relative">
                                        <select name="variacoes[{{ $tipo }}]" 
                                                class="config-select select-variacao w-full appearance-none rounded-xl sm:rounded-2xl border-none bg-white/5 px-4 py-2.5 sm:px-6 sm:py-4 text-xs sm:text-sm font-bold text-white focus:ring-2 focus:ring-brand-primary transition-all cursor-pointer">
                                            <option value="" data-preco="0">Padrão / Selecione</option>
                                            @foreach($opcoes as $opcao)
                                                <option value="{{ $opcao->nome_opcao }}" data-preco="{{ $opcao->acrescimo_venda }}">
                                                    {{ $opcao->nome_opcao }} 
                                                    @if($opcao->acrescimo_venda > 0)
                                                        (+ R$ {{ number_format($opcao->acrescimo_venda, 2, ',', '.') }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-icon name="chevron-down" class="absolute right-6 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30 pointer-events-none" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Bloco de Arte --}}
                    @if($produto->exige_arte)
                        <div class="config-arte-block p-4 sm:p-6 bg-brand-primary/10 rounded-xl sm:rounded-[2.5rem] border border-brand-primary/20 space-y-3 sm:space-y-4">
                            <h4 class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-brand-primary flex items-center gap-2">
                                <x-icon name="photo" class="w-3.5 h-3.5 sm:w-4 sm:h-4" /> Design e Arte
                            </h4>
                            
                            <div class="grid grid-cols-1 gap-2 sm:gap-3">
                                <label class="config-arte-option flex items-center gap-2.5 sm:gap-3 p-3 sm:p-4 bg-white/5 rounded-xl sm:rounded-2xl border border-transparent hover:border-brand-primary cursor-pointer transition-all group">
                                    <input type="radio" name="tipo_arte" value="enviar" checked class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-brand-primary focus:ring-0 border-white/20 bg-transparent" onchange="toggleArteUpload()">
                                    <div class="flex-1">
                                        <p class="text-xs sm:text-sm font-bold text-white leading-none mb-0.5 sm:mb-1">Enviar meu arquivo</p>
                                        <p class="text-[9px] sm:text-[10px] text-white/40 leading-tight">PDF, JPG, PNG ou Corel/AI.</p>
                                    </div>
                                </label>
                                
                                <label class="config-arte-option flex items-center gap-2.5 sm:gap-3 p-3 sm:p-4 bg-white/5 rounded-xl sm:rounded-2xl border border-transparent hover:border-brand-primary cursor-pointer transition-all group">
                                    <input type="radio" name="tipo_arte" value="contratar" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-brand-primary focus:ring-0 border-white/20 bg-transparent" onchange="toggleArteUpload()">
                                    <div class="flex-1">
                                        <p class="text-xs sm:text-sm font-bold text-white leading-none mb-0.5 sm:mb-1">Preciso que criem a arte</p>
                                        <p class="text-[9px] sm:text-[10px] text-white/40 leading-tight">+ R$ {{ number_format($produto->preco_arte, 2, ',', '.') }} (Criação)</p>
                                    </div>
                                </label>
                            </div>

                            <div id="blocoUploadArte" class="relative group mt-1.5 sm:mt-2">
                                <div class="w-full rounded-xl sm:rounded-2xl border-2 border-dashed border-white/10 bg-white/20 p-4 sm:p-6 text-center hover:border-brand-primary transition-all">
                                    <x-icon name="arrow-up-tray" class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1.5 sm:mb-2 text-white/30" />
                                    <p class="text-[10px] sm:text-xs font-bold text-white/70">Arraste ou clique</p>
                                    <p class="text-[8px] sm:text-[9px] text-white/30 uppercase tracking-widest mt-0.5 sm:mt-1">PDF, JPG, PNG ou AI</p>
                                    <input type="file" name="arte_arquivo" class="absolute inset-0 opacity-0 cursor-pointer">
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Observações --}}
                    <div>
                        <textarea name="observacoes" rows="2" placeholder="Observações, acabamentos..." 
                                  class="config-textarea w-full rounded-xl sm:rounded-[2rem] border-none bg-white/5 px-4 py-3 sm:px-6 sm:py-4 text-xs sm:text-sm text-white focus:ring-2 focus:ring-brand-primary transition-all placeholder:text-white/20"></textarea>
                    </div>

                    {{-- Feedback de Sucesso --}}
                    <div id="feedback-carrinho" class="hidden p-3 sm:p-4 bg-emerald-500/20 border border-emerald-500/30 rounded-xl sm:rounded-2xl">
                        <p class="text-emerald-400 font-bold flex items-center gap-2">
                            <x-icon name="check-badge" class="w-5 h-5" />
                            <span id="feedback-mensagem">Produto adicionado ao carrinho!</span>
                        </p>
                    </div>

                    {{-- Botões de Ação --}}
                    <div class="public-mobile-cta pt-2 sm:pt-6 space-y-3 sm:space-y-4">
                        {{-- Botão Adicionar ao Carrinho --}}
                        <button type="button" onclick="adicionarAoCarrinho()" id="btn-adicionar-carrinho"
                                class="btn-cta-primary public-touch group relative w-full h-12 sm:h-16 bg-brand-primary text-white rounded-xl sm:rounded-[2rem] overflow-hidden shadow-2xl shadow-brand-primary/40 active:scale-95 transition-all">
                            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
                            <span class="relative z-10 flex items-center justify-center gap-2 sm:gap-3 text-sm sm:text-lg font-black uppercase tracking-wider sm:tracking-widest">
                                <x-icon name="shopping-bag" class="w-4 h-4 sm:w-5 sm:h-5" />
                                Adicionar ao Carrinho
                            </span>
                        </button>

                        {{-- Botões Secundários --}}
                        <div class="hidden sm:grid grid-cols-2 gap-4">
                            <a href="{{ \App\Support\PublicUrlHelper::catalogo() }}" 
                               class="flex items-center justify-center gap-2 h-14 bg-white/5 text-white/70 rounded-2xl font-bold text-sm hover:bg-white/10 hover:text-white transition-all">
                                <x-icon name="arrow-left" class="w-4 h-4" />
                                Continuar Comprando
                            </a>
                            <button type="button" onclick="comprarAgora()" 
                                    class="flex items-center justify-center gap-2 h-14 bg-white/10 text-white rounded-2xl font-bold text-sm hover:bg-brand-primary transition-all">
                                Comprar Agora
                                <x-icon name="arrow-right" class="w-4 h-4" />
                            </button>
                        </div>

                {{-- Link Ver Carrinho --}}
                        <a href="{{ \App\Support\PublicUrlHelper::carrinho() }}" id="link-ver-carrinho"
                           class="hidden w-full text-center text-sm font-bold text-brand-primary hover:text-white transition-colors py-2">
                            Ver meu carrinho →
                        </a>
                    </div>
                </form>
            </div>
            
            {{-- WhatsApp do Especialista --}}
            @php $whatsClean = preg_replace('/[^0-9]/', '', $configSite['empresa_whatsapp'] ?? '5575999279354'); @endphp
            <a href="https://wa.me/{{ $whatsClean }}?text=Tenho%20dúvidas%20sobre%20o%20produto%20{{ urlencode($produto->nome) }}" target="_blank" class="product-whatsapp mt-3 flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-3 transition-all group hover:bg-emerald-500/20 sm:mt-8 sm:rounded-[2.5rem] sm:p-6 sm:gap-4">
                <div class="whats-icon w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-emerald-500 flex items-center justify-center text-white text-xl sm:text-3xl shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                    📱
                </div>
                <div>
                    <h4 class="font-black text-emerald-700 leading-tight text-sm sm:text-base">Dúvidas? Fale Online</h4>
                    <p class="text-[9px] sm:text-xs text-emerald-600/70 font-bold uppercase tracking-widest">Atendimento especializado</p>
                </div>
            </a>
        </aside>
    </div>

    {{-- Produtos Relacionados --}}
    @if($relacionados->isNotEmpty())
        <section class="related-section mt-12 sm:mt-24">
            <div class="related-header flex items-center justify-between mb-6 sm:mb-12">
                <div>
                    <span class="text-brand-primary font-black uppercase tracking-[0.15em] sm:tracking-[0.2em] text-[9px] sm:text-[10px] mb-1 sm:mb-2 block">Combine com este produto</span>
                    <h2 class="text-xl sm:text-3xl font-black text-brand-secondary tracking-tighter">Quem comprou também levou</h2>
                </div>
                <div class="hidden md:flex gap-4">
                    <a href="{{ \App\Support\PublicUrlHelper::catalogo() }}" class="text-sm font-bold text-slate-400 hover:text-brand-primary transition-colors flex items-center gap-2">
                        Ver todo o catálogo <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                </div>
            </div>
            
            <div class="related-grid grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-8">
                @foreach($relacionados as $rel)
                    <x-product-card :produto="$rel" />
                @endforeach
            </div>
        </section>
    @endif

    <script>
        function changeImage(url, btn) {
            document.getElementById('main-product-image').src = url;
            document.querySelectorAll('button[onclick*="changeImage"]').forEach(b => b.classList.remove('border-brand-primary'));
            btn.classList.add('border-brand-primary');
        }

        function adjustQty(amount) {
            const input = document.getElementById('input-quantidade');
            let val = parseInt(input.value) || 1;
            val += amount;
            if (val < 1) val = 1;
            input.value = val;
            atualizarCalculoPreco();
        }

        const precoBaseVenda = {{ (float) $produto->preco_base }};
        const precoArte = {{ (float) $produto->preco_arte }};

        function atualizarCalculoPreco() {
            let precoU = precoBaseVenda;
            document.querySelectorAll('.select-variacao').forEach(s => {
                if(s.selectedIndex > 0) precoU += parseFloat(s.options[s.selectedIndex].dataset.preco) || 0;
            });

            let extra = 0;
            const hireArt = document.querySelector('input[name="tipo_arte"][value="contratar"]');
            if(hireArt && hireArt.checked) extra = precoArte;

            const qty = parseInt(document.getElementById('input-quantidade').value) || 1;
            const total = (precoU * qty) + extra;

            const display = document.getElementById('preco-final-display');
            if(display) display.innerText = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        document.querySelectorAll('.select-variacao').forEach(el => el.addEventListener('change', atualizarCalculoPreco));
        document.getElementById('input-quantidade')?.addEventListener('input', atualizarCalculoPreco);
        document.querySelectorAll('input[name="tipo_arte"]').forEach(el => el.addEventListener('change', atualizarCalculoPreco));

        function toggleArteUpload() {
            const v = document.querySelector('input[name="tipo_arte"]:checked')?.value;
            const b = document.getElementById('blocoUploadArte');
            if(b) b.style.display = (v === 'enviar') ? 'block' : 'none';
        }

        // Funções de Carrinho
        async function adicionarAoCarrinho(irParaCheckout = false) {
            const form = document.getElementById('form-produto');
            const btn = document.getElementById('btn-adicionar-carrinho');
            const feedback = document.getElementById('feedback-carrinho');
            const feedbackMsg = document.getElementById('feedback-mensagem');
            const linkCarrinho = document.getElementById('link-ver-carrinho');

            // Coletar dados do formulário
            const formData = new FormData(form);
            const dados = {
                produto_id: formData.get('produto_id'),
                quantidade: formData.get('quantidade') || 1,
                observacoes: formData.get('observacoes') || '',
            };

            // Coletar variações selecionadas
            const variacoes = [];
            document.querySelectorAll('.select-variacao').forEach(select => {
                if (select.value) {
                    variacoes.push(select.value);
                }
            });
            if (variacoes.length > 0) {
                dados.observacoes = (dados.observacoes ? dados.observacoes + ' | ' : '') + 'Variações: ' + variacoes.join(', ');
            }

            // Arte
            const tipoArte = document.querySelector('input[name="tipo_arte"]:checked');
            if (tipoArte) {
                dados.observacoes = (dados.observacoes ? dados.observacoes + ' | ' : '') + 'Arte: ' + (tipoArte.value === 'contratar' ? 'Solicitar criação' : 'Cliente envia');
            }

            // Desabilitar botão
            btn.disabled = true;
            btn.innerHTML = '<span class="relative z-10 flex items-center justify-center gap-3 text-lg font-black uppercase tracking-widest">Adicionando...</span>';

            try {
                const response = await fetch('{{ route("site.carrinho.adicionar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(dados)
                });

                const result = await response.json();

                if (result.success) {
                    // Mostrar feedback
                    feedbackMsg.textContent = result.message || 'Produto adicionado ao carrinho!';
                    feedback.classList.remove('hidden');
                    linkCarrinho.classList.remove('hidden');

                    // Atualizar contador do carrinho no header se existir
                    const cartCounter = document.querySelector('[data-cart-count]');
                    if (cartCounter && result.carrinho) {
                        cartCounter.textContent = result.carrinho.total_itens;
                        cartCounter.classList.remove('hidden');
                    }

                    if (irParaCheckout) {
                        window.location.href = '{{ route("site.checkout.carrinho") }}';
                    }
                } else {
                    feedbackMsg.textContent = result.error || 'Erro ao adicionar produto.';
                    feedback.classList.remove('hidden');
                    feedback.classList.remove('bg-emerald-500/20', 'border-emerald-500/30');
                    feedback.classList.add('bg-red-500/20', 'border-red-500/30');
                    feedbackMsg.classList.remove('text-emerald-400');
                    feedbackMsg.classList.add('text-red-400');
                }
            } catch (error) {
                console.error('Erro:', error);
                feedbackMsg.textContent = 'Erro de conexão. Tente novamente.';
                feedback.classList.remove('hidden');
                feedback.classList.remove('bg-emerald-500/20', 'border-emerald-500/30');
                feedback.classList.add('bg-red-500/20', 'border-red-500/30');
                feedbackMsg.classList.remove('text-emerald-400');
                feedbackMsg.classList.add('text-red-400');
            } finally {
                // Restaurar botão
                btn.disabled = false;
                btn.innerHTML = '<div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div><span class="relative z-10 flex items-center justify-center gap-3 text-lg font-black uppercase tracking-widest"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg> Adicionar ao Carrinho</span>';
            }
        }

        function comprarAgora() {
            adicionarAoCarrinho(true);
        }
    </script>
</x-layouts.publico>
