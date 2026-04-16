{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Criado em: 2026-04-16
--}}
<x-layouts.app>
    <section class="max-w-md mx-auto mt-10 overflow-hidden rounded-2xl border bg-white shadow-xl">
        <div class="bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 px-6 py-8 text-center text-white">
            <img src="{{ asset('img/logo_horizontal.png') }}" alt="VaptCRM" class="mx-auto h-16 drop-shadow-md">
            <h2 class="mt-4 text-xl font-bold">Recuperação de Senha</h2>
            <p class="mt-2 text-sm font-medium text-blue-100">Enviaremos instruções para o seu e-mail</p>
        </div>
        
        <div class="p-6">

        @if($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('sucesso'))
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                <div class="flex items-start gap-2">
                    <svg class="h-5 w-5 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('sucesso') }}</span>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            
            <div>
                <label class="text-sm font-semibold text-slate-700">E-mail Cadastrado</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Digite seu e-mail de acesso" class="mt-1 w-full rounded-lg border px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" />
            </div>

            <button type="submit" class="mt-6 w-full rounded-lg bg-gradient-to-r from-blue-600 to-blue-500 px-4 py-3 font-bold text-white shadow-md transition-transform hover:scale-[1.02]">Enviar Instruções</button>
            
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700 transition-colors flex items-center justify-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar para o Login
                </a>
            </div>
        </form>
        </div>
    </section>
</x-layouts.app>
