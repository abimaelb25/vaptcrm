{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-04 20:09 -03:00
--}}
<x-layouts.app>
    <section class="max-w-md mx-auto mt-10 overflow-hidden rounded-2xl border bg-white shadow-xl">
        <div class="bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 px-6 py-8 text-center text-white">
            <img src="{{ asset('img/logo_horizontal.png') }}" alt="VaptCRM" class="mx-auto h-16 drop-shadow-md">
            <p class="mt-2 text-sm font-medium text-blue-100">Acesse o seu painel de controle</p>
        </div>
        
        <div class="p-6">

        @if($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('sucesso'))
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('sucesso') }}
            </div>
        @endif

        <form method="POST" action="{{ route('auth.autenticar') }}" class="mt-6 space-y-4">
            @csrf
            

            <div>
                <label class="text-sm font-semibold text-slate-700">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" />
            </div>
            <div>
                <div class="flex items-center justify-between">
                    <label class="text-sm font-semibold text-slate-700">Senha</label>
                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-blue-600 hover:text-blue-500">Esqueceu a senha?</a>
                </div>
                <input type="password" name="senha" required class="mt-1 w-full rounded-lg border px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" />
            </div>


            <button class="mt-6 w-full rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-3 font-bold text-white shadow-md transition-transform hover:scale-[1.02]">Acessar Conta</button>
        </form>
        </div>
    </section>
</x-layouts.app>
