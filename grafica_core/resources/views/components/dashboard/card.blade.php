@props([
    'titulo',
    'valor',
    'icone' => null,
    'cor' => 'blue', // blue, green, amber, red, etc
    'tamanho' => 'normal' // normal, grande
])

@php
    $colorClasses = [
        'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-500', 'border' => 'border-blue-100'],
        'green' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-500', 'border' => 'border-emerald-100'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-500', 'border' => 'border-amber-100'],
        'red' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-500', 'border' => 'border-rose-100'],
        'cyan' => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-500', 'border' => 'border-cyan-100'],
        'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-500', 'border' => 'border-purple-100'],
    ][$cor] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-500', 'border' => 'border-slate-100'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl bg-white border border-slate-100 p-5 shadow-sm hover:shadow-md transition-all']) }}>
    <div class="flex items-center gap-3 mb-3">
        @if($icone)
            <div class="h-10 w-10 rounded-xl {{ $colorClasses['bg'] }} flex items-center justify-center">
                <svg class="w-5 h-5 {{ $colorClasses['text'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    {!! $icone !!}
                </svg>
            </div>
        @endif
    </div>
    <p class="{{ $tamanho === 'grande' ? 'text-2xl' : 'text-3xl' }} font-black text-slate-800">{{ $valor }}</p>
    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">{{ $titulo }}</p>
</div>
