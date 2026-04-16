@props([
    'titulo',
    'itens',
    'verTodosUrl' => null,
    'vazio' => 'Nenhum item encontrado'
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-100 bg-white p-6 shadow-sm']) }}>
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">{{ $titulo }}</h3>
        @if($verTodosUrl)
            <a href="{{ $verTodosUrl }}" class="text-[10px] font-black text-brand-primary hover:text-brand-secondary transition-colors flex items-center gap-1 uppercase">
                Ver todos
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        @endif
    </div>

    <div class="space-y-3">
        {{ $slot }}
    </div>
</div>
