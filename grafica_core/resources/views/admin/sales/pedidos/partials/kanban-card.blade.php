@props(['order'])
@php
    /*
    | Autoria: Abimael Borges
    | Site: https://abimaelborges.adv.br
    | Modificado em: 14/04/2026 (Card Realista)
    */
    $whatsapp = $order->cliente->telefone ? preg_replace('/[^0-9]/', '', $order->cliente->telefone) : null;
    $pago = $order->pagamentos->sum('valor') >= $order->total;
    $itemComArte = $order->itens->first(function($item) { return !empty($item->caminho_arte); });
@endphp

<div onclick="window.location.href='{{ route('admin.sales.pedidos.show', $order->id) }}'" class="kanban-card bg-white rounded-xl border border-slate-200 p-3 shadow-sm transition-all hover:shadow-md relative overflow-hidden group cursor-pointer" data-id="{{ $order->id }}">
    {{-- Barra Lateral para Cor de Destaque (opcional, mas o print mostra clean) --}}
    
    {{-- Conteúdo do Card --}}
    <div class="flex items-start gap-1">
        {{-- Drag Handle --}}
        <div class="drag-handle">
            <i class="fas fa-grip-vertical opacity-20 group-hover:opacity-40 transition-opacity"></i>
        </div>

        <div class="flex-grow min-w-0">
            {{-- Header: ID e WhatsApp --}}
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-slate-700 uppercase tracking-tighter">#{{ $order->numero ?? $order->id }} - <span class="text-slate-500 font-bold truncate max-w-[150px] inline-block align-bottom">{{ $order->cliente->nome }}</span></span>
                
                <div class="flex items-center gap-1.5">
                    @if($itemComArte)
                        <a href="{{ route('admin.sales.pedidos.arte', $itemComArte->id) }}" class="text-blue-500 hover:text-blue-600 transition-colors" title="Baixar Arte" onclick="event.stopPropagation()">
                            <i class="fas fa-file-download shadow-sm"></i>
                        </a>
                    @endif
                    
                    @if($whatsapp)
                        <a href="https://wa.me/55{{ $whatsapp }}" target="_blank" class="text-emerald-500 hover:text-emerald-600 transition-colors" title="WhatsApp" onclick="event.stopPropagation()">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Grid de Informações 3 Colunas --}}
            <div class="grid grid-cols-3 gap-2 py-1 items-center border-t border-slate-50 mt-1">
                {{-- Entrega --}}
                <div class="text-center border-r border-slate-100 pr-1">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-tighter leading-none">Entrega</p>
                    <p class="text-[10px] font-bold text-slate-700 mt-0.5">{{ $order->prazo_entrega ? \Carbon\Carbon::parse($order->prazo_entrega)->format('d/m') : '--' }}</p>
                </div>

                {{-- Produto --}}
                <div class="text-center border-r border-slate-100 px-1">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-tighter leading-none">Produto</p>
                    <p class="text-[9px] font-bold text-slate-700 leading-tight mt-0.5 line-clamp-2" title="{{ $order->itens->first()->produto->nome ?? 'Serviço' }}">
                        {{ $order->itens->first()->produto->nome ?? 'Serviço' }}
                    </p>
                </div>

                {{-- Pagamento --}}
                <div class="text-right pl-1">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-tighter leading-none">Pagamento</p>
                    <p class="text-[10px] font-black text-slate-800 mt-0.5">R$ {{ number_format($order->total, 2, ',', '.') }}</p>
                    <p class="text-[8px] font-bold {{ $pago ? 'text-emerald-500' : 'text-slate-400' }} leading-none mt-0.5">
                        {{ $pago ? 'Pago' : 'Pend.' }} R$ {{ number_format($order->pagamentos->sum('valor'), 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
