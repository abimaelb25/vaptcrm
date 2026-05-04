<x-layouts.app>
    <div class="mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Notas <span class="text-brand-primary">NF-e XML</span></h1>
            <p class="text-slate-500 font-medium">Historico de importacoes desta loja. Reabra uma nota confirmada para corrigir mapeamentos.</p>
        </div>
        <a href="{{ route('admin.inventory.nfe-importacao.create') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-brand-primary px-6 py-3 text-xs font-black text-white shadow hover:-translate-y-0.5 transition uppercase tracking-widest">
            + Nova importacao
        </a>
    </div>

    @if(session('sucesso'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">
            {{ session('sucesso') }}
        </div>
    @endif

    @if($importacoes->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
            <p class="text-slate-400 font-bold mb-4">Nenhuma importacao encontrada.</p>
            <a href="{{ route('admin.inventory.nfe-importacao.create') }}"
               class="inline-flex rounded-2xl bg-brand-primary px-6 py-3 text-xs font-black text-white uppercase tracking-widest">
                Importar primeira NF-e
            </a>
        </div>
    @else
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-4 py-3 text-left text-[10px] font-black uppercase text-slate-400">Nota / Serie</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black uppercase text-slate-400">Fornecedor</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black uppercase text-slate-400">Data emissao</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black uppercase text-slate-400">Valor total</th>
                        <th class="px-4 py-3 text-center text-[10px] font-black uppercase text-slate-400">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black uppercase text-slate-400">Importada em</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($importacoes as $importacao)
                        @php
                            $payload = $importacao->payload_json ?? [];
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-bold text-slate-700">
                                {{ $payload['numero'] ?? '-' }} / {{ $payload['serie'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $payload['fornecedor']['nome'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">
                                {{ isset($payload['data_emissao']) ? \Carbon\Carbon::parse($payload['data_emissao'])->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-700">
                                R$ {{ number_format((float) ($payload['valor_total'] ?? 0), 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($importacao->status === 'confirmada')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-black uppercase text-emerald-700">
                                        Confirmada
                                    </span>
                                @elseif($importacao->status === 'preview')
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-black uppercase text-amber-700">
                                        Pendente
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-black uppercase text-slate-500">
                                        {{ $importacao->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs">
                                @if($importacao->status === 'confirmada' && $importacao->confirmada_em)
                                    {{ $importacao->confirmada_em->format('d/m/Y H:i') }}
                                @else
                                    {{ $importacao->created_at->format('d/m/Y H:i') }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.inventory.nfe-importacao.show', $importacao) }}"
                                   class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-brand-primary hover:text-white transition">
                                    @if($importacao->status === 'confirmada')
                                        Ver / Reabrir
                                    @else
                                        Continuar
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($importacoes->hasPages())
            <div class="mt-6">
                {{ $importacoes->links() }}
            </div>
        @endif
    @endif
</x-layouts.app>
