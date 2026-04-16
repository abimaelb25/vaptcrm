{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-04 20:04 -03:00
--}}
<x-layouts.publico>
    <section class="rounded-2xl border bg-white p-6 shadow-sm">
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
        @endphp
        <h1 class="text-2xl font-bold">Pedido {{ $pedido->numero }}</h1>
        <p class="mt-2 text-sm text-slate-600">Status atual: <span class="font-semibold">{{ $statusAmigavel }}</span></p>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="rounded-lg border p-4">
                <h2 class="font-semibold">Resumo</h2>
                <p class="mt-2 text-sm">Cliente: {{ $pedido->cliente->nome }}</p>
                <p class="mt-1 text-sm">Total: R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
                <p class="mt-1 text-sm">Entrega: {{ str_replace('_', ' ', $pedido->tipo_entrega) }}</p>
            </div>
            <div class="rounded-lg border p-4">
                <h2 class="font-semibold">Pagamento</h2>
                @php($pagamento = $pedido->pagamentos->first())
                <p class="mt-2 text-sm">Situação: {{ $pagamento?->status ?? 'pendente' }}</p>
                <p class="mt-1 text-sm">Método: PIX</p>
            </div>
        </div>

        <a href="https://wa.me/5500000000000" target="_blank" rel="noopener" class="mt-6 inline-flex rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-900">Falar com atendimento</a>
    </section>
</x-layouts.publico>
