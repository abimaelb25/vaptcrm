{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-04 20:04 -03:00
--}}
<x-layouts.publico>
    <x-public.breadcrumb :items="[
        ['label' => 'Início', 'url' => \App\Support\PublicUrlHelper::inicio()],
        ['label' => 'Acompanhar Pedido', 'url' => route('site.pedido.acompanhar')],
        ['label' => 'Resultado'],
    ]" />

    <section class="public-card p-5 sm:p-6 md:p-8">
        @php
            $lbls = [
                'rascunho' => 'Aguardando Pagamento Inicial',
                'aguardando_aprovacao' => 'Aguardando Aprovação e Triagem',
                'aprovado' => 'Pedido Aprovado',
                'em_producao' => 'Produzindo nas Máquinas',
                'pronto' => 'Finalizado e Pronto para Retirada',
                'entregue' => 'Entrega Feita ao Cliente',
                'cancelado' => 'Pedido Cancelado'
            ];
            $statusAmigavel = $lbls[$pedido->status] ?? ucfirst(str_replace('_', ' ', $pedido->status));
            $codigoPedido = $pedido->codigo_pedido ?? $pedido->numero;
            $pagamento = $pedido->pagamentos->first();
        @endphp

        <div class="mb-6 border-b border-slate-100 pb-5">
            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-brand-primary">Andamento do pedido</p>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-brand-secondary sm:text-3xl">Pedido {{ $codigoPedido }}</h1>
            <p class="mt-2 text-sm text-slate-600">Status atual: <span class="font-black text-brand-secondary">{{ $statusAmigavel }}</span></p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-500">Resumo</h2>
                <p class="mt-2 text-sm">Cliente: {{ $pedido->cliente->nome ?? 'Não informado' }}</p>
                <p class="mt-1 text-sm">Total: R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
                <p class="mt-1 text-sm">Entrega: {{ str_replace('_', ' ', $pedido->tipo_entrega ?? 'não informado') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-500">Pagamento</h2>
                <p class="mt-2 text-sm">Situação: {{ $pagamento?->status ?? 'pendente' }}</p>
                <p class="mt-1 text-sm">Método: {{ $pagamento?->metodo ?? 'a confirmar' }}</p>
            </div>
        </div>

        @php
            $whatsClean = preg_replace('/[^0-9]/', '', $configSite['empresa_whatsapp'] ?? '5575999279354');
            $whatsMessage = 'Olá! Gostaria de acompanhar o pedido ' . $codigoPedido;
        @endphp

        <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <a href="{{ route('site.pedido.acompanhar') }}" class="public-touch inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50">
                Nova consulta
            </a>
            <a href="https://wa.me/{{ $whatsClean }}?text={{ urlencode($whatsMessage) }}" target="_blank" rel="noopener" class="public-touch inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-600">
                Falar com atendimento
            </a>
        </div>
    </section>
</x-layouts.publico>
