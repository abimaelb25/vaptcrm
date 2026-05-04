@props([
    'branding' => [],
    'nomeEmpresa' => 'VaptCRM',
])

@php
    $systemVersion = config('app.version');

    if (empty($systemVersion) && is_file(base_path('VERSION'))) {
        $systemVersion = trim((string) file_get_contents(base_path('VERSION')));
    }

    $systemVersion = $systemVersion ?: 'v1.0.0';
    if (!str_starts_with((string) $systemVersion, 'v')) {
        $systemVersion = 'v' . $systemVersion;
    }
@endphp

<footer class="mt-auto border-t border-slate-200 bg-white py-6">
    <div class="mx-auto flex w-full max-w-screen-2xl items-center justify-between gap-4 px-6">
        <div class="flex items-center gap-3">
            @if(!empty($branding['logo']))
                <img src="{{ asset('storage/' . $branding['logo']) }}" class="h-8 w-auto" alt="{{ $nomeEmpresa }}">
            @else
                <img src="{{ asset('img/logo_horizontal.png') }}" class="h-8 w-auto" alt="{{ $nomeEmpresa }}">
            @endif
            <p class="text-sm font-bold text-slate-700">Sistema de Gestao de Grafica</p>
        </div>

        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black uppercase tracking-wider text-slate-500">
            {{ $systemVersion }}
        </span>
    </div>
</footer>
