@props([
    'titulo',
    'mensagem' => null,
    'tipo' => 'atencao', // atencao, erro, info, sucesso
    'link' => null,
    'linkTexto' => 'Ver detalhes'
])

@php
    $styles = [
        'atencao' => [
            'bg' => 'bg-amber-50',
            'border' => 'border-amber-200',
            'icon' => 'bg-amber-100',
            'text' => 'text-amber-800',
            'iconColor' => 'text-amber-600',
            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />'
        ],
        'erro'    => [
            'bg' => 'bg-rose-50',
            'border' => 'border-rose-200',
            'icon' => 'bg-rose-100',
            'text' => 'text-rose-800',
            'iconColor' => 'text-rose-600',
            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />'
        ],
        'info'    => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'icon' => 'bg-blue-100',
            'text' => 'text-blue-800',
            'iconColor' => 'text-blue-600',
            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />'
        ],
        'sucesso' => [
            'bg' => 'bg-emerald-50',
            'border' => 'border-emerald-200',
            'icon' => 'bg-emerald-100',
            'text' => 'text-emerald-800',
            'iconColor' => 'text-emerald-600',
            'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
        ],
    ][$tipo];
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl border {$styles['border']} {$styles['bg']} p-4 shadow-sm transition-all"]) }}>
    <div class="flex items-start gap-4">
        <div class="h-10 w-10 shrink-0 rounded-xl {$styles['icon']} flex items-center justify-center">
            <svg class="w-5 h-5 {$styles['iconColor']}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                {!! $styles['svg'] !!}
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-black {$styles['text']}">{{ $titulo }}</p>
            @if($mensagem)
                <p class="text-xs font-medium opacity-80 mt-0.5">{{ $mensagem }}</p>
            @endif
            @if($link)
                <a href="{{ $link }}" class="inline-block mt-2 text-xs font-black uppercase tracking-wider underline opacity-70 hover:opacity-100 transition-opacity">
                    {{ $linkTexto }}
                </a>
            @endif
        </div>
    </div>
</div>
