@php
/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:55
| Descrição: Perfil Detalhado do Pedido/Orçamento (UX Premium).
*/
@endphp
<x-layouts.app titulo="Pedido #{{ $pedido->numero }} - Vapt CRM">
    <div class="mb-4">
        <a href="{{ route('admin.sales.pedidos.index') }}" class="text-xs font-black text-slate-400 hover:text-brand-primary mb-2 inline-flex items-center gap-2 uppercase tracking-tighter transition-colors">
            <i class="fas fa-arrow-left text-[10px]"></i> Voltar ao Quadro
        </a>
    </div>

    <!-- Header do Pedido -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm overflow-hidden relative">
        <div class="flex items-center gap-6">
            <div class="h-16 w-16 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center text-3xl">
                <i class="fas {{ $statusMap[$pedido->status]['icon'] ?? 'fa-receipt' }}"></i>
            </div>
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-3xl font-black text-brand-secondary tracking-tight">{{ $pedido->numero }}</h1>
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $statusMap[$pedido->status]['color'] ?? 'border-slate-300' }}">
                        {{ $statusMap[$pedido->status]['label'] ?? $pedido->status }}
                    </span>
                </div>
                <p class="text-slate-500 font-medium">
                    Cliente: <span class="font-bold text-slate-800">{{ $pedido->cliente->nome }}</span> &bull; 
                    Data: <span class="font-bold text-slate-700">{{ $pedido->created_at->format('d/m/Y H:i') }}</span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button onclick="window.print()" class="btn bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-2xl font-black text-xs uppercase transition-all">
                <i class="fas fa-print mr-1"></i> Imprimir O.S.
            </button>
            
            @if($pedido->status === \App\Models\Pedido::STATUS_RASCUNHO)
                <form action="{{ route('admin.sales.pedidos.status', $pedido->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ \App\Models\Pedido::STATUS_AGUARDANDO }}">
                    <button type="submit" class="btn bg-brand-primary text-white px-6 py-3 rounded-2xl font-black text-xs uppercase shadow-xl shadow-brand-primary/20 hover:scale-105 active:scale-95 transition-all">
                        Converter em Venda
                    </button>
                </form>
            @endif

            <button onclick="document.getElementById('modalStatus').classList.remove('hidden')" class="btn bg-brand-secondary text-white px-6 py-3 rounded-2xl font-black text-xs uppercase shadow-xl shadow-brand-secondary/20 hover:scale-105 transition-all">
                Mudar Status
            </button>
        </div>
        
        <!-- Decor Glassmorphism -->
        <div class="absolute -top-10 -right-10 h-32 w-32 bg-brand-primary/5 rounded-full blur-2xl"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8" x-data="{ tab: 'itens' }">
        
        <!-- Sidebar: Resumo Financeiro -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900 rounded-3xl p-8 text-white shadow-2xl relative overflow-hidden">
                <h3 class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-6">Resumo Financeiro</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-white/60">
                         <span class="text-xs font-bold uppercase">Subtotal</span>
                         <span class="font-black">R$ {{ number_format($pedido->subtotal, 2, ',', '.') }}</span>
                    </div>
                    @if($pedido->valor_frete > 0)
                    <div class="flex justify-between items-center text-white/60">
                         <span class="text-xs font-bold uppercase">Frete (+)</span>
                         <span class="font-black">R$ {{ number_format($pedido->valor_frete, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($pedido->desconto > 0 || $pedido->valor_desconto_cupom > 0)
                    <div class="flex justify-between items-center text-emerald-400">
                         <span class="text-xs font-bold uppercase">Desconto (-)</span>
                         <span class="font-black">- R$ {{ number_format($pedido->desconto + $pedido->valor_desconto_cupom, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    
                    <div class="pt-6 border-t border-white/10 mt-6">
                        <span class="block text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Total a Pagar</span>
                        <div class="text-3xl font-black text-white">R$ {{ number_format($pedido->total, 2, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Pagamento Status -->
                <div class="mt-8 flex items-center gap-3 p-4 rounded-2xl {{ $pedido->estaPago() ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/20 text-rose-400 border border-rose-500/30' }}">
                    <i class="fas {{ $pedido->estaPago() ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">{{ $pedido->estaPago() ? 'Liquidado' : 'Pendente' }}</span>
                </div>
            </div>

            <!-- Dados Cliente -->
            <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-50 pb-3">Cliente</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center text-lg"><i class="fas fa-user"></i></div>
                        <div>
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Nome / Empresa</div>
                            <div class="text-sm font-black text-slate-800">{{ $pedido->cliente->nome }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg"><i class="fab fa-whatsapp"></i></div>
                        <div>
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Contato</div>
                            <div class="text-sm font-black text-slate-800">{{ $pedido->cliente->whatsapp ?: '---' }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                     <button class="w-full py-3 bg-slate-50 text-slate-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-colors">Ver Ficha Completa</button>
                </div>
            </div>
        </div>

        <!-- Área Principal Content -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- Navbar de Abas Internas -->
            <div class="flex gap-2 p-1 bg-slate-100 rounded-2xl w-fit">
                <button @click="tab = 'itens'" :class="tab === 'itens' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-6 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">Itens & Artes</button>
                <button @click="tab = 'timeline'" :class="tab === 'timeline' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-6 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">Linha do Tempo</button>
                <button @click="tab = 'obs'" :class="tab === 'obs' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-6 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">Anotações</button>
            </div>

            <!-- ABA: ITENS -->
            <div x-show="tab === 'itens'" class="animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/50">
                        <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest">Produtos do Pedido</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/30 text-slate-400 text-[9px] font-black uppercase tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="px-8 py-5">Item / Arte</th>
                                    <th class="px-8 py-5 text-center">Qtd.</th>
                                    <th class="px-8 py-5 text-right">Unitário</th>
                                    <th class="px-8 py-5 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($pedido->itens as $item)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="h-12 w-12 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                                @if($item->produto && $item->produto->foto)
                                                    <img src="{{ asset('storage/' . $item->produto->foto) }}" class="h-full w-full object-cover">
                                                @else
                                                    <i class="fas fa-box text-slate-300 flex h-full w-full items-center justify-center"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-black text-slate-800 text-sm leading-tight">{{ $item->produto->nome ?? 'Item Personalizado' }}</div>
                                                <div class="text-[10px] text-slate-400 font-medium mt-1 line-clamp-1 italic">{{ $item->descricao_item }}</div>
                                                
                                                @if($item->caminho_arte)
                                                <div class="mt-3">
                                                    <a href="{{ route('admin.sales.pedidos.arte', $item->id) }}" class="inline-flex items-center gap-2 px-3 py-1 bg-brand-primary/10 text-brand-primary rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-brand-primary hover:text-white transition-all">
                                                        <i class="fas fa-cloud-download-alt"></i> Baixar Arte
                                                    </a>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center text-sm font-black text-slate-700">{{ $item->quantidade }}</td>
                                    <td class="px-8 py-6 text-right text-sm font-black text-slate-700">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                    <td class="px-8 py-6 text-right text-sm font-black text-brand-secondary">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ABA: TIMELINE (HISTÓRICO) -->
            <div x-show="tab === 'timeline'" class="animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-8 border-l-4 border-slate-200 pl-3">Movimentações do Pedido</h2>
                    
                    <div class="relative space-y-8 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-slate-100 before:via-slate-200 before:to-transparent">
                        @forelse($pedido->historico->sortByDesc('created_at') as $log)
                        <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-100 text-slate-400 shadow-sm shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10 transition-colors group-hover:bg-brand-primary group-hover:text-white">
                                <i class="fas {{ $statusMap[$log->status_novo]['icon'] ?? 'fa-history' }} text-xs"></i>
                            </div>
                            <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-5 rounded-3xl border border-slate-100 bg-slate-50/50 shadow-sm transition hover:shadow-lg hover:bg-white">
                                <div class="flex items-center justify-between space-x-2 mb-2">
                                    <div class="font-black text-slate-800 text-xs uppercase tracking-tight">Status: {{ $statusMap[$log->status_novo]['label'] ?? $log->status_novo }}</div>
                                    <time class="font-black text-brand-primary text-[10px]">{{ $log->created_at->format('d/m/y H:i') }}</time>
                                </div>
                                <div class="text-[11px] text-slate-500 font-medium leading-relaxed italic">"{{ $log->descricao }}"</div>
                                <div class="mt-4 flex items-center gap-2">
                                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Por: {{ $log->usuario->nome ?? 'Sistema' }}</div>
                                </div>
                            </div>
                        </div>
                        @empty
                            <p class="text-center text-slate-400 font-bold py-10 uppercase text-xs">Aguardando movimentações...</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- ABA: OBSERVAÇÕES -->
            <div x-show="tab === 'obs'" class="animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="bg-brand-secondary/5 rounded-3xl border border-brand-secondary/10 p-10 text-center">
                    <i class="fas fa-sticky-note text-4xl text-brand-secondary/20 mb-4"></i>
                    <h3 class="font-black text-brand-secondary uppercase text-sm mb-4">Instruções Internas</h3>
                    <p class="text-slate-600 font-medium italic text-lg leading-relaxed">
                        @if($pedido->observacoes)
                            "{{ $pedido->observacoes }}"
                        @else
                            "Nenhuma observação especial registrada para este pedido."
                        @endif
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Mudar Status -->
    <div id="modalStatus" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="document.getElementById('modalStatus').classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-3xl shadow-2xl p-8 transform transition-all">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
                <h2 class="text-2xl font-black text-brand-secondary tracking-tight">Atualizar Entrega</h2>
                <button onclick="document.getElementById('modalStatus').classList.add('hidden')" class="text-slate-300 hover:text-slate-600 transition-colors"><i class="fas fa-times"></i></button>
            </div>
            
            <form action="{{ route('admin.sales.pedidos.status', $pedido->id) }}" method="POST">
                @csrf @method('PATCH')
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Novo Estágio do Pedido</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($statusMap as $slug => $conf)
                                <label class="relative flex items-center p-3 rounded-2xl border border-slate-100 cursor-pointer hover:bg-slate-50 transition-all">
                                    <input type="radio" name="status" value="{{ $slug }}" {{ $pedido->status === $slug ? 'checked' : '' }} class="h-4 w-4 text-brand-primary border-slate-300 focus:ring-brand-primary">
                                    <span class="ml-3 text-[10px] font-black uppercase text-slate-600 truncate">{{ $conf['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nota de Movimentação</label>
                        <textarea name="descricao" rows="3" class="w-full rounded-2xl border-slate-200 focus:border-brand-primary focus:ring-brand-primary font-medium text-slate-700 px-4 py-3 bg-slate-50/50" placeholder="Ex: Arte aprovada pelo cliente via WhatsApp..."></textarea>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modalStatus').classList.add('hidden')" class="px-6 py-3 font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Cancelar</button>
                    <button type="submit" class="px-10 py-3 font-black text-white bg-brand-primary hover:bg-orange-500 shadow-xl shadow-brand-primary/20 rounded-2xl transition-all">
                        Salvar Alteração
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
