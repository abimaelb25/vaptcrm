{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-04 20:04 -03:00
--}}
<x-layouts.publico>
    <section class="mx-auto max-w-xl rounded-2xl border bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold">Acompanhar pedido</h1>
        <p class="mt-2 text-sm text-slate-600">Informe o código do pedido e telefone ou e-mail cadastrado.</p>

        <form method="POST" action="{{ route('site.pedido.consultar') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="codigo" class="text-sm font-medium">Código do pedido</label>
                <input id="codigo" name="codigo" value="{{ old('codigo') }}" required class="mt-1 w-full rounded-lg border px-3 py-2" />
            </div>
            <div>
                <label for="contato" class="text-sm font-medium">Telefone ou e-mail</label>
                <input id="contato" name="contato" value="{{ old('contato') }}" required class="mt-1 w-full rounded-lg border px-3 py-2" />
            </div>
            <button class="w-full rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white">Consultar</button>
        </form>
    </section>
</x-layouts.publico>

