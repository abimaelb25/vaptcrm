{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-06 00:00 -03:00
--}}
@php
    $gruposVariacoes = $produto->variacoes->groupBy('tipo_variacao');
@endphp
<x-layouts.publico>
    {{-- Breadcrumbs --}}
    <nav class="flex mb-4 text-xs font-bold uppercase tracking-widest text-slate-400 gap-2 items-center">
        <a href="{{ route('site.inicio') }}" class="hover:text-brand-primary transition-colors">Início</a>
        <span>/</span>
        <a href="{{ route('site.catalogo') }}" class="hover:text-brand-primary transition-colors">Catálogo</a>
        <span>/</span>
        @if($produto->categoriaRel)
            <a href="{{ route('site.categoria', $produto->categoriaRel->slug) }}" class="hover:text-brand-primary transition-colors">{{ $produto->categoriaRel->nome }}</a>
            <span>/</span>
        @endif
        <span class="text-brand-primary truncate max-w-[150px]">{{ $produto->nome }}</span>
    </nav>
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

    <div class="grid lg:grid-cols-12 gap-12 items-start">
        
        <!-- COLUNA ESQUERDA: Galeria e Info --Substancial -->
        <div class="lg:col-span-7 space-y-8">
            
            {{-- Galeria de Imagens --}}
            <div class="bg-white rounded-[2.5rem] p-4 shadow-sm border border-slate-100 overflow-hidden group">
                <div class="aspect-square w-full bg-slate-50 rounded-[2rem] overflow-hidden relative">
                    @if($produto->imagem_principal)
                        <img id="main-product-image" src="{{ asset('storage/' . $produto->imagem_principal) }}" alt="{{ $produto->nome }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-8xl text-slate-200">📦</div>
                    @endif

                    @if($produto->badge_comercial)
                        <div class="absolute top-6 left-6">
                            <span class="bg-brand-primary text-white text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-xl">
                                {{ $produto->badge_comercial }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Miniaturas/Thumnails --}}
                @if($produto->imagens->count() > 0)
                    <div class="flex gap-4 mt-6 px-2 overflow-x-auto pb-2 scrollbar-hide">
                        <button onclick="changeImage('{{ asset('storage/' . $produto->imagem_principal) }}', this)" class="w-20 h-20 shrink-0 rounded-2xl border-2 border-brand-primary overflow-hidden transition-all shadow-sm">
                            <img src="{{ asset('storage/' . $produto->imagem_principal) }}" class="w-full h-full object-cover">
                        </button>
                        @foreach($produto->imagens as $img)
                            <button onclick="changeImage('{{ asset('storage/' . $img->caminho) }}', this)" class="w-20 h-20 shrink-0 rounded-2xl border-2 border-transparent hover:border-slate-300 overflow-hidden transition-all grayscale hover:grayscale-0">
                                <img src="{{ asset('storage/' . $img->caminho) }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Detalhes Acadêmicos / Descrição --}}
            <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-sm border border-slate-100">
                <div class="flex items-center gap-4 mb-8">
                    <span class="w-1.5 h-8 bg-brand-primary rounded-full"></span>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Descrição do Produto</h2>
                </div>
                
                <div class="prose prose-slate max-w-none prose-p:text-slate-500 prose-p:leading-relaxed prose-strong:text-slate-800">
                    {!! nl2br(e($produto->descricao_completa ?: $produto->descricao_curta)) !!}
                </div>

                @if($produto->prazo_estimado)
                    <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center gap-4 p-5 rounded-3xl bg-slate-50 border border-slate-100/50">
                            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-2xl shadow-sm">🕒</div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Produção</p>
                                <p class="text-sm font-bold text-slate-700 italic">{{ $produto->prazo_estimado }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 p-5 rounded-3xl bg-slate-50 border border-slate-100/50">
                            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-2xl shadow-sm">📦</div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Embalagem</p>
                                <p class="text-sm font-bold text-slate-700 italic">Proteção Reforçada</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Selos de Confiança / Roadmap Visual --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-6 bg-slate-50 rounded-[2rem] border border-slate-100/50 grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all cursor-default">
                    <x-icon name="shield-check" class="w-8 h-8 mx-auto mb-3 text-brand-secondary" />
                    <p class="text-[10px] font-black uppercase text-slate-800">Compra 100% Segura</p>
                </div>
                <div class="text-center p-6 bg-slate-50 rounded-[2rem] border border-slate-100/50 grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all cursor-default">
                    <x-icon name="check-badge" class="w-8 h-8 mx-auto mb-3 text-brand-secondary" />
                    <p class="text-[10px] font-black uppercase text-slate-800">Qualidade Premium</p>
                </div>
                <div class="text-center p-6 bg-slate-50 rounded-[2rem] border border-slate-100/50 grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all cursor-default">
                    <x-icon name="chat-bubble-left-right" class="w-8 h-8 mx-auto mb-3 text-brand-secondary" />
                    <p class="text-[10px] font-black uppercase text-slate-800">Suporte Especializado</p>
                </div>
            </div>
        </div>

        <!-- COLUNA DIREIT: Comercial e Personalização -->
        <aside class="lg:col-span-5 sticky top-28">
            <div class="bg-slate-900 rounded-[3rem] p-8 md:p-10 text-white shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-brand-primary/10 rounded-full blur-3xl -mr-32 -mt-32"></div>

                {{-- Header Comercial --}}
                <div class="relative z-10 mb-8 pb-8 border-b border-white/10">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="bg-brand-primary h-px flex-1"></span>
                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-brand-primary">Configurador</span>
                        <span class="bg-brand-primary h-px flex-1"></span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-black tracking-tight mb-2">{{ $produto->nome }}</h1>
                    <p class="text-sm text-white/50 font-medium italic mb-6">{{ $produto->descricao_curta }}</p>

                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Preço Total Estimado</p>
                            <h3 class="text-5xl font-black text-brand-primary tracking-tighter" id="preco-final-display">
                                R$ {{ number_format($produto->preco_base, 2, ',', '.') }}
                            </h3>
                        </div>
                        <div class="text-right pb-1">
                            @if($produto->unidade_venda)
                                <span class="bg-white/10 text-white/70 text-[10px] font-black px-3 py-1 rounded-lg uppercase tracking-wider">
                                    {{ $produto->unidade_venda }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Formulário de Pedido --}}
                <form method="POST" action="{{ route('site.checkout.store', $produto->slug) }}" enctype="multipart/form-data" class="relative z-10 flex flex-col gap-6">
                    @csrf
                    
                    {{-- Dados do Cliente --}}
                    <div class="space-y-4 mb-4">
                        <input type="text" name="nome_cliente" required placeholder="Seu nome completo" 
                               class="w-full rounded-2xl border-none bg-white/5 px-6 py-4 text-sm text-white focus:ring-2 focus:ring-brand-primary transition-all placeholder:text-white/20">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="telefone_cliente" required placeholder="WhatsApp" 
                                   class="w-full rounded-2xl border-none bg-white/5 px-6 py-4 text-sm text-white focus:ring-2 focus:ring-brand-primary transition-all placeholder:text-white/20">
                            <input type="email" name="email_cliente" placeholder="E-mail" 
                                   class="w-full rounded-2xl border-none bg-white/5 px-6 py-4 text-sm text-white focus:ring-2 focus:ring-brand-primary transition-all placeholder:text-white/20">
                        </div>
                    </div>

                    {{-- Quantidade --}}
                    <div class="p-4 bg-white/5 rounded-[2rem] border border-white/5">
                        <label class="block mb-3 text-[10px] font-black text-white/40 uppercase tracking-widest ml-2">Quantidade Desejada</label>
                        <div class="flex items-center gap-4">
                            <button type="button" onclick="adjustQty(-1)" class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-xl font-black hover:bg-brand-primary transition-colors">-</button>
                            <input type="number" name="quantidade" id="input-quantidade" value="1" min="1" required 
                                   class="flex-1 bg-transparent border-none text-center text-2xl font-black text-white focus:ring-0 p-0">
                            <button type="button" onclick="adjustQty(1)" class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-xl font-black hover:bg-brand-primary transition-colors">+</button>
                        </div>
                    </div>

                    {{-- Variações Dinâmicas --}}
                    @if($gruposVariacoes->isNotEmpty())
                        <div class="space-y-6 pt-2">
                            @foreach($gruposVariacoes as $tipo => $opcoes)
                                <div>
                                    <label class="block mb-3 text-[10px] font-black text-white/40 uppercase tracking-widest ml-2">{{ $tipo }}</label>
                                    <div class="relative">
                                        <select name="variacoes[{{ $tipo }}]" 
                                                class="select-variacao w-full appearance-none rounded-2xl border-none bg-white/5 px-6 py-4 text-sm font-bold text-white focus:ring-2 focus:ring-brand-primary transition-all cursor-pointer">
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
                        <div class="p-6 bg-brand-primary/10 rounded-[2.5rem] border border-brand-primary/20 space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-widest text-brand-primary flex items-center gap-2">
                                <x-icon name="photo" class="w-4 h-4" /> Design e Arte
                            </h4>
                            
                            <div class="grid grid-cols-1 gap-3">
                                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-2xl border border-transparent hover:border-brand-primary cursor-pointer transition-all group">
                                    <input type="radio" name="tipo_arte" value="enviar" checked class="w-4 h-4 text-brand-primary focus:ring-0 border-white/20 bg-transparent" onchange="toggleArteUpload()">
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-white leading-none mb-1">Vou enviar meu arquivo</p>
                                        <p class="text-[10px] text-white/40 leading-tight">PDF, JPG, PNG ou Corel/AI.</p>
                                    </div>
                                </label>
                                
                                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-2xl border border-transparent hover:border-brand-primary cursor-pointer transition-all group">
                                    <input type="radio" name="tipo_arte" value="contratar" class="w-4 h-4 text-brand-primary focus:ring-0 border-white/20 bg-transparent" onchange="toggleArteUpload()">
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-white leading-none mb-1">Preciso que criem a arte</p>
                                        <p class="text-[10px] text-white/40 leading-tight">+ R$ {{ number_format($produto->preco_arte, 2, ',', '.') }} (Criação Professional)</p>
                                    </div>
                                </label>
                            </div>

                            <div id="blocoUploadArte" class="relative group mt-2">
                                <div class="w-full rounded-2xl border-2 border-dashed border-white/10 bg-white/20 p-6 text-center hover:border-brand-primary transition-all">
                                    <x-icon name="arrow-up-tray" class="w-8 h-8 mx-auto mb-2 text-white/30" />
                                    <p class="text-xs font-bold text-white/70">Arraste seu arquivo aqui</p>
                                    <p class="text-[9px] text-white/30 uppercase tracking-widest mt-1">ou clique para selecionar</p>
                                    <input type="file" name="arte_arquivo" class="absolute inset-0 opacity-0 cursor-pointer">
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Observações --}}
                    <div>
                        <textarea name="especificacoes" rows="3" placeholder="Informações extras ou acabamentos específicos..." 
                                  class="w-full rounded-[2rem] border-none bg-white/5 px-6 py-4 text-sm text-white focus:ring-2 focus:ring-brand-primary transition-all placeholder:text-white/20"></textarea>
                    </div>

                    {{-- CTA Final --}}
                    <div class="pt-6">
                        <button type="submit" class="group relative w-full h-20 bg-brand-primary text-white rounded-[2rem] overflow-hidden shadow-2xl shadow-brand-primary/40 active:scale-95 transition-all">
                            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
                            <span class="relative z-10 flex items-center justify-center gap-3 text-xl font-black uppercase tracking-widest">
                                Comprar Agora
                                <x-icon name="arrow-right" class="w-6 h-6 group-hover:translate-x-2 transition-transform" />
                            </span>
                        </button>
                    </div>
                </form>
            </div>
            
            {{-- WhatsApp do Especialista --}}
            @php $whatsClean = preg_replace('/[^0-9]/', '', $configSite['empresa_whatsapp'] ?? '5575999279354'); @endphp
            <a href="https://wa.me/{{ $whatsClean }}?text=Tenho%20dúvidas%20sobre%20o%20produto%20{{ urlencode($produto->nome) }}" target="_blank" class="mt-8 flex items-center gap-4 p-6 bg-emerald-500/10 border border-emerald-500/20 rounded-[2.5rem] hover:bg-emerald-500/20 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-emerald-500 flex items-center justify-center text-white text-3xl shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                    📱
                </div>
                <div>
                    <h4 class="font-black text-emerald-700 leading-tight">Dúvidas? Fale Online</h4>
                    <p class="text-xs text-emerald-600/70 font-bold uppercase tracking-widest">Atendimento especializado</p>
                </div>
            </a>
        </aside>
    </div>

    {{-- Produtos Relacionados --}}
    @if($relacionados->isNotEmpty())
        <section class="mt-24">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <span class="text-brand-primary font-black uppercase tracking-[0.2em] text-[10px] mb-2 block">Combine com este produto</span>
                    <h2 class="text-3xl font-black text-brand-secondary tracking-tighter">Quem comprou também levou</h2>
                </div>
                <div class="hidden md:flex gap-4">
                    <a href="{{ route('site.catalogo') }}" class="text-sm font-bold text-slate-400 hover:text-brand-primary transition-colors flex items-center gap-2">
                        Ver todo o catálogo <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
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
    </script>
    
    <script>
        const precoBaseVenda = {{ (float) $produto->preco_base }};

        function atualizarCalculoPreco() {
            let precoFinalUnidade = precoBaseVenda;
            
            // Soma as variações
            const selects = document.querySelectorAll('.select-variacao');
            selects.forEach(select => {
                if(select.options[select.selectedIndex]) {
                    const acrescimo = parseFloat(select.options[select.selectedIndex].dataset.preco) || 0;
                    precoFinalUnidade += acrescimo;
                }
            });

            // Se exige arte e o cara escolheu "contratar"
            let precoArteFinal = 0;
            const arteContratar = document.querySelector('input[name="tipo_arte"][value="contratar"]');
            if(arteContratar && arteContratar.checked) {
                precoArteFinal = {{ (float) $produto->preco_arte }};
            }

            const inputQtd = document.getElementById('input-quantidade');
            const quantidade = parseInt(inputQtd ? inputQtd.value : 1) || 1;
            
            const valorTotal = (precoFinalUnidade * quantidade) + precoArteFinal;

            const display = document.getElementById('preco-final-display');
            if (display && precoBaseVenda > 0) {
                display.innerText = 'R$ ' + valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        }

        document.querySelectorAll('.select-variacao').forEach(el => el.addEventListener('change', atualizarCalculoPreco));
        document.getElementById('input-quantidade')?.addEventListener('input', atualizarCalculoPreco);
        document.querySelectorAll('input[name="tipo_arte"]').forEach(el => el.addEventListener('change', atualizarCalculoPreco));
    </script>
</x-layouts.publico>

