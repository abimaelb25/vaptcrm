{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-10
--}}
<x-layouts.app>
    <div class="mb-8">
        <a href="{{ route('admin.sales.cupons.index') }}" class="text-slate-400 hover:text-brand-primary font-bold text-sm flex items-center gap-2 mb-2 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Voltar para Listagem
        </a>
        <h1 class="text-3xl font-black text-brand-secondary">Novo Cupom de Desconto</h1>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-100 p-4 font-bold text-rose-700 animate-shake">
            <p class="mb-2">⚠️ Por favor, corrija os seguintes erros:</p>
            <ul class="text-sm font-medium list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.sales.cupons.store') }}" method="POST" class="max-w-3xl">
        @csrf
        <div class="rounded-3xl bg-white shadow-2xl border border-slate-100 p-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block mb-2 text-xs font-black uppercase text-slate-400 tracking-widest">Código do Cupom</label>
                    <input type="text" name="codigo" value="{{ old('codigo') }}" required placeholder="EX: VERAO2026" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 font-mono font-black text-brand-secondary focus:outline-none focus:ring-4 focus:ring-brand-primary/20 transition-all uppercase">
                </div>
                
                <div>
                    <label class="block mb-2 text-xs font-black uppercase text-slate-400 tracking-widest">Tipo de Desconto</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="has-[:checked]:bg-brand-secondary has-[:checked]:text-white flex items-center justify-center p-4 rounded-2xl border border-slate-200 cursor-pointer transition-all font-black text-xs uppercase tracking-tighter text-slate-500 bg-white">
                            <input type="radio" name="tipo" value="percentual" class="hidden" {{ old('tipo', 'percentual') === 'percentual' ? 'checked' : '' }}>
                            Percentual (%)
                        </label>
                        <label class="has-[:checked]:bg-brand-secondary has-[:checked]:text-white flex items-center justify-center p-4 rounded-2xl border border-slate-200 cursor-pointer transition-all font-black text-xs uppercase tracking-tighter text-slate-500 bg-white">
                            <input type="radio" name="tipo" value="valor_fixo" class="hidden" {{ old('tipo') === 'valor_fixo' ? 'checked' : '' }}>
                            Fixo (R$)
                        </label>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block mb-2 text-xs font-black uppercase text-slate-400 tracking-widest">Valor do Benefício</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="valor" value="{{ old('valor') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 font-black text-slate-700 focus:outline-none focus:ring-4 focus:ring-brand-primary/20 transition-all">
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-xs font-black uppercase text-slate-400 tracking-widest">Limite de Uso (Total)</label>
                    <input type="number" name="limite_uso" value="{{ old('limite_uso') }}" placeholder="∞ (Deixe vazio para ilimitado)" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 font-black text-slate-700 focus:outline-none focus:ring-4 focus:ring-brand-primary/20 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block mb-2 text-xs font-black uppercase text-slate-400 tracking-widest">Início da Validade</label>
                    <input type="datetime-local" name="validade_inicio" value="{{ old('validade_inicio') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 font-bold text-slate-600 focus:outline-none focus:ring-4 focus:ring-brand-primary/20 transition-all">
                </div>

                <div>
                    <label class="block mb-2 text-xs font-black uppercase text-slate-400 tracking-widest">Fim da Validade</label>
                    <input type="datetime-local" name="validade_fim" value="{{ old('validade_fim') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 font-bold text-slate-600 focus:outline-none focus:ring-4 focus:ring-brand-primary/20 transition-all">
                </div>
            </div>

            <div class="pt-4 flex items-center gap-4">
                <input type="hidden" name="ativo" value="0">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="ativo" value="1" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                    <span class="ms-3 text-sm font-black uppercase text-slate-400 tracking-widest">Cupom Ativo</span>
                </label>
            </div>

            <div class="border-t border-slate-100 pt-8 flex justify-end">
                <button type="submit" class="rounded-2xl bg-brand-secondary px-12 py-5 text-sm font-black uppercase tracking-widest text-white shadow-xl transition-all hover:bg-slate-900 active:scale-95">
                    Criar Cupom Agora
                </button>
            </div>
        </div>
    </form>
</x-layouts.app>
