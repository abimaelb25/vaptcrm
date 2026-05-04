{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-04 20:04 -03:00
--}}
<x-layouts.publico>
    <x-public.breadcrumb :items="[
        ['label' => 'Início', 'url' => \App\Support\PublicUrlHelper::inicio()],
        ['label' => 'Acompanhar Pedido'],
    ]" />

    <section class="public-card mx-auto max-w-xl p-5 sm:p-6 md:p-8">
        <div class="mb-6 border-b border-slate-100 pb-5">
            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-brand-primary">Rastreamento</p>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-brand-secondary sm:text-3xl">Acompanhar pedido</h1>
            <p class="mt-2 text-sm text-slate-600">Informe o código e o telefone ou e-mail usado no pedido.</p>
        </div>

        <form method="POST" action="{{ route('site.pedido.consultar') }}" class="space-y-4">
            @csrf
            <div>
                <label for="codigo" class="label-form">Código do pedido</label>
                <input id="codigo" name="codigo" value="{{ old('codigo') }}" required class="input-modern" placeholder="Ex: VP-26-1001" />
            </div>
            <div>
                <label for="contato" class="label-form">Telefone ou e-mail</label>
                <input id="contato" name="contato" value="{{ old('contato') }}" required class="input-modern" placeholder="(11) 99999-9999 ou voce@exemplo.com" />
            </div>

            <button class="public-touch mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-secondary px-4 py-3 text-sm font-black uppercase tracking-wider text-white shadow-md transition hover:brightness-110">
                Consultar pedido
                <x-icon name="arrow-right" class="h-4 w-4" />
            </button>
        </form>
    </section>
</x-layouts.publico>

