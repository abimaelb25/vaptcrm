{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-13 22:00 -03:00
--}}
@php
    $cs = $configSite ?? [];
    
    // Branding Resolved (Sempre da loja no catálogo público)
    $nomeEmpresa   = $branding['name'] ?? 'Loja';
    $primaryColor  = $branding['primary_color'] ?? '#FF7A00';
    $secondaryColor = $branding['secondary_color'] ?? '#1E293B';
    $corDestaque   = $cs['aparencia_cor_destaque'] ?? '#F59E0B';
    
    $modoEscuro    = ($cs['aparencia_modo'] ?? 'claro') === 'escuro';
    $whatsapp      = $cs['empresa_whatsapp'] ?? '5575999279354';
    $isFullWidth   = $fullWidth ?? false;
@endphp
<!DOCTYPE html>
<html lang="pt-BR" @if($modoEscuro) class="dark" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $cs['aparencia_rodape_texto'] ?? 'Catálogo Online e Pedidos - ' . $nomeEmpresa }}">
    <title>{{ $titulo ?? ($nomeEmpresa . ' - Catálogo Online') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @if(!empty($branding['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $branding['favicon']) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --color-brand-primary: {{ $primaryColor }};
            --color-brand-secondary: {{ $secondaryColor }};
            --color-brand-accent: {{ $corDestaque }};
            @if($modoEscuro)
            --color-brand-bg: #0F172A;
            --color-brand-text: #E2E8F0;
            @endif
        }
        .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-brand-bg text-brand-text font-sans antialiased selection:bg-brand-primary selection:text-white flex flex-col min-h-screen">
    {{-- Header Público --}}
    <header class="sticky top-0 z-50 bg-brand-secondary/95 backdrop-blur-md border-b-4 border-brand-primary text-white shadow-xl">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('site.inicio') }}" class="flex items-center gap-3 group">
                @if(!empty($branding['logo']))
                    <img src="{{ asset('storage/' . $branding['logo']) }}" class="h-10 w-auto group-hover:scale-105 transition-transform duration-300 drop-shadow-md" alt="{{ $nomeEmpresa }}">
                @else
                    <img src="{{ asset('img/logo_horizontal.png') }}" class="h-10 w-auto group-hover:scale-105 transition-transform duration-300 drop-shadow-md" alt="{{ $nomeEmpresa }}">
                @endif
            </a>

            <nav class="flex items-center gap-4 md:gap-8">
                @if(!($hideNav ?? false))
                <a href="{{ route('site.catalogo') }}" class="hidden md:block relative overflow-hidden group py-1 text-sm font-bold uppercase tracking-wider">
                    <span class="hover:text-amber-200 transition-colors">Produtos</span>
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand-primary transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="{{ route('site.pedido.acompanhar') }}" class="relative overflow-hidden group py-1 text-sm font-bold uppercase tracking-wider">
                    <span class="hover:text-amber-200 transition-colors">Meus Pedidos</span>
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand-primary transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="hidden sm:inline-flex rounded-full bg-brand-primary px-6 py-2.5 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:bg-orange-600">
                    WhatsApp
                </a>
                @endif
            </nav>
        </div>
    </header>

    {{-- Conteúdo Principal --}}
    <main class="flex-1 w-full @if(!$isFullWidth) mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 @endif animate-fade-in">
        <!-- Flash Messages -->
        @if(session('sucesso'))
            <div class="mb-6 rounded-xl border-l-4 border-emerald-500 bg-emerald-500/10 p-4 text-emerald-700 shadow-sm">
                <p class="font-semibold">{{ session('sucesso') }}</p>
            </div>
        @endif
        
        @if($errors->any())
            <div class="mb-6 rounded-xl border-l-4 border-rose-500 bg-rose-500/10 p-4 text-rose-700 shadow-sm">
                <ul class="list-inside list-disc font-medium text-sm">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- Rodapé Público --}}
    <footer class="mt-auto border-t border-slate-200 {{ $modoEscuro ? 'bg-slate-900' : 'bg-white' }} py-12">
        <div class="mx-auto max-w-7xl px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 items-start">
                <div class="md:col-span-2 space-y-6">
                    @if(!empty($cs['aparencia_logo_rodape']))
                        <img src="{{ asset('storage/' . $cs['aparencia_logo_rodape']) }}" class="h-10 w-auto" alt="{{ $nomeEmpresa }}">
                    @elseif(!empty($cs['aparencia_logo']))
                        <img src="{{ asset('storage/' . $cs['aparencia_logo']) }}" class="h-10 w-auto" alt="{{ $nomeEmpresa }}">
                    @endif
                    <p class="text-sm text-slate-500 leading-relaxed max-w-md">
                        {{ $cs['aparencia_rodape_texto'] ?? 'Oferecemos soluções gráficas de alta qualidade com agilidade e compromisso.' }}
                    </p>
                    <div class="flex items-center gap-4">
                        @if(!empty($cs['empresa_instagram']))
                            <a href="{{ $cs['empresa_instagram'] }}" target="_blank" class="text-slate-400 hover:text-brand-primary transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                            </a>
                        @endif
                        @if(!empty($whatsapp))
                            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" class="text-slate-400 hover:text-emerald-500 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Contato Rápido --}}
                <div class="space-y-4">
                    <h3 class="font-bold text-slate-800 uppercase text-xs tracking-widest">Contato</h3>
                    <div class="space-y-2 text-sm text-slate-500">
                        @if(!empty($cs['empresa_endereco'])) <p>{{ $cs['empresa_endereco'] }}</p> @endif
                        @if(!empty($cs['empresa_telefone'])) <p>Tel: {{ $cs['empresa_telefone'] }}</p> @endif
                        @if(!empty($cs['empresa_email'])) <p>{{ $cs['empresa_email'] }}</p> @endif
                    </div>
                </div>

                {{-- Institucional / Páginas Legais --}}
                <div class="space-y-4 text-right md:text-left">
                    <h3 class="font-bold text-slate-800 uppercase text-xs tracking-widest">Institucional</h3>
                    <div class="flex flex-col gap-2 text-sm text-slate-500">
                        @foreach($paginasLegais ?? [] as $pg)
                            <a href="{{ route('site.pagina', $pg->slug) }}" class="hover:text-brand-primary transition-colors">
                                {{ $pg->titulo }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-slate-400">&copy; {{ date('Y') }} {{ $nomeEmpresa }}. Todos os direitos reservados.</p>
                <div class="flex items-center gap-4 grayscale opacity-50">
                    <img src="https://logodownload.org/wp-content/uploads/2014/07/visa-logo-1.png" class="h-4 w-auto" alt="Visa">
                    <img src="https://logodownload.org/wp-content/uploads/2014/07/mastercard-logo-7.png" class="h-4 w-auto" alt="Mastercard">
                    <img src="https://logodownload.org/wp-content/uploads/2015/03/pix-logo.png" class="h-4 w-auto" alt="PIX">
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
