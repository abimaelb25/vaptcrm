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
            
            <!-- Verificação Anti-Bot (Honeypot Invisível) -->
            <div style="display: none; visibility: hidden; position: absolute; left: -9999px;" aria-hidden="true">
                <label for="verificacao_humana">Não preencha este campo se você for humano</label>
                <input type="text" name="verificacao_humana" id="verificacao_humana" tabindex="-1" autocomplete="off" />
            </div>

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

            <!-- Desafio Anti-Bot Matemático -->
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                <label class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Verificação Anti-Bot: Quanto é {{ $num1 }} + {{ $num2 }}?
                </label>
                <input type="number" name="captcha" required placeholder="Digite o resultado" class="mt-2 w-full rounded-lg border px-4 py-2 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" />
            </div>

            <button class="mt-6 w-full rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-3 font-bold text-white shadow-md transition-transform hover:scale-[1.02]">Acessar Conta</button>
        </form>
        </div>
    </section>
</x-layouts.app>
