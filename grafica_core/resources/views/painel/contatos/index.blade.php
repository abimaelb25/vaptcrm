{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-04 20:14 -03:00
--}}
<x-layouts.app>
    <h1 class="text-2xl font-bold">Contatos e atendimentos</h1>

    <form method="POST" action="{{ route('admin.sales.contatos.store') }}" class="mt-6 grid gap-3 rounded-xl border bg-white p-4 md:grid-cols-2">
        @csrf
        <input name="cliente_id" placeholder="ID do cliente" class="rounded border px-3 py-2" required>
        <input name="pedido_id" placeholder="ID do pedido (opcional)" class="rounded border px-3 py-2">
        <select name="tipo_contato" class="rounded border px-3 py-2" required>
            <option value="whatsapp">WhatsApp</option>
            <option value="ligacao">Ligação</option>
            <option value="email">E-mail</option>
            <option value="presencial">Presencial</option>
        </select>
        <input name="usuario_id" placeholder="ID do responsável" class="rounded border px-3 py-2" required>
        <textarea name="resumo" placeholder="Resumo da conversa" class="rounded border px-3 py-2 md:col-span-2" required></textarea>
        <input name="proximo_passo" placeholder="Próximo passo" class="rounded border px-3 py-2">
        <input type="date" name="data_retorno" class="rounded border px-3 py-2">
        <button class="rounded bg-blue-700 px-4 py-2 text-sm font-semibold text-white md:col-span-2">Registrar contato</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-xl border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-left">
                <tr>
                    <th class="px-4 py-2">Cliente</th>
                    <th class="px-4 py-2">Tipo</th>
                    <th class="px-4 py-2">Resumo</th>
                    <th class="px-4 py-2">Retorno</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contatos as $contato)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $contato->cliente->nome ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $contato->tipo_contato }}</td>
                        <td class="px-4 py-2">{{ $contato->resumo }}</td>
                        <td class="px-4 py-2">{{ $contato->data_retorno }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.app>
