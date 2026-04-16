@props(['sections'])

<nav {{ $attributes->merge(['class' => 'space-y-6']) }}>
    @foreach($sections as $section)
        <div>
            @if(!empty($section['section']))
                <div class="px-3 mb-2">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400/60 transition-colors group-hover:text-slate-400">
                        {{ $section['section'] }}
                    </p>
                </div>
            @endif

            <ul class="space-y-1">
                @foreach($section['items'] as $item)
                    @php
                        $isActive = isset($item['active_pattern']) && request()->routeIs($item['active_pattern']);
                        $href = $item['url'] ?? (isset($item['route']) ? route($item['route']) : '#');
                        $isPdv = $item['is_pdv'] ?? false;
                        
                        // Cores Premium baseadas no estado
                        $colorClass = isset($item['color']) && $item['color'] === 'rose' 
                            ? ($isActive ? 'bg-rose-500 text-white shadow-lg shadow-rose-200' : 'text-rose-500 hover:bg-rose-50')
                            : ($isActive 
                                ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20' 
                                : 'text-slate-600 hover:bg-slate-50 hover:text-brand-secondary');
                    @endphp

                    <li>
                        <a href="{{ $href }}" 
                           @if($isPdv) 
                                onclick="event.preventDefault(); window.open(this.href, 'PDV', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' + screen.width + ',height=' + screen.height)" 
                           @endif
                           @if(isset($item['external']) && $item['external']) target="_blank" @endif
                           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all duration-300 group {{ $colorClass }} {{ $isActive ? 'translate-x-1' : 'hover:translate-x-1' }}">
                            
                            <div class="relative">
                                <x-icon :name="$item['icon']" class="w-[20px] h-[20px] shrink-0 transition-transform duration-300 group-hover:scale-110" />
                                @if(str_contains($item['label'], 'Alertas') || str_contains($item['label'], 'Contas a Pagar'))
                                     <span class="absolute -top-1 -right-1 flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                                     </span>
                                @endif
                            </div>
                            
                            <span class="truncate">{{ $item['label'] }}</span>

                            @if($isActive)
                                <div class="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-white/20">
                                    <div class="h-1.5 w-1.5 rounded-full bg-white"></div>
                                </div>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
