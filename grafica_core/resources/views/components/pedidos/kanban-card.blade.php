@props(['pedido'])
@php
    $cifra = 'text-slate-600';
    if(in_array($pedido->status, ['rascunho', 'aguardando_aprovacao'])) $cifra = 'text-orange-500';
    elseif(in_array($pedido->status, ['em_producao', 'entregue', 'pronto'])) $cifra = 'text-emerald-600';
    
    $pago = in_array($pedido->status, ['em_producao', 'entregue', 'pronto']);
    $prazo = $pedido->prazo_entrega ? \Illuminate\Support\Carbon::parse($pedido->prazo_entrega) : null;
    $atrasado = $prazo && $prazo->isPast() && !in_array($pedido->status, ['entregue', 'cancelado']);
@endphp

<div class="kanban-card bg-white rounded-xl shadow-sm border border-slate-200 p-4 hover:shadow-md transition-all cursor-grab active:cursor-grabbing group relative" 
     data-id="{{ $pedido->id }}" 
     data-status="{{ $pedido->status }}">
    
    <div class="flex justify-between items-start mb-2">
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">#{{ $pedido->numero }}</span>
        <div class="flex gap-1">
            @if($pedido->origem === 'online')
                <div class="h-2 w-2 rounded-full bg-purple-500" title="Origem: Site/Online"></div>
            @endif
            @if($prazo && $prazo->isToday())
                <div class="h-2 w-2 rounded-full bg-red-500 animate-pulse" title="Entrega marcada para HOJE!"></div>
            @elseif($atrasado)
                <div class="h-2 w-2 rounded-full bg-red-600" title="Pedido com entrega ATRASADA"></div>
            @endif
        </div>
    </div>

    <h4 class="font-bold text-slate-800 text-sm leading-tight mb-1 truncate" title="{{ $pedido->cliente->nome }}">
        {{ $pedido->cliente->nome }}
    </h4>
    
    <div class="text-[11px] text-slate-500 mb-3 line-clamp-1 font-medium">
        {{ $pedido->itens->first()->produto->nome ?? 'Pacote de Serviços' }}
        @if($pedido->itens->count() > 1)
            <span class="text-slate-300 ml-0.5">+{{ $pedido->itens->count() - 1 }}</span>
        @endif
    </div>

    <div class="flex items-center justify-between mt-auto pt-3 border-t border-slate-100">
        <div class="flex flex-col">
            <span class="text-xs font-black {{ $cifra }}">R$ {{ number_format((float)$pedido->total, 2, ',', '.') }}</span>
            <span class="text-[9px] font-bold uppercase tracking-widest {{ $pago ? 'text-emerald-500' : 'text-orange-400' }}">
                {{ $pago ? 'Acertado' : 'Pendente' }}
            </span>
        </div>
        
        <div class="flex items-center gap-2">
            @php
                $fone = $pedido->cliente->whatsapp ?? $pedido->cliente->telefone;
                $foneLimpo = preg_replace('/[^0-9]/', '', $fone);
            @endphp
            @if($fone)
                <a href="https://wa.me/55{{ $foneLimpo }}" target="_blank" class="text-emerald-500 hover:scale-110 transition-transform" title="Chamar no WhatsApp">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </a>
            @endif
            <a href="{{ route('admin.sales.pedidos.show', $pedido->id) }}" class="text-slate-400 hover:text-brand-primary transition-colors" title="Abrir Detalhes">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </a>
        </div>
    </div>
</div>
