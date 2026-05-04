{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 17/04/2026
Descrição: Modal de alerta de limite atingido com CTA para upgrade.
--}}
@if(session('limite_atingido'))
<div x-data="{ open: true }" x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        
        {{-- Banner de Fundo --}}
        <div class="h-24 bg-gradient-to-br from-brand-primary to-orange-600 flex items-center justify-center">
            <div class="bg-white/20 p-4 rounded-full backdrop-blur-md">
                <x-icon name="exclamation-triangle" class="w-10 h-10 text-white" />
            </div>
        </div>

        <div class="p-8 text-center">
            <h3 class="text-xl font-black text-slate-800 mb-2 uppercase tracking-tight">Limite Atingido</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-8">
                {{ session('limite_atingido') }}
            </p>

            <div class="flex flex-col gap-3">
                <a href="{{ route('admin.billing.index') }}" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-brand-primary text-white font-bold rounded-2xl shadow-lg shadow-brand-primary/30 hover:bg-orange-600 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                    <x-icon name="identification" class="w-5 h-5" />
                    Fazer Upgrade Agora
                </a>
                
                <button @click="open = false" class="w-full py-3 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">
                    Decidir depois
                </button>
            </div>
        </div>

        {{-- Detalhe lateral --}}
        <div class="absolute top-0 right-0 p-4">
             <button @click="open = false" class="text-white/50 hover:text-white transition-colors">
                 <x-icon name="x-mark" class="w-6 h-6" />
             </button>
        </div>
    </div>
</div>
@endif
