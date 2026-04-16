{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:50
--}}
<x-layouts.app>
    <div class="mb-12 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tighter">Fluxo <span class="text-brand-primary text-2xl">Operacional</span></h1>
            <p class="text-slate-500 font-medium tracking-widest uppercase text-[10px]">Configuração global de etapas de produção.</p>
        </div>
        <a href="{{ route('admin.ops.production.index') }}" class="text-xs font-black text-slate-400 hover:text-brand-primary transition uppercase tracking-widest border border-slate-200 px-4 py-2 rounded-xl bg-white shadow-sm">← Chão de Fábrica</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Listagem de Etapas -->
        <div class="space-y-6">
            <h2 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                <span>⚡</span> Etapas de Processo
            </h2>

            <div class="rounded-3xl bg-white shadow-xl border border-slate-100 overflow-hidden">
                <div class="divide-y divide-slate-50">
                    @forelse($steps as $step)
                        <div class="p-6 flex items-center justify-between group hover:bg-slate-50 transition duration-300">
                            <div class="flex items-center gap-4">
                                <span class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black text-sm shadow-lg group-hover:bg-brand-primary transition-all duration-500">
                                    {{ $step->ordem }}
                                </span>
                                <div>
                                    <h4 class="text-base font-black text-slate-800 tracking-tight">{{ $step->nome }}</h4>
                                    <span class="text-[10px] font-black uppercase text-slate-400">Ativo na Operação</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 opacity-20 group-hover:opacity-100 transition duration-300">
                                <button class="p-2 text-slate-400 hover:text-brand-primary" title="Subir Ordem">🔼</button>
                                <button class="p-2 text-slate-400 hover:text-brand-primary" title="Descer Ordem">🔽</button>
                            </div>
                        </div>
                    @empty
                        <div class="p-16 text-center italic text-slate-400 font-medium">Nenhuma etapa definida. Cada pedido gerará uma OP sem itens de controle.</div>
                    @endforelse
                </div>
            </div>
            
            <p class="text-[10px] font-black text-slate-300 uppercase italic tracking-widest">💡 Toda nova Ordem de Produção (OP) iniciará com esta sequência de etapas pré-definida.</p>
        </div>

        <!-- Formulário Adição -->
        <div class="space-y-8">
            <div class="rounded-[2.5rem] bg-brand-secondary p-12 shadow-2xl relative overflow-hidden group">
                <span class="absolute -right-10 -bottom-10 text-[12rem] text-white opacity-5 transform rotate-12 group-hover:scale-125 transition duration-1000">🏗️</span>
                
                <h3 class="text-2xl font-black text-white mb-8 tracking-tighter">Configurar Nova Estação de Trabalho</h3>
                
                <form action="{{ route('admin.ops.production.step.store') }}" method="POST" class="space-y-6 relative">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 underline decoration-brand-primary decoration-4">Nomenclatura da Etapa</label>
                        <input type="text" name="nome" required placeholder="Ex: Impressão UV, Dobra Maxtronic, Logística" class="w-full rounded-2xl border-0 bg-white/10 ring-1 ring-white/20 focus:ring-2 focus:ring-brand-primary text-white text-lg font-bold px-6 py-4 placeholder:text-white/30 transition-all duration-300">
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full rounded-2xl bg-brand-primary py-5 text-center font-black text-white shadow-xl hover:-translate-y-1 transition duration-500 uppercase tracking-widest text-sm">
                            Integrar Estação ao Processo
                        </button>
                        <p class="mt-4 text-center text-[10px] font-black text-white/40 uppercase tracking-widest">Controles de Operação Industrial • VaptCRM</p>
                    </div>
                </form>
            </div>

            <div class="rounded-3xl bg-white p-8 border border-slate-100 shadow-sm flex items-start gap-4 transform -rotate-1 relative group hover:rotate-0 transition duration-500">
                <span class="text-3xl">🧩</span>
                <div>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Rastreabilidade Operacional</h4>
                    <p class="text-xs font-medium text-slate-500 leading-relaxed italic italic mt-2">Personalize o fluxo do bônus de produção. A gráfica rápida depende de processos ágeis e controle de paradas. Defina suas macro-etapas para obter indicadores reais de produtividade.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
