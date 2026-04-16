{{--
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 23:50
| Descrição: Componente de Card de Produto Premium para o E-commerce.
--}}
@props(['produto'])

@php
    $precoBase = (float) $produto->preco_base;
    $temVariacoes = $produto->variacoes->count() > 0 || $produto->acabamentos->count() > 0;
    $badge = $produto->badge_comercial ?? ($produto->destaque ? 'Destaque' : null);
@endphp

<article {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl flex flex-col h-full']) }}>
    
    {{-- Badges --}}
    @if($badge)
        <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
            <span class="bg-brand-primary text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest shadow-lg shadow-brand-primary/30">
                {{ $badge }}
            </span>
        </div>
    @endif

    @if($produto->prazo_estimado && str_contains(strtolower($produto->prazo_estimado), '24h'))
        <div class="absolute top-4 right-4 z-10">
            <span class="bg-emerald-500 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest shadow-lg shadow-emerald-500/30">
                Entrega Rápida
            </span>
        </div>
    @endif

    {{-- Imagem --}}
    <a href="{{ route('site.produto', $produto) }}" class="aspect-square w-full overflow-hidden bg-slate-50 block sm:h-64 lg:h-72">
        @if($produto->imagem_principal)
            <img src="{{ asset('storage/' . $produto->imagem_principal) }}" 
                 alt="{{ $produto->nome }}" 
                 loading="lazy"
                 class="h-full w-full object-cover transition-transform duration-1000 group-hover:scale-110">
        @else
            <div class="flex h-full w-full items-center justify-center text-6xl text-slate-200">📦</div>
        @endif
        
        {{-- Overlay de Ação Rápida (Desktop) --}}
        <div class="absolute inset-x-0 bottom-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300 hidden lg:block">
            <div class="bg-white/95 backdrop-blur-sm p-2 rounded-xl shadow-xl flex gap-2">
                <button class="flex-1 bg-brand-secondary text-white text-xs font-black py-2.5 rounded-lg uppercase tracking-wider hover:bg-brand-primary transition-colors">
                    Personalizar
                </button>
            </div>
        </div>
    </a>

    {{-- Conteúdo --}}
    <div class="p-5 flex flex-col flex-1">
        <div class="flex items-center justify-between mb-1.5">
            <span class="text-[10px] font-black uppercase tracking-[0.15em] text-brand-primary/70">
                {{ $produto->categoriaRel->nome ?? 'Impressos' }}
            </span>
            @if($produto->unidade_venda)
                <span class="text-[10px] font-bold text-slate-400 uppercase">
                    {{ $produto->unidade_venda }}
                </span>
            @endif
        </div>

        <h2 class="text-base font-bold text-slate-800 line-clamp-1 group-hover:text-brand-primary transition-colors">
            <a href="{{ route('site.produto', $produto) }}">{{ $produto->nome }}</a>
        </h2>

        @if($produto->descricao_curta)
            <p class="mt-2 text-xs text-slate-400 line-clamp-2 leading-relaxed flex-1">
                {{ $produto->descricao_curta }}
            </p>
        @endif

        {{-- Footer do Card --}}
        <div class="mt-5 pt-4 border-t border-slate-50 flex items-end justify-between">
            <div class="flex flex-col">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">
                    {{ $temVariacoes ? 'A partir de' : 'Valor Total' }}
                </span>
                <span class="text-xl font-black text-brand-secondary tracking-tight">
                    @if($precoBase > 0)
                        <span class="text-sm font-bold mr-0.5">R$</span>{{ number_format($precoBase, 2, ',', '.') }}
                    @else
                        Consulte
                    @endif
                </span>
            </div>

            <a href="{{ route('site.produto', $produto) }}" class="p-2.5 rounded-xl bg-slate-50 text-brand-secondary hover:bg-brand-primary hover:text-white transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>
    </div>
</article>
