{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 17/04/2026
Descrição: Página de solicitação de recuperação de senha.
--}}
<x-layouts.app titulo="Recuperar Senha - VaptCRM">
    <section class="max-w-md mx-auto mt-10 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl animate-fade-in-up">
        
        <div class="bg-gradient-to-br from-brand-secondary to-slate-800 px-6 py-10 text-center text-white relative">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                 <x-icon name="envelope" class="w-20 h-20" />
            </div>

            <img src="{{ asset('img/logo_horizontal.png') }}" alt="VaptCRM" class="mx-auto h-12 drop-shadow-md relative z-10">
            <h2 class="mt-6 text-2xl font-black tracking-tight">Esqueceu a senha?</h2>
            <p class="mt-2 text-sm font-medium text-slate-300">Enviaremos as instruções para o seu e-mail cadastrado</p>
        </div>
        
        <div class="p-8">

            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-status-error/20 bg-status-error/5 p-4 text-sm text-status-error animate-shake">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <x-icon name="exclamation-circle" class="w-5 h-5" />
                        <span>Atenção:</span>
                    </div>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if(session('sucesso'))
                <div class="mb-6 rounded-2xl border border-status-success/20 bg-status-success/5 p-5 text-sm text-status-success animate-fade-in">
                    <div class="flex items-start gap-4">
                        <div class="bg-status-success/20 p-2 rounded-full">
                            <x-icon name="check" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="font-black uppercase tracking-widest text-[10px] mb-1">Sucesso!</p>
                            <p class="font-medium leading-relaxed">{{ session('sucesso') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Seu E-mail</label>
                    <div class="relative group">
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="Digite seu e-mail de acesso" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 transition-all focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/10" />
                        <div class="absolute inset-y-0 right-4 flex items-center text-slate-300 group-focus-within:text-brand-primary transition-colors">
                             <x-icon name="at-symbol" class="w-5 h-5" />
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full rounded-2xl bg-brand-primary py-4 font-black text-white shadow-xl shadow-brand-primary/20 transition-all hover:scale-[1.02] hover:bg-orange-600 active:scale-95 flex items-center justify-center gap-2">
                    Enviar Instruções
                    <x-icon name="arrow-right" class="w-5 h-5" />
                </button>
                
                <div class="pt-4 text-center">
                    <a href="{{ route('login') }}" class="text-xs font-black text-slate-400 hover:text-brand-secondary transition-colors flex items-center justify-center gap-2 uppercase tracking-widest">
                        <x-icon name="arrow-left" class="w-4 h-4" />
                        Voltar para o Login
                    </a>
                </div>
            </form>
        </div>
    </section>
</x-layouts.app>
