{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-10
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Cupons de Desconto</h1>
            <p class="text-slate-500 font-medium font-serif italic">Gerencie benefícios e incentivos de venda</p>
        </div>
        
        <a href="{{ route('admin.sales.cupons.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-primary px-6 py-3 text-sm font-black text-white shadow-lg transition-all hover:scale-105 active:scale-95">
            <span class="text-xl">+</span> Novo Cupom
        </a>
    </div>

    @if(session('sucesso'))
        <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-100 p-4 text-emerald-700 font-bold flex items-center gap-3 animate-slide-in">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white text-sm">✓</span>
            {{ session('sucesso') }}
        </div>
    @endif

    <div class="rounded-3xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <th class="px-8 py-5">Código</th>
                        <th class="px-6 py-5">Benefício</th>
                        <th class="px-6 py-5">Validade</th>
                        <th class="px-6 py-5">Uso / Limite</th>
                        <th class="px-6 py-5">Status</th>
                        <th class="px-8 py-5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm font-medium">
                    @forelse($cupons as $cupom)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-5">
                                <span class="bg-slate-100 px-3 py-1.5 rounded-lg font-mono font-black text-brand-secondary border border-slate-200">
                                    {{ $cupom->codigo }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                @if($cupom->tipo === 'percentual')
                                    <span class="text-emerald-600 font-black text-lg">{{ number_format($cupom->valor, 0) }}%</span>
                                    <span class="text-[10px] text-slate-400 uppercase ml-1">OFF</span>
                                @else
                                    <span class="text-blue-600 font-black text-lg">R$ {{ number_format($cupom->valor, 2, ',', '.') }}</span>
                                    <span class="text-[10px] text-slate-400 uppercase ml-1">FIXO</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                @if($cupom->validade_fim)
                                    <div class="flex flex-col">
                                        <span class="text-slate-600">{{ $cupom->validade_fim->format('d/m/Y') }}</span>
                                        <span class="text-[10px] {{ $cupom->validade_fim->isPast() ? 'text-rose-400' : 'text-slate-400' }}">
                                            {{ $cupom->validade_fim->isPast() ? 'Expirado' : 'Expira em ' . $cupom->validade_fim->diffForHumans() }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-slate-300 italic">Sem expiração</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-slate-700">{{ $cupom->usos_atuais }}</span>
                                    <span class="text-slate-300">/</span>
                                    <span class="text-slate-400">{{ $cupom->limite_uso ?? '∞' }}</span>
                                </div>
                                <div class="mt-1.5 h-1 w-full max-w-[100px] rounded-full bg-slate-100 overflow-hidden">
                                    @php 
                                        $porc = $cupom->limite_uso ? ($cupom->usos_atuais / $cupom->limite_uso) * 100 : 0;
                                    @endphp
                                    <div class="h-full bg-brand-primary" style="width: {{ min(100, $porc) }}%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                @if($cupom->isValid())
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-[10px] font-black uppercase text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Ativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase text-slate-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span> Inativo
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.sales.cupons.edit', $cupom) }}" class="p-2 text-slate-400 hover:text-brand-primary transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </a>
                                    <form action="{{ route('admin.sales.cupons.destroy', $cupom) }}" method="POST" onsubmit="return confirm('Apagar este cupom?')">
                                        @csrf @method('DELETE')
                                        <button class="p-2 text-slate-400 hover:text-rose-500 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-20 text-center">
                                <p class="text-slate-400 font-bold italic">Nenhum cupom cadastrado ainda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cupons->hasPages())
            <div class="bg-slate-50/50 p-6 border-t border-slate-50">
                {{ $cupons->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
