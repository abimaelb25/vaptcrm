@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div>
        <h1 class="page-header-title">{{ $title }}</h1>
        @if(!empty($subtitle))
            <p class="page-header-subtitle">{{ $subtitle }}</p>
        @endif
        @isset($meta)
            <div class="mt-2">{{ $meta }}</div>
        @endisset
    </div>

    @isset($actions)
        <div class="page-actions">
            {{ $actions }}
        </div>
    @endisset
</div>
