{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-06 00:00 -03:00
--}}
<x-layouts.publico titulo="Pedido Recebido - Gráfica Vapt Vupt">
    <section class="max-w-2xl mx-auto mt-10 md:mt-20 px-4">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden text-center border border-slate-100">
            <!-- Header animado -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 py-12 px-6 relative overflow-hidden">
                <!-- Icone Sucesso -->
                <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-white/20 backdrop-blur shadow-inner animate-pulse">
                    <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                
                <h1 class="mt-6 text-3xl md:text-5xl font-black text-white tracking-tight drop-shadow-md">
                    Obrigado!
                </h1>
                <p class="mt-4 text-emerald-50 text-lg max-w-lg mx-auto font-medium">
                    Recebemos o seu pedido e já estamos organizando tudo para você.
                </p>
            </div>

            <!-- Resumo e próximos passos -->
            <div class="p-8 md:p-12 space-y-8">
                <div class="bg-brand-secondary/5 rounded-2xl p-6 border border-brand-secondary/10">
                    <p class="text-sm text-slate-500 font-bold uppercase tracking-wider mb-2">Seu Protocolo Único</p>
                    <p class="text-4xl font-black text-brand-secondary tracking-widest font-mono">
                        {{ $pedido->numero }}
                    </p>
                    <p class="text-xs text-slate-400 mt-3 font-medium">Guarde esse número para acompanhar a produção.</p>
                </div>

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

                <div class="pt-6 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-4">
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

