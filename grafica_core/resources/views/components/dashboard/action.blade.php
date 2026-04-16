@props([
    'titulo',
    'url',
    'icone',
    'cor' => 'brand' // brand, secondary, slate
])

@php
    $styles = [
        'brand' => 'bg-brand-primary text-white shadow-brand-primary/30 hover:shadow-brand-primary/50 hover:bg-orange-600',
        'secondary' => 'bg-brand-secondary text-white shadow-brand-secondary/30 hover:shadow-brand-secondary/50',
        'slate' => 'bg-white text-slate-700 border border-slate-200 shadow-sm hover:bg-slate-50',
    ][$cor] ?? $cor;
@endphp

<a href="{{ $url }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-black shadow-lg transition-all hover:-translate-y-0.5 {$styles}"]) }}>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        {!! $icone !!}
    </svg>
    {{ $titulo }}
</a>
