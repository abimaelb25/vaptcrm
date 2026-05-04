{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-22 02:00 -03:00
--}}
<x-layouts.publico titulo="{{ $configSite['empresa_nome'] ?? 'Gráfica' }} - Checkout">
    
    <x-public.breadcrumb :items="[
        ['label' => 'Início', 'url' => \App\Support\PublicUrlHelper::inicio()],
        ['label' => 'Catálogo', 'url' => \App\Support\PublicUrlHelper::catalogo()],
        ['label' => 'Carrinho', 'url' => \App\Support\PublicUrlHelper::carrinho()],
        ['label' => 'Checkout'],
    ]" />

    <div class="mx-auto max-w-4xl">
        <div class="checkout-header mb-6 text-center sm:mb-10">
            <h1 class="text-xl sm:text-3xl font-black text-brand-secondary mb-1 sm:mb-2">Finalizar Compra</h1>
            <p class="text-slate-500 text-sm">Complete seus dados para finalizar o pedido</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Formulário de Checkout --}}
            <div class="flex-1">
                <div class="checkout-form-card bg-white rounded-xl sm:rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6">
                    <h2 class="font-black text-slate-800 mb-6 flex items-center gap-2">
                        <x-icon name="user-circle" class="w-5 h-5 text-brand-primary" />
                        Seus Dados
                    </h2>

                    <form action="{{ route('site.checkout.finalizar') }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-0.5 sm:mb-1">Nome completo *</label>
                            <input type="text" name="nome_cliente" required
                                   class="w-full rounded-lg sm:rounded-xl border-slate-200 py-2.5 sm:py-3 px-3 sm:px-4 text-sm focus:ring-brand-primary focus:border-brand-primary"
                                   placeholder="Seu nome">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-0.5 sm:mb-1">WhatsApp *</label>
                                <input type="tel" name="telefone_cliente" required
                                       class="w-full rounded-lg sm:rounded-xl border-slate-200 py-2.5 sm:py-3 px-3 sm:px-4 text-sm focus:ring-brand-primary focus:border-brand-primary"
                                       placeholder="(00) 00000-0000">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-0.5 sm:mb-1">E-mail</label>
                                <input type="email" name="email_cliente"
                                       class="w-full rounded-lg sm:rounded-xl border-slate-200 py-2.5 sm:py-3 px-3 sm:px-4 text-sm focus:ring-brand-primary focus:border-brand-primary"
                                       placeholder="seu@email.com">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-0.5 sm:mb-1">Observações</label>
                            <textarea name="observacoes" rows="2"
                                      class="w-full rounded-lg sm:rounded-xl border-slate-200 py-2.5 sm:py-3 px-3 sm:px-4 text-sm focus:ring-brand-primary focus:border-brand-primary"
                                      placeholder="Alguma observação sobre o pedido?"></textarea>
                        </div>

                        <hr class="my-6 border-slate-100">

                        <h2 class="font-black text-slate-800 mb-4 flex items-center gap-2">
                            <x-icon name="tag" class="w-5 h-5 text-brand-primary" />
                            Cupom de Desconto
                        </h2>

                        <div>
                            <div class="flex gap-2">
                                <input type="text" name="cupom_codigo" id="input-cupom"
                                       class="flex-1 rounded-xl border-slate-200 py-3 px-4 focus:ring-brand-primary focus:border-brand-primary uppercase"
                                       placeholder="Digite o cupom">
                                <button type="button" onclick="validarCupom()" 
                                        class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition-colors text-sm">
                                    Aplicar
                                </button>
                            </div>
                            <p id="cupom-feedback" class="text-sm mt-2 hidden"></p>
                        </div>

                        <hr class="my-6 border-slate-100">

                        <h2 class="font-black text-slate-800 mb-4 flex items-center gap-2">
                            <x-icon name="credit-card" class="w-5 h-5 text-brand-primary" />
                            Forma de Pagamento
                        </h2>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-brand-primary transition-colors">
                                <input type="radio" name="forma_pagamento" value="pix" class="text-brand-primary focus:ring-brand-primary" checked>
                                <span class="font-bold text-slate-700">PIX</span>
                                <span class="text-sm text-slate-500 ml-auto">Aprovação imediata</span>
                            </label>

                            <label class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-brand-primary transition-colors">
                                <input type="radio" name="forma_pagamento" value="cartao" class="text-brand-primary focus:ring-brand-primary">
                                <span class="font-bold text-slate-700">Cartão de Crédito</span>
                                <span class="text-sm text-slate-500 ml-auto">Via Stripe</span>
                            </label>
                        </div>

                        <button type="submit" 
                                class="checkout-submit w-full mt-4 sm:mt-6 bg-brand-primary text-white font-black py-3 sm:py-4 rounded-lg sm:rounded-xl hover:bg-brand-secondary transition-all shadow-lg shadow-brand-primary/20 flex items-center justify-center gap-2 text-sm sm:text-base">
                            Confirmar Pedido <x-icon name="arrow-right" class="w-5 h-5" />
                        </button>
                    </form>
                </div>
            </div>

            {{-- Resumo do Pedido --}}
            <aside class="w-full lg:w-80 shrink-0">
                <div class="sticky top-28 rounded-2xl border border-slate-100 bg-white shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-brand-secondary to-slate-700 px-6 py-4">
                        <h2 class="text-white font-black text-sm uppercase tracking-widest">Seu Pedido</h2>
                    </div>
                    
                    <div class="p-4 max-h-64 overflow-y-auto">
                        @foreach($carrinho['items'] as $item)
                            <div class="flex items-center gap-3 py-2 border-b border-slate-50 last:border-0">
                                <div class="w-12 h-12 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                                    @if($item['imagem'])
                                        <img src="{{ asset('storage/' . $item['imagem']) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-lg">📦</div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm text-slate-800 truncate">{{ $item['nome'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $item['quantidade'] }}x R$ {{ number_format($item['preco_unitario'], 2, ',', '.') }}</p>
                                </div>
                                <p class="font-bold text-sm text-slate-800 whitespace-nowrap">
                                    R$ {{ number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-6 border-t border-slate-100 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Subtotal</span>
                            <span class="font-bold text-slate-800">{{ $carrinho['subtotal_formatado'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Frete</span>
                            <span class="font-medium text-emerald-600">A combinar</span>
                        </div>
                        <hr class="border-slate-100">
                        <div class="flex justify-between">
                            <span class="font-bold text-slate-800">Total</span>
                            <span class="text-xl font-black text-brand-primary">{{ $carrinho['subtotal_formatado'] }}</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('site.carrinho') }}" class="block text-center text-sm font-bold text-slate-600 hover:text-brand-primary transition-colors mt-4">
                    <x-icon name="arrow-left" class="w-4 h-4 inline" /> Voltar ao Carrinho
                </a>
            </aside>
        </div>
    </div>
</x-layouts.publico>
