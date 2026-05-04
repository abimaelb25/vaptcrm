{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 17/04/2026
Descrição: Formulário de redefinição de senha com token.
--}}
<x-layouts.app titulo="Redefinir Senha - VaptCRM">
    <section class="max-w-md mx-auto mt-10 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl animate-fade-in-up">
        
        <div class="bg-gradient-to-br from-brand-secondary to-slate-800 px-6 py-10 text-center text-white relative">
             <div class="absolute top-0 right-0 p-4 opacity-10">
                 <x-icon name="key" class="w-20 h-20" />
             </div>

            <img src="{{ asset('img/logo_horizontal.png') }}" alt="VaptCRM" class="mx-auto h-12 drop-shadow-md relative z-10">
            <h2 class="mt-6 text-2xl font-black tracking-tight">Nova Senha</h2>
            <p class="mt-2 text-sm font-medium text-slate-300">Crie uma senha forte e segura para o seu acesso</p>
        </div>
        
        <div class="p-8">

            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-status-error/20 bg-status-error/5 p-4 text-sm text-status-error animate-shake">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <x-icon name="exclamation-circle" class="w-5 h-5" />
                        <span>Ops! Verifique os dados:</span>
                    </div>
                    <ul class="list-disc list-inside opacity-80">
                         @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                         @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">E-mail de Acesso</label>
                    <div class="relative group">
                        <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3.5 text-slate-500 font-bold focus:outline-none transition-all cursor-not-allowed" />
                        <div class="absolute inset-y-0 right-4 flex items-center text-slate-300">
                             <x-icon name="lock-closed" class="w-5 h-5" />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nova Senha</label>
                    <input type="password" name="password" required autofocus placeholder="No mínimo 8 caracteres" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 transition-all focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/10" />
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Confirme a Nova Senha</label>
                    <input type="password" name="password_confirmation" required placeholder="Repita a nova senha" class="w-full rounded-2xl border border-slate-200 px-5 py-3.5 transition-all focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/10" />
                </div>

                <button type="submit" class="w-full rounded-2xl bg-brand-primary py-4 font-black text-white shadow-xl shadow-brand-primary/20 transition-all hover:scale-[1.02] hover:bg-orange-600 active:scale-95 flex items-center justify-center gap-2">
                    <x-icon name="check-circle" class="w-5 h-5" />
                    Salvar e Entrar no Painel
                </button>
                
                <div class="pt-4 text-center">
                    <a href="{{ route('login') }}" class="text-xs font-black text-slate-400 hover:text-brand-secondary transition-colors uppercase tracking-widest">
                        Cancelar operação
                    </a>
                </div>
            </form>
        </div>
    </section>
</x-layouts.app>
