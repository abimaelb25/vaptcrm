{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-22 02:00 -03:00
--}}
<x-layouts.publico titulo="{{ $configSite['empresa_nome'] ?? 'Gráfica' }} - Carrinho">
    
    <div class="mx-auto max-w-7xl px-0 py-6 sm:px-2 sm:py-10 lg:px-4">
        <x-public.breadcrumb :items="[
            ['label' => 'Início', 'url' => \App\Support\PublicUrlHelper::inicio()],
            ['label' => 'Catálogo', 'url' => \App\Support\PublicUrlHelper::catalogo()],
            ['label' => 'Meu Carrinho'],
        ]" />

        {{-- Alertas --}}
        @if(session('sucesso'))
            <div class="mb-10 rounded-2xl bg-emerald-50 border-l-4 border-emerald-500 p-6 text-emerald-900 shadow-sm flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="bg-emerald-500/10 p-2 rounded-full shrink-0">
                    <x-icon name="check-badge" class="w-6 h-6 text-emerald-600" />
                </div>
                <span class="font-bold text-sm leading-tight">{{ session('sucesso') }}</span>
            </div>
        @endif

        @if(session('erro'))
            <div class="mb-10 rounded-2xl bg-red-50 border-l-4 border-red-500 p-6 text-red-900 shadow-sm flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="bg-red-500/10 p-2 rounded-full shrink-0">
                    <x-icon name="exclamation-circle" class="w-6 h-6 text-red-600" />
                </div>
                <span class="font-bold text-sm leading-tight">{{ session('erro') }}</span>
            </div>
        @endif

        @if(!empty($itensRemovidos))
            <div class="mb-10 rounded-2xl bg-amber-50 border-l-4 border-amber-500 p-6 text-amber-900 shadow-sm">
                <div class="flex items-center gap-4 mb-4">
                    <div class="bg-amber-500/10 p-2 rounded-full shrink-0">
                        <x-icon name="exclamation-triangle" class="w-6 h-6 text-amber-600" />
                    </div>
                    <span class="font-black text-xs uppercase tracking-widest text-amber-800">Atenção: Itens Removidos</span>
                </div>
                <ul class="list-disc list-inside text-sm pl-12 space-y-1 font-medium opacity-80">
                    @foreach($itensRemovidos as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 items-start">
            
            {{-- Lista de Itens --}}
            <div class="flex-1 w-full lg:max-w-none">
                <div class="mb-7 flex items-center justify-between border-b border-slate-100 pb-6 sm:mb-10 sm:pb-8">
                    <div class="cart-header">
                        <h1 class="text-xl sm:text-4xl font-black text-brand-secondary tracking-tight">Carrinho</h1>
                        <p class="text-slate-400 text-sm mt-1 font-medium">Você tem itens prontos para produção</p>
                    </div>
                    @if($carrinho['total_linhas'] > 0)
                        <div class="hidden sm:block text-right">
                            <span class="bg-slate-50 text-slate-500 text-[10px] font-black px-4 py-2 rounded-full border border-slate-100 uppercase tracking-widest">
                                {{ $carrinho['total_itens'] }} {{ $carrinho['total_itens'] === 1 ? 'Volume' : 'Volumes' }}
                            </span>
                        </div>
                    @endif
                </div>

                @if($carrinho['total_linhas'] > 0)
                    <div class="space-y-6">
                        @foreach($carrinho['items'] as $itemKey => $item)
                            <article class="cart-item group relative flex flex-col sm:flex-row items-center sm:items-stretch gap-4 sm:gap-6 rounded-xl sm:rounded-[2rem] border border-slate-100 bg-white p-4 sm:p-6 transition-all duration-300 hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.05)] hover:border-brand-primary/20">
                                {{-- Imagem --}}
                                <div class="cart-item-img w-20 h-20 sm:w-28 sm:h-28 shrink-0 overflow-hidden rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-50 group-hover:scale-105 transition-transform duration-500">
                                    @if($item['imagem'])
                                        <img src="{{ asset('storage/' . $item['imagem']) }}" 
                                             alt="{{ $item['nome'] }}" 
                                             class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100">
                                            <x-icon name="photo" class="w-10 h-10 text-slate-200" />
                                        </div>
                                    @endif
                                </div>

                                {{-- Detalhes --}}
                                <div class="flex-1 flex flex-col justify-center min-w-0 text-center sm:text-left">
                                    <div class="mb-2">
                                        <a href="{{ \App\Support\PublicUrlHelper::produtoPorSlug($item['slug']) }}" class="cart-item-name text-sm sm:text-lg font-black text-slate-800 hover:text-brand-primary transition-colors leading-tight group-hover:underline decoration-brand-primary/20">
                                            {{ $item['nome'] }}
                                        </a>
                                    </div>
                                    
                                    @if($item['variacao_nome'])
                                        <div class="inline-flex items-center gap-2 justify-center sm:justify-start px-2 py-0.5 rounded-lg bg-brand-primary/5 border border-brand-primary/10 w-fit mx-auto sm:mx-0">
                                            <span class="text-[9px] font-black text-brand-primary uppercase tracking-widest">Especificação:</span>
                                            <span class="text-xs font-bold text-brand-primary/80">{{ $item['variacao_nome'] }}</span>
                                        </div>
                                    @endif

                                    @if($item['observacoes'])
                                        <p class="text-xs text-slate-400 mt-3 font-medium opacity-60 line-clamp-1 group-hover:line-clamp-none transition-all">
                                            Obs: {{ $item['observacoes'] }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Quantidade e Ações --}}
                                <div class="flex flex-col items-center sm:items-end justify-between gap-4 py-2 sm:pl-8 sm:border-l border-slate-50">
                                    <div class="text-center sm:text-right">
                                        <p class="cart-item-price text-base sm:text-xl font-black text-slate-800 tracking-tight">
                                            R$ {{ number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') }}
                                        </p>
                                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Unitário: R$ {{ number_format($item['preco_unitario'], 2, ',', '.') }}</span>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        {{-- Controle de Quantidade --}}
                                        <form action="{{ route('site.carrinho.atualizar', $itemKey) }}" method="POST" class="flex items-center bg-slate-50 p-1 rounded-xl border border-slate-100">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button" 
                                                    onclick="alterarQtd(this, -1)" 
                                                    class="w-8 h-8 rounded-lg bg-white text-slate-400 hover:text-brand-primary hover:shadow-sm transition-all flex items-center justify-center font-bold border border-slate-50 shadow-sm">
                                                <x-icon name="minus" class="w-3 h-3" />
                                            </button>
                                            <input type="number" 
                                                   name="quantidade" 
                                                   value="{{ $item['quantidade'] }}" 
                                                   min="1" 
                                                   max="9999"
                                                   class="w-10 h-8 bg-transparent text-center border-none text-sm font-black text-slate-700 focus:ring-0"
                                                   onchange="this.form.submit()">
                                            <button type="button" 
                                                    onclick="alterarQtd(this, 1)" 
                                                    class="w-8 h-8 rounded-lg bg-white text-slate-400 hover:text-brand-primary hover:shadow-sm transition-all flex items-center justify-center font-bold border border-slate-50 shadow-sm">
                                                <x-icon name="plus" class="w-3 h-3" />
                                            </button>
                                        </form>

                                        {{-- Botão Remover --}}
                                        <form action="{{ route('site.carrinho.remover', $itemKey) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="w-10 h-10 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-500 hover:text-white transition-all duration-300 flex items-center justify-center group/btn border border-slate-100"
                                                    onclick="return confirm('Remover este item?')"
                                                    title="Remover Item">
                                                <x-icon name="trash" class="w-4 h-4 group-hover/btn:scale-110 transition-transform" />
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- Ações do Carrinho --}}
                    <div class="mt-12 flex flex-wrap items-center justify-between gap-6 px-4">
                        <a href="{{ \App\Support\PublicUrlHelper::catalogo() }}" class="inline-flex items-center gap-3 text-[11px] font-black text-slate-400 hover:text-brand-primary transition-all group tracking-widest uppercase">
                            <x-icon name="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
                            <span>Continuar Comprando</span>
                        </a>
                        
                        <form action="{{ route('site.carrinho.limpar') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-[11px] font-black text-red-200 hover:text-red-500 transition-colors uppercase tracking-widest flex items-center gap-2 group"
                                    onclick="return confirm('Esvaziar todo o carrinho?')">
                                <x-icon name="trash" class="w-4 h-4 opacity-50 group-hover:opacity-100" />
                                <span>Limpar Carrinho</span>
                            </button>
                        </form>
                    </div>

                @else
                    {{-- Carrinho Vazio --}}
                    <div class="py-24 text-center rounded-[3rem] border-2 border-dashed border-slate-100 bg-white shadow-sm overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-slate-50/20 pointer-events-none"></div>
                        <div class="relative z-10">
                            <div class="inline-flex items-center justify-center w-20 h-20 mb-8 bg-slate-50 rounded-full">
                                <x-icon name="shopping-cart" class="w-10 h-10 text-slate-200" />
                            </div>
                            <h2 class="text-2xl font-black text-slate-800 mb-3 tracking-tight">O carrinho ainda está vazio</h2>
                            <p class="text-slate-400 mb-10 max-w-xs mx-auto text-sm font-medium">Suas produções começam aqui. Adicione algo do nosso catálogo!</p>
                            <a href="{{ \App\Support\PublicUrlHelper::catalogo() }}" class="inline-flex items-center gap-3 bg-brand-secondary text-white font-black px-10 py-5 rounded-2xl hover:bg-brand-primary transition-all hover:scale-[1.03] shadow-xl shadow-brand-secondary/10 group">
                                <span class="tracking-wide">VOLTAR AO CATÁLOGO</span>
                                <x-icon name="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Resumo do Pedido --}}
            @if($carrinho['total_linhas'] > 0)
                <aside class="w-full lg:w-[420px] shrink-0 lg:sticky lg:top-32 mt-12 lg:mt-0">
                    <div class="cart-summary rounded-xl sm:rounded-[2.5rem] border border-slate-100 bg-white shadow-[0_30px_60px_-15px_rgba(0,0,0,0.08)] overflow-hidden">
                        <div class="cart-summary-header bg-brand-secondary p-5 sm:p-8 pb-10 sm:pb-14 relative overflow-hidden">
                            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-56 h-56 bg-white/5 rounded-full pointer-events-none"></div>
                            <h2 class="text-white font-black text-lg sm:text-2xl tracking-normal relative z-10">Resumo Final</h2>
                            <p class="text-brand-accent/60 text-[10px] font-black uppercase tracking-[0.2em] mt-1 relative z-10">PROCESSAMENTO SEGURO</p>
                        </div>
                        
                        <div class="cart-summary-body p-5 sm:p-8 pb-6 sm:pb-10 -mt-8 bg-white rounded-t-[2.5rem] relative z-20">
                            <div class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Itens Selecionados</span>
                                    <span class="font-black text-slate-800">{{ $carrinho['total_itens'] }} un</span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Valor dos Produtos</span>
                                    <span class="font-black text-slate-800">R$ {{ number_format($carrinho['subtotal'], 2, ',', '.') }}</span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Frete</span>
                                    <div class="flex flex-col items-end">
                                        <span class="font-black text-emerald-500 uppercase text-[10px] bg-emerald-50 px-2 py-0.5 rounded-md tracking-widest">Grátis*</span>
                                        <span class="text-[8px] text-slate-300 font-black mt-1 uppercase tracking-tighter">PARA ALGUMAS REGIÕES</span>
                                    </div>
                                </div>

                                <div class="pt-8 mt-4 border-t border-slate-50">
                                    <div class="flex justify-between items-end">
                                        <span class="font-black text-slate-800 text-base">Total do Pedido</span>
                                        <span class="cart-total-value text-2xl sm:text-4xl font-black text-brand-primary tracking-normal leading-none" style="letter-spacing: -0.02em;">
                                            {{ $carrinho['subtotal_formatado'] }}
                                        </span>
                                    </div>
                                </div>

                                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 flex items-start gap-4">
                                    <div class="p-2 bg-emerald-500/10 rounded-xl shrink-0">
                                        <x-icon name="shield-check" class="w-5 h-5 text-emerald-600" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-700 uppercase tracking-widest mb-1">Pagamento 100% Seguro</p>
                                        <p class="text-[9px] text-slate-400 font-bold leading-relaxed">Criptografia de ponta a ponta e processamento imediato via Stripe®.</p>
                                    </div>
                                </div>

                                <a href="{{ \App\Support\PublicUrlHelper::checkout() }}" 
                                   class="cart-cta-finalizar group flex items-center justify-between w-full bg-brand-primary text-white font-black p-4 sm:p-6 rounded-xl sm:rounded-2xl hover:bg-brand-secondary transition-all hover:scale-[1.02] active:scale-95 shadow-2xl shadow-brand-primary/20 mt-5 sm:mt-8 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                                    <span class="text-sm sm:text-lg tracking-wide relative z-10">FINALIZAR COMPRA</span>
                                    <x-icon name="arrow-right" class="w-6 h-6 group-hover:translate-x-1.5 transition-transform relative z-10" />
                                </a>

                                <div class="flex items-center justify-center gap-5 pt-8 opacity-30 grayscale hover:grayscale-0 hover:opacity-100 transition-all duration-500">
                                    <img src="https://img.icons8.com/color/48/visa.png" class="h-5 w-auto" alt="Visa">
                                    <img src="https://img.icons8.com/color/48/mastercard.png" class="h-5 w-auto" alt="Master">
                                    <img src="https://img.icons8.com/color/48/pix.png" class="h-5 w-auto" alt="PIX">
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            @endif
        </div>

        <p class="mt-12 text-[9px] text-center text-slate-300 font-black uppercase tracking-[0.2em] leading-loose max-w-2xl mx-auto">
            Ao finalizar este pedido você concorda com nossos 
            <a href="#" class="text-slate-400 hover:text-brand-primary border-b border-slate-200 mx-1">Termos de Uso</a> e 
            <a href="#" class="text-slate-400 hover:text-brand-primary border-b border-slate-200 mx-1">Privacidade</a>
        </p>
    </div>

    @push('scripts')
    <script>
        function alterarQtd(btn, delta) {
            const form = btn.closest('form');
            const input = form.querySelector('input[name="quantidade"]');
            let val = parseInt(input.value) + delta;
            
            btn.classList.add('scale-90', 'opacity-50');
            setTimeout(() => btn.classList.remove('scale-90', 'opacity-50'), 200);
            
            if (val < 1) val = 1;
            if (val > 9999) val = 9999;
            
            if (parseInt(input.value) === val) return;
            
            input.value = val;
            form.submit();
        }
    </script>
    @endpush
</x-layouts.publico>

