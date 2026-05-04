{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-06 00:00 -03:00
--}}
<x-layouts.publico titulo="Pedido Recebido - Gráfica Vapt Vupt">
    <x-public.breadcrumb :items="[
        ['label' => 'Início', 'url' => \App\Support\PublicUrlHelper::inicio()],
        ['label' => 'Checkout', 'url' => route('site.checkout.carrinho')],
        ['label' => 'Sucesso'],
    ]" />

    <section class="mx-auto mt-4 max-w-2xl px-0 sm:mt-8 md:mt-12">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden text-center border border-slate-100">
            <!-- Header animado -->
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-600 px-5 py-10 sm:px-6 sm:py-12">
                <!-- Icone Sucesso -->
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white/20 shadow-inner backdrop-blur sm:h-24 sm:w-24 animate-pulse">
                    <svg class="h-10 w-10 text-white sm:h-12 sm:w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                
                <h1 class="mt-5 text-3xl font-black tracking-tight text-white drop-shadow-md sm:mt-6 md:text-5xl">
                    Obrigado!
                </h1>
                <p class="mx-auto mt-3 max-w-lg text-sm font-medium text-emerald-50 sm:mt-4 sm:text-lg">
                    Recebemos o seu pedido e já estamos organizando tudo para você.
                </p>
            </div>

            <!-- Resumo e próximos passos -->
            <div class="space-y-7 p-5 sm:p-8 md:p-12">
                <div class="bg-brand-secondary/5 rounded-2xl p-6 border border-brand-secondary/10">
                    <p class="text-sm text-slate-500 font-bold uppercase tracking-wider mb-2">Seu Código de Acompanhamento</p>
                    <p class="text-3xl font-black text-brand-secondary tracking-widest font-mono sm:text-4xl">
                        {{ $pedido->codigo_pedido ?? $pedido->numero }}
                    </p>
                    <p class="text-xs text-slate-400 mt-3 font-medium">Guarde esse código para acompanhar a produção.</p>
                </div>

                {{-- QR Code PIX (se disponível) --}}
                @if(!empty($pix))
                    <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded-2xl p-4 sm:p-6 border-2 border-teal-200 text-center">
                        <div class="flex items-center justify-center gap-2 mb-4">
                            <img src="https://logodownload.org/wp-content/uploads/2020/11/pix-logo.png" alt="PIX" class="h-6">
                            <span class="text-lg font-black text-teal-700">Pague com PIX</span>
                        </div>
                        
                        <p class="text-2xl font-black text-emerald-600 mb-4">
                            R$ {{ number_format($pedido->total, 2, ',', '.') }}
                        </p>

                        @if($pix['qrcode_base64'])
                            <div class="bg-white rounded-xl p-3 sm:p-4 inline-block shadow-md mb-4">
                                <img src="{{ $pix['qrcode_base64'] }}" alt="QR Code PIX" class="mx-auto h-40 w-40 sm:h-48 sm:w-48">
                            </div>
                        @endif

                        <div class="mt-4">
                            <p class="text-xs text-slate-500 mb-2 font-bold uppercase tracking-wider">Ou copie o código PIX:</p>
                            <div class="relative">
                                <input type="text" readonly id="pix-payload" value="{{ $pix['payload'] }}" 
                                       class="w-full bg-white border border-teal-200 rounded-xl px-4 py-3 text-xs font-mono text-slate-600 pr-24 text-center">
                                <button type="button" onclick="copiarPix()" 
                                        class="absolute right-2 top-1/2 -translate-y-1/2 bg-teal-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-teal-700 transition-colors">
                                    Copiar
                                </button>
                            </div>
                            <p id="pix-copiado" class="text-xs text-emerald-600 font-bold mt-2 hidden">✓ Copiado!</p>
                        </div>

                        <p class="text-xs text-slate-500 mt-4">
                            Abra o app do seu banco, escolha pagar com PIX e escaneie o QR Code ou cole o código acima.
                        </p>
                    </div>

                    <script>
                        function copiarPix() {
                            const input = document.getElementById('pix-payload');
                            navigator.clipboard.writeText(input.value).then(() => {
                                document.getElementById('pix-copiado').classList.remove('hidden');
                                setTimeout(() => document.getElementById('pix-copiado').classList.add('hidden'), 3000);
                            });
                        }
                    </script>
                @endif

                <div class="space-y-4 text-slate-600 text-left relative z-10">
                    <h3 class="font-bold text-slate-800 text-xl border-b pb-2 mb-4">Próximos Passos</h3>
                    
                    <div class="flex gap-4 items-start">
                        <div class="bg-brand-primary/10 text-brand-primary font-black rounded-full h-8 w-8 flex items-center justify-center shrink-0 mt-0.5">1</div>
                        <div>
                            <strong class="text-slate-800">Contato Rápido</strong>
                            <p class="text-sm mt-1">Um especialista de nossa equipe entrará em contato com você via WhatsApp usando os dados cadastrados.</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-4 items-start">
                        <div class="bg-brand-primary/10 text-brand-primary font-black rounded-full h-8 w-8 flex items-center justify-center shrink-0 mt-0.5">2</div>
                        <div>
                            <strong class="text-slate-800">Cotação Final e Arte</strong>
                            <p class="text-sm mt-1">Acertaremos os detalhes de layout e confirmaremos os descontos, valores e data de entrega com você.</p>
                        </div>
                    </div>

                    <div class="flex gap-4 items-start pb-4">
                        <div class="bg-brand-primary/10 text-brand-primary font-black rounded-full h-8 w-8 flex items-center justify-center shrink-0 mt-0.5">3</div>
                        <div>
                            <strong class="text-slate-800">Mão na Massa</strong>
                            <p class="text-sm mt-1">Com sua aprovação, enviaremos o material imediatamente para as impressoras de última geração.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 border-t border-slate-100 pt-6 md:grid-cols-2 md:gap-4">
                    <a href="{{ route('site.pedido.acompanhar') }}" class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-800 p-4 font-bold text-white shadow hover:bg-slate-700 transition">
                        Acompanhar Pedido
                        <svg class="h-4 w-4 text-slate-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    
                    <a href="{{ route('site.inicio') }}" class="inline-flex w-full items-center justify-center text-sm font-bold text-slate-500 hover:text-brand-primary hover:bg-slate-50 bg-white border border-slate-200 p-4 rounded-xl transition">
                        Voltar para o Início
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.publico>

