{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:40
--}}
<x-layouts.app>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Equipamento: <span class="text-brand-primary">{{ $asset->nome }}</span></h1>
            <p class="text-slate-500 font-medium">Controle de manutenção e histórico operacional.</p>
        </div>
        <a href="{{ route('admin.inventory.assets.index') }}" class="text-xs font-black text-slate-400 hover:text-brand-primary transition uppercase tracking-widest border border-slate-200 px-4 py-2 rounded-xl bg-white shadow-sm">← Gestão de Ativos</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Dashboard do Ativo -->
        <div class="space-y-10">
            <!-- KPIs do Ativo -->
            <div class="rounded-3xl bg-white p-8 shadow-2xl border-4 border-slate-50">
                <div class="flex items-center justify-between mb-8">
                    <div class="p-4 rounded-2xl bg-brand-primary/10 text-brand-primary text-4xl">🛠️</div>
                    <span class="rounded-full px-4 py-1.5 text-xs font-black uppercase ring-2 tracking-widest
                        {{ $asset->status === 'ativo' ? 'bg-emerald-100 text-emerald-600 ring-emerald-200' : 'bg-orange-100 text-orange-600 ring-orange-200' }}">
                        {{ $asset->status }}
                    </span>
                </div>
                
                <div class="space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1 underline decoration-brand-primary decoration-2">Marca/Modelo</p>
                        <p class="text-xl font-black text-slate-800">{{ $asset->marca }} {{ $asset->modelo }}</p>
                        <p class="text-[10px] font-black text-slate-400 uppercase">SÉRIE: {{ $asset->numero_serie ?? 'N/D' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Aquisicao</p>
                            <p class="text-lg font-black text-slate-800">R$ {{ number_format($asset->valor_aquisicao, 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Valor Atual</p>
                            <p class="text-lg font-black text-emerald-600">R$ {{ number_format($asset->valor_atual, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulário de Manutenção -->
            <div class="rounded-3xl bg-slate-100 p-8 shadow-xl border-t-8 border-brand-primary">
                <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2 uppercase tracking-tight">
                    <span>⚙️</span> Nova Intervenção
                </h3>
                <form action="{{ route('admin.inventory.assets.maintenances.store', $asset) }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Tipo</label>
                        <select name="tipo" class="w-full rounded-xl border-slate-200 bg-white font-bold text-sm">
                            <option value="preventiva">Preventiva</option>
                            <option value="corretiva">Corretiva (Urgente)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Custo (R$)</label>
                        <input type="number" step="0.01" name="custo" required class="w-full rounded-xl border-slate-200 bg-white text-lg font-black text-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Data</label>
                        <input type="date" name="data" required value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-slate-200 bg-white text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Descrição</label>
                        <textarea name="descricao" required rows="3" class="w-full rounded-xl border-slate-200 bg-white text-sm" placeholder="O que foi feito? Peças trocadas?"></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-brand-primary py-4 text-center font-black text-white shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-xs">
                        Registrar Histórico
                    </button>
                </form>
            </div>
        </div>

        <!-- Timeline de Manutenções -->
        <div class="lg:col-span-2">
            <h3 class="text-2xl font-black text-brand-secondary mb-8 border-b-4 border-brand-primary/10 pb-4 inline-block">Histórico de Manutenções</h3>
            
            <div class="space-y-6">
                @forelse($asset->maintenances as $main)
                    <div class="rounded-3xl bg-white p-8 shadow-lg border border-slate-100 flex flex-col sm:flex-row justify-between gap-6 transition hover:shadow-2xl group">
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center justify-center min-w-[60px] p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-[10px] font-black text-slate-400 uppercase">{{ $main->data->format('M') }}</span>
                                <span class="text-2xl font-black text-slate-700">{{ $main->data->format('d') }}</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="rounded-full px-2.5 py-1 text-[8px] font-black uppercase ring-1 {{ $main->tipo === 'corretiva' ? 'bg-red-50 text-red-600 ring-red-200 animate-pulse' : 'bg-emerald-50 text-emerald-600 ring-emerald-200' }}">
                                        {{ $main->tipo }}
                                    </span>
                                    @if($main->responsavel)
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">🔧 Por: {{ $main->responsavel->nome }}</span>
                                    @endif
                                </div>
                                <p class="text-base font-bold text-slate-700 leading-snug">{{ $main->descricao }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:flex-col sm:items-end gap-3 border-t sm:border-0 pt-4 sm:pt-0">
                            <p class="text-2xl font-black text-slate-800">R$ {{ number_format($main->custo, 2, ',', '.') }}</p>
                            <span class="text-[8px] font-black text-brand-primary uppercase tracking-widest bg-brand-primary/5 px-2 py-0.5 rounded">REGISTRADO</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl bg-white p-24 text-center border-4 border-dashed border-slate-50">
                        <span class="text-7xl block mb-6 opacity-5 scale-150">📋</span>
                        <p class="text-xl font-black text-slate-300 italic uppercase tracking-tighter">Nenhuma intervenção registrada neste ativo.</p>
                        <p class="text-[10px] font-black text-slate-200 uppercase mt-2 tracking-widest">Inicie o histórico preventivo para garantir a produtividade.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
