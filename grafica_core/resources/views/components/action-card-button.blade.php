@props([
    'icon' => 'fa-circle',
    'label' => 'Ação',
    'href' => '#',
    'color' => 'slate', // 'blue', 'amber', 'red', 'slate'
    'isForm' => false,
    'formAction' => null,
    'formMethod' => 'POST',
    'formConfirm' => null,
    'disabled' => false,
    'title' => null,
])

@php
    $colorMap = [
        'blue' => [
            'bg' => 'bg-blue-50',
            'icon' => 'text-blue-700',
            'border' => 'border-blue-100',
            'hover' => 'hover:bg-blue-100',
            'focus' => 'focus:ring-blue-300',
            'text' => 'text-blue-900',
        ],
        'amber' => [
            'bg' => 'bg-amber-50',
            'icon' => 'text-amber-700',
            'border' => 'border-amber-100',
            'hover' => 'hover:bg-amber-100',
            'focus' => 'focus:ring-amber-300',
            'text' => 'text-amber-900',
        ],
        'red' => [
            'bg' => 'bg-red-50',
            'icon' => 'text-red-700',
            'border' => 'border-red-100',
            'hover' => 'hover:bg-red-100',
            'focus' => 'focus:ring-red-300',
            'text' => 'text-red-900',
        ],
        'slate' => [
            'bg' => 'bg-slate-50',
            'icon' => 'text-slate-700',
            'border' => 'border-slate-200',
            'hover' => 'hover:bg-slate-100',
            'focus' => 'focus:ring-slate-300',
            'text' => 'text-slate-900',
        ],
    ];
    
    $colors = $colorMap[$color] ?? $colorMap['slate'];
@endphp

@if($isForm && $formAction)
    <form action="{{ $formAction }}" method="POST" class="inline" 
          @if($formConfirm) onsubmit="return confirm('{{ $formConfirm }}')" @endif>
        @csrf
        @if($formMethod !== 'POST') @method($formMethod) @endif
        
        <button type="submit"
                {{ $disabled ? 'disabled' : '' }}
                @if($title) title="{{ $title }}" aria-label="{{ $title }}" @endif
                class="inline-flex flex-col items-center justify-center gap-2 px-4 py-3 rounded-2xl border transition-all text-center group
                       {{ $colors['bg'] }} {{ $colors['border'] }} {{ $colors['text'] }}
                       {{ !$disabled ? $colors['hover'] : 'opacity-50 cursor-not-allowed' }}
                       focus:outline-none focus:ring-2 {{ $colors['focus'] }} focus:ring-offset-2
                       shadow-sm hover:shadow-md">
            <i class="fas fa-{{ $icon }} text-lg font-black {{ $colors['icon'] }}"></i>
            <span class="text-xs font-bold whitespace-nowrap">{{ $label }}</span>
        </button>
    </form>
@else
    <a href="{{ !$disabled ? $href : '#' }}" 
       {{ $disabled ? 'onclick="return false"' : '' }}
       @if($title) title="{{ $title }}" aria-label="{{ $title }}" @endif
       class="inline-flex flex-col items-center justify-center gap-2 px-4 py-3 rounded-2xl border transition-all text-center group no-underline
              {{ $colors['bg'] }} {{ $colors['border'] }} {{ $colors['text'] }}
              {{ !$disabled ? $colors['hover'] : 'opacity-50 cursor-not-allowed' }}
              focus:outline-none focus:ring-2 {{ $colors['focus'] }} focus:ring-offset-2
              shadow-sm hover:shadow-md">
        <i class="fas fa-{{ $icon }} text-lg font-black {{ $colors['icon'] }}"></i>
        <span class="text-xs font-bold whitespace-nowrap">{{ $label }}</span>
    </a>
@endif
