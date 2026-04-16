{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:30
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Meus <span class="text-brand-primary">Equipamentos</span></h1>
            <p class="text-slate-500 font-medium tracking-tight">Gestão de ativos, depreciação e histórico de máquinas.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
            <a href="{{ route('admin.inventory.assets.create') }}" class="rounded-xl bg-slate-800 px-6 py-2.5 text-sm font-bold text-white shadow-xl transition hover:bg-slate-700 flex items-center gap-2">
                <span>➕</span> Novo Equipamento
            </a>
        </div>
    </div>

    <!-- Tabela de Ativos -->
    <div class="rounded-3xl bg-white border border-slate-100 shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Maquinário</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Tipo / Setor</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Aquisição</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Depreciação</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Valor Atual</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/50 transition duration-300 group">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <span class="p-2.5 rounded-xl bg-brand-primary/10 text-brand-primary text-xl shadow-sm group-hover:bg-brand-primary group-hover:text-white transition-all duration-500">
                                        {{ $asset->tipo === 'impressora' ? '🖨️' : ($asset->tipo === 'plotter' ? '🔌' : '⚙️') }}
                                    </span>
                                    <div>
                                        <p class="text-base font-black text-slate-800 leading-tight mb-0.5">{{ $asset->nome }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">{{ $asset->marca }} {{ $asset->modelo }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="rounded-xl px-3 py-1 bg-slate-100 text-[10px] font-black text-slate-600 uppercase">{{ $asset->setor ?? 'Geral' }}</span>
                            </td>
                            <td class="px-6 py-5 text-right font-bold text-slate-500 text-sm italic">
                                R$ {{ number_format($asset->valor_aquisicao, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-5 text-right text-red-500 font-black text-sm">
                                - R$ {{ number_format($asset->depreciacao_acumulada, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-5 text-right font-black text-slate-800 text-lg">
                                R$ {{ number_format($asset->valor_atual, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest
                                    {{ $asset->status === 'ativo' ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200' : ($asset->status === 'manutencao' ? 'bg-orange-50 text-orange-600 ring-1 ring-orange-200' : 'bg-red-50 text-red-600 ring-1 ring-red-200') }}">
                                    {{ $asset->status }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <a href="{{ route('admin.inventory.assets.show', $asset) }}" class="p-2.5 text-slate-400 hover:text-brand-primary transition inline-flex items-center bg-slate-50 rounded-xl hover:bg-white hover:shadow-md" title="Ver Detalhes e Manutenção">
                                    🔍
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-32 text-center">
                                <span class="text-8xl block mb-6 opacity-10 grayscale">🛠️</span>
                                <p class="text-xl font-black text-slate-400 italic">Nenhum ativo imobilizado registrado.</p>
                                <p class="text-sm text-slate-300 font-bold uppercase tracking-widest mt-2">Cadastre suas máquinas para controle de custo e depreciação.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $assets->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
