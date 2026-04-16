{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-13 14:47 -03:00
--}}
@php
    $cs = $configSite ?? [];
    $cp = $configPlataforma ?? [];
    
    // Branding Resolved
    $nomeEmpresa   = $branding['name'] ?? 'VaptCRM';
    $primaryColor  = $branding['primary_color'] ?? '#FF7A00';
    $secondaryColor = $branding['secondary_color'] ?? '#1E293B';
    
    $modoEscuro    = ($cs['aparencia_modo'] ?? 'claro') === 'escuro';
    $whatsapp      = $cs['empresa_whatsapp'] ?? ($cp['plataforma_whatsapp_suporte'] ?? '');
@endphp
<!DOCTYPE html>
<html lang="pt-BR" @if($modoEscuro) class="dark" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $cp['plataforma_nome'] ?? 'VaptCRM' }} - CRM Moderno para Gráfica">
    <title>{{ $titulo ?? ($nomeEmpresa . ' - Catálogo') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @if(!empty($branding['favicon']))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $branding['favicon']) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --color-brand-primary: {{ $primaryColor }};
            --color-brand-secondary: {{ $secondaryColor }};
            --color-brand-accent: {{ $cs['aparencia_cor_destaque'] ?? '#F59E0B' }};
            @if($modoEscuro)
            --color-brand-bg: #0F172A;
            --color-brand-text: #E2E8F0;
            @endif
        }
    </style>
    @stack('styles')
</head>
<body class="bg-brand-bg text-brand-text font-sans antialiased selection:bg-brand-primary selection:text-white flex flex-col min-h-screen">
    <header class="sticky top-0 z-50 bg-brand-secondary/95 backdrop-blur-md border-b-4 border-brand-primary text-white shadow-xl transition-all duration-300">
        <div class="mx-auto flex w-full items-center justify-between px-6 py-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    @if(!empty($branding['logo']))
                        <img src="{{ asset('storage/' . $branding['logo']) }}" class="h-10 w-auto group-hover:scale-105 transition-transform duration-300 drop-shadow-md" alt="{{ $nomeEmpresa }}">
                    @else
                        <img src="{{ asset('img/logo_horizontal.png') }}" class="h-10 w-auto group-hover:scale-105 transition-transform duration-300 drop-shadow-md" alt="{{ $nomeEmpresa }}">
                    @endif
                </a>

                @if(!empty($branding['tenant_name']))
                    <div class="hidden lg:flex items-center gap-3 pl-6 border-l border-white/20">
                        <span class="bg-white/10 px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest text-orange-200 border border-white/10">
                            {{ $branding['tenant_name'] }}
                        </span>
                    </div>
                @endif

                {{-- Atalho para o Super Admin (Mestre) --}}
                {{-- Abimael Borges | https://abimaelborges.adv.br | 2026-04-16 01:12 BRT --}}
                @if(auth()->user() && auth()->user()->isSuperAdmin())
                    <a href="{{ route('superadmin.dashboard') }}" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-600/20 border border-indigo-400/30 text-indigo-100 hover:bg-indigo-600 hover:text-white transition-all text-[10px] font-black uppercase tracking-wider">
                        <i class="fas fa-shield-halved"></i>
                        Painel Master
                    </a>
                @endif
            </div>

            <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('site.catalogo') }}" class="relative overflow-hidden group py-1">
                    <span class="hover:text-amber-200 transition-colors">Catálogo</span>
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand-primary transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="{{ route('site.pedido.acompanhar') }}" class="relative overflow-hidden group py-1">
                    <span class="hover:text-amber-200 transition-colors">Acompanhar Pedido</span>
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-brand-primary transition-all duration-300 group-hover:w-full"></span>
                </a>
                <button type="button" id="btnToggleTema" class="rounded-lg border border-white/20 p-2 hover:bg-white/10 transition-colors" aria-label="Alternar modo claro/escuro" title="Alternar modo claro/escuro">
                    <svg id="iconeSol" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                    <svg id="iconeLua" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                </button>
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="rounded-full bg-brand-primary px-6 py-2.5 font-bold text-white shadow-[0_0_15px_rgba(255,122,0,0.4)] transition-all duration-300 hover:scale-105 hover:bg-orange-600 hover:shadow-[0_0_25px_rgba(255,122,0,0.6)]">
                    Contato WhatsApp
                </a>
            </nav>
        </div>
    </header>

    @if(request()->routeIs('admin.*'))
        <div class="flex-1 w-full px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row gap-6">
            <aside class="hidden w-64 shrink-0 md:block">
                <div class="sticky top-28 rounded-2xl border border-slate-200 bg-white/80 shadow-lg backdrop-blur-xl flex flex-col max-h-[calc(100vh-8rem)] overflow-y-auto">
                    {{-- Perfil do usuário --}}
                    <div class="p-5 flex items-center gap-3 border-b border-slate-100">
                        <div class="h-9 w-9 rounded-full bg-brand-secondary text-white flex items-center justify-center font-bold text-sm shadow-inner shrink-0">
                            {{ substr(auth()->user()->nome, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            @php
                                $employeeId = auth()->user()->funcionario->id ?? auth()->id();
                                $roleDisplay = !empty(auth()->user()->cargo) ? auth()->user()->cargo : auth()->user()->perfil;
                            @endphp
                            <a href="{{ route('admin.system.equipe.show', $employeeId) }}" class="text-sm font-bold text-slate-800 hover:text-brand-primary transition-colors truncate block">{{ auth()->user()->nome }}</a>
                            <p class="text-[10px] text-slate-400 capitalize font-semibold tracking-wide">{{ $roleDisplay }}</p>
                        </div>
                    </div>

                    @php
                        $menuSections = \App\Support\Menu\PainelMenu::forUser(auth()->user());
                    @endphp

                    {{-- Menu Principal Dinâmico --}}
                    <div class="px-3 py-4 flex-1">
                        <x-layouts.sidebar-menu :sections="$menuSections" />
                    </div>
                </div>
            </aside>

            <main class="min-w-0 flex-1">
                <!-- Mobile Nav -->
                <div class="mb-6 flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white/80 p-3 shadow-sm backdrop-blur-md md:hidden scrollbar-hide">
                    @foreach($menuSections as $section)
                        @foreach($section['items'] as $item)
                            @php
                                $isActive = isset($item['active_pattern']) && request()->routeIs($item['active_pattern']);
                                $href = $item['url'] ?? (isset($item['route']) ? route($item['route']) : '#');
                                $isPdv = $item['is_pdv'] ?? false;
                            @endphp
                            <a href="{{ $href }}" 
                               @if($isPdv) onclick="event.preventDefault(); window.open(this.href, 'PDV', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' + screen.width + ',height=' + screen.height)" @endif
                               class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition-colors {{ $isActive ? 'bg-brand-primary text-white shadow-sm' : 'bg-slate-50 text-slate-600' }}">
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    @endforeach
                </div>

                <!-- Flash Messages -->
                @if(session('sucesso'))
                    <div class="mb-6 rounded-xl border-l-4 border-status-success bg-status-success/10 p-4 text-status-success shadow-sm animate-fade-in-down">
                        <p class="font-semibold">{{ session('sucesso') }}</p>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="mb-6 rounded-xl border-l-4 border-status-error bg-status-error/10 p-4 text-status-error shadow-sm animate-fade-in-down">
                        <ul class="list-inside list-disc font-medium text-sm">
                            @foreach($errors->all() as $erro)
                                <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="animate-fade-in">
                    {{ $slot }}
                </div>
            </main>
        </div>
    @else
        <main class="flex-1 w-full mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
            {{ $slot }}
        </main>
    @endif

    <footer class="mt-auto border-t border-slate-200 {{ $modoEscuro ? 'bg-slate-900' : 'bg-white' }} py-10">
        <div class="mx-auto max-w-screen-2xl px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                {{-- Coluna 1: Logo e descrição --}}
                <div class="flex flex-col items-center md:items-start gap-3">
                    @if(!empty($cs['aparencia_logo_rodape']))
                        <img src="{{ asset('storage/' . $cs['aparencia_logo_rodape']) }}" class="h-8 w-auto opacity-70" alt="{{ $nomeEmpresa }}">
                    @elseif(!empty($cs['aparencia_logo']))
                        <img src="{{ asset('storage/' . $cs['aparencia_logo']) }}" class="h-8 w-auto opacity-50 grayscale" alt="{{ $nomeEmpresa }}">
                    @else
                        <img src="{{ asset('img/logo_horizontal.png') }}" class="h-8 grayscale brightness-0 opacity-50" alt="{{ $nomeEmpresa }}">
                    @endif
                    @if(!empty($cs['aparencia_rodape_texto']))
                        <p class="text-xs text-slate-500 leading-relaxed text-center md:text-left max-w-xs">{{ $cs['aparencia_rodape_texto'] }}</p>
                    @endif
                </div>

                {{-- Coluna 2: Dados da empresa --}}
                <div class="text-center md:text-left space-y-1.5">
                    @if(!empty($cs['empresa_endereco']))
                        <p class="text-xs text-slate-500">{{ $cs['empresa_endereco'] }}</p>
                    @endif
                    @if(!empty($cs['empresa_cidade_uf']))
                        <p class="text-xs text-slate-500">{{ $cs['empresa_cidade_uf'] }} {{ !empty($cs['empresa_cep']) ? '- CEP ' . $cs['empresa_cep'] : '' }}</p>
                    @endif
                    @if(!empty($cs['empresa_telefone']))
                        <p class="text-xs text-slate-500">Tel: {{ $cs['empresa_telefone'] }}</p>
                    @endif
                    @if(!empty($cs['empresa_cnpj']))
                        <p class="text-xs text-slate-400">CNPJ: {{ $cs['empresa_cnpj'] }}</p>
                    @endif
                </div>

                {{-- Coluna 3: Links e copyright --}}
                <div class="flex flex-col items-center md:items-end gap-3">
                    <div class="flex items-center gap-3">
                        @if(!empty($cs['empresa_instagram']))
                            <a href="{{ $cs['empresa_instagram'] }}" target="_blank" rel="noopener" class="text-slate-400 hover:text-brand-primary transition-colors" aria-label="Instagram">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                            </a>
                        @endif
                        @if(!empty($whatsapp))
                            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="text-slate-400 hover:text-emerald-500 transition-colors" aria-label="WhatsApp">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                        @endif
                    </div>
                    <p class="text-xs font-medium text-slate-400">
                        &copy; {{ date('Y') }} {{ $nomeEmpresa }}.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Toggle de acessibilidade: modo claro/escuro (localStorage)
        (function() {
            const btn = document.getElementById('btnToggleTema');
            const sol = document.getElementById('iconeSol');
            const lua = document.getElementById('iconeLua');
            const html = document.documentElement;

            function aplicarTema(escuro) {
                if (escuro) {
                    html.classList.add('dark');
                    document.documentElement.style.setProperty('--color-brand-bg', '#0F172A');
                    document.documentElement.style.setProperty('--color-brand-text', '#E2E8F0');
                } else {
                    html.classList.remove('dark');
                    document.documentElement.style.removeProperty('--color-brand-bg');
                    document.documentElement.style.removeProperty('--color-brand-text');
                }
                atualizarIcone(escuro);
            }

            function atualizarIcone(escuro) {
                if (!sol || !lua) return;
                sol.classList.toggle('hidden', !escuro);
                lua.classList.toggle('hidden', escuro);
            }

            var temaLocal = localStorage.getItem('tema_catalogo');
            var modoAdmin = {{ json_encode($modoEscuro) }};
            var estaEscuro = temaLocal ? temaLocal === 'escuro' : modoAdmin;
            aplicarTema(estaEscuro);

            if (btn) {
                btn.addEventListener('click', function() {
                    estaEscuro = !estaEscuro;
                    localStorage.setItem('tema_catalogo', estaEscuro ? 'escuro' : 'claro');
                    aplicarTema(estaEscuro);
                });
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
