{{--
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-17 18:50
| Descrição: Componente de Card de Produto Premium Otimizado para Mobile.
--}}
@props(['produto'])

@php
    $precoBase = (float) $produto->preco_base;
    $temVariacoes = $produto->variacoes->count() > 0 || $produto->acabamentos->count() > 0;
    $badge = $produto->badge_comercial ?? ($produto->destaque ? 'Destaque' : null);
@endphp

<article {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-xl sm:rounded-2xl border border-slate-100 bg-white shadow-sm transition-all duration-500 hover:-translate-y-1 sm:hover:-translate-y-2 hover:shadow-xl flex flex-col h-full']) }}>
    
    {{-- Badges --}}
    @if($badge)
        <div class="absolute top-2 left-2 sm:top-4 sm:left-4 z-10 flex flex-col gap-2">
            <span class="bg-brand-primary text-white text-[8px] sm:text-[10px] font-black px-2 sm:px-3 py-0.5 sm:py-1 rounded-full uppercase tracking-widest shadow-lg shadow-brand-primary/30">
                {{ $badge }}
            </span>
        </div>
    @endif

    {{-- Imagem Otimizada --}}
    <a href="{{ route('site.produto', $produto) }}" class="aspect-square w-full overflow-hidden bg-slate-50 block min-h-[140px] sm:h-64 lg:h-72 relative">
        @if($produto->imagem_principal)
            <img src="{{ asset('storage/' . $produto->imagem_principal) }}" 
                 alt="{{ $produto->nome }}" 
                 loading="lazy"
                 class="h-full w-full object-cover transition-transform duration-1000 group-hover:scale-110">
        @else
            <div class="flex h-full w-full items-center justify-center text-4xl sm:text-6xl text-slate-100">📦</div>
        @endif
        
        {{-- Overlay de Ação Rápida (Apenas Desktop) --}}
        <div class="absolute inset-x-0 bottom-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300 hidden lg:block">
            <div class="bg-white/95 backdrop-blur-sm p-2 rounded-xl shadow-xl flex gap-2 border border-slate-100">
                <button class="flex-1 bg-brand-secondary text-white text-[10px] font-black py-2.5 rounded-lg uppercase tracking-wider hover:bg-brand-primary transition-colors">
                    Ver Opções
                </button>
            </div>
        </div>
    </a>

    {{-- Conteúdo Compacto --}}
    <div class="p-3 sm:p-5 flex flex-col flex-1">
        <div class="flex items-center justify-between mb-1 sm:mb-1.5 min-h-[14px]">
            <span class="text-[8px] sm:text-[10px] font-black uppercase tracking-[0.15em] text-brand-primary/70 truncate">
                {{ $produto->categoriaRel->nome ?? 'Produção' }}
            </span>
        </div>

        <h2 class="text-xs sm:text-base font-bold text-slate-800 line-clamp-2 group-hover:text-brand-primary transition-colors leading-tight min-h-[2.5rem] sm:min-h-0">
            <a href="{{ route('site.produto', $produto) }}">{{ $produto->nome }}</a>
        </h2>

        {{-- Footer Mobile Friendly --}}
        <div class="mt-auto pt-3 sm:pt-4 border-t border-slate-50 flex items-center justify-between gap-2">
            <div class="flex flex-col min-w-0">
                <span class="text-[7px] sm:text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 whitespace-nowrap overflow-hidden">
                    {{ $temVariacoes ? 'A partir de' : 'Valor Total' }}
                </span>
                <span class="text-sm sm:text-xl font-black text-brand-secondary tracking-tight truncate">
                    @if($precoBase > 0)
                        <span class="text-[10px] sm:text-sm font-bold mr-0.5">R$</span>{{ number_format($precoBase, 2, ',', '.') }}
                    @else
                        Consulte
                    @endif
                </span>
            </div>

            <a href="{{ route('site.produto', $produto) }}" class="p-1.5 sm:p-2.5 rounded-lg sm:rounded-xl bg-slate-50 text-brand-secondary hover:bg-brand-primary hover:text-white transition-all shadow-sm shrink-0 border border-slate-100 active:scale-95">
                <x-icon name="chevron-right" class="w-4 h-4 sm:w-5 sm:h-5" />
            </a>
        </div>
    </div>
</article>
