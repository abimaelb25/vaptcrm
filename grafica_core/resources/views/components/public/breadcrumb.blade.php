@props(['items' => []])

<nav class="public-breadcrumb" aria-label="Breadcrumb">
    @foreach($items as $index => $item)
        @if(!empty($item['url']))
            <a href="{{ $item['url'] }}" class="transition-colors hover:text-brand-primary">{{ $item['label'] }}</a>
        @else
            <span class="truncate text-brand-primary">{{ $item['label'] }}</span>
        @endif

        @if($index < count($items) - 1)
            <span class="text-slate-300">/</span>
        @endif
    @endforeach
</nav>
