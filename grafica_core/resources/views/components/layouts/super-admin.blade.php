<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin - VaptCRM SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col transition-all duration-300">
            <div class="h-16 flex items-center justify-center border-b border-slate-800">
                <span class="text-xl font-bold bg-gradient-to-r from-blue-400 to-indigo-500 bg-clip-text text-transparent">VaptCRM Admin</span>
            </div>

            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('superadmin.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 transition-colors {{ request()->routeIs('superadmin.dashboard') ? 'bg-slate-800 border-l-4 border-indigo-500' : 'border-l-4 border-transparent' }}">
                            <i class="fas fa-chart-line w-6"></i>
                            <span class="ml-2 font-medium">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.lojas.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 transition-colors {{ request()->routeIs('superadmin.lojas.*') ? 'bg-slate-800 border-l-4 border-indigo-500' : 'border-l-4 border-transparent' }}">
                            <i class="fas fa-store w-6"></i>
                            <span class="ml-2 font-medium">Lojas (Tenants)</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.planos.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 transition-colors {{ request()->routeIs('superadmin.planos.*') ? 'bg-slate-800 border-l-4 border-indigo-500' : 'border-l-4 border-transparent' }}">
                            <i class="fas fa-layer-group w-6"></i>
                            <span class="ml-2 font-medium">Planos SaaS</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.assinaturas.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 transition-colors {{ request()->routeIs('superadmin.assinaturas.*') ? 'bg-slate-800 border-l-4 border-indigo-500' : 'border-l-4 border-transparent' }}">
                            <i class="fas fa-file-invoice-dollar w-6"></i>
                            <span class="ml-2 font-medium">Assinaturas</span>
                        </a>
                    </li>
                    <li class="pt-2">
                        <div class="px-6 py-2 text-[10px] font-black uppercase text-slate-500 tracking-widest">Atendimento</div>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.support.categorias.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 transition-colors {{ request()->routeIs('superadmin.support.categorias.*') ? 'bg-slate-800 border-l-4 border-indigo-500' : 'border-l-4 border-transparent' }}">
                            <i class="fas fa-tags w-6"></i>
                            <span class="ml-2 font-medium">Categorias</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.support.tickets.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 transition-colors {{ request()->routeIs('superadmin.support.tickets.*') ? 'bg-slate-800 border-l-4 border-indigo-500' : 'border-l-4 border-transparent' }}">
                            <i class="fas fa-headset w-6"></i>
                            <span class="ml-2 font-medium">Central de Tickets</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.support.central-de-ajuda.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 transition-colors {{ request()->routeIs('superadmin.support.central-de-ajuda.*') ? 'bg-slate-800 border-l-4 border-indigo-500' : 'border-l-4 border-transparent' }}">
                            <i class="fas fa-video w-6"></i>
                            <span class="ml-2 font-medium">VaptAcademy</span>
                        </a>
                    </li>
                    <li class="pt-2">
                        <div class="px-6 py-2 text-[10px] font-black uppercase text-slate-500 tracking-widest">SISTEMA</div>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.branding.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 transition-colors {{ request()->routeIs('superadmin.branding.*') ? 'bg-slate-800 border-l-4 border-indigo-500' : 'border-l-4 border-transparent' }}">
                            <i class="fas fa-paints-roller w-6"></i>
                            <span class="ml-2 font-medium">Identidade Visual</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.depoimentos.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 transition-colors {{ request()->routeIs('superadmin.depoimentos.*') ? 'bg-slate-800 border-l-4 border-indigo-500' : 'border-l-4 border-transparent' }}">
                            <i class="fas fa-quote-left w-6"></i>
                            <span class="ml-2 font-medium">Prova Social</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <form action="{{ route('auth.sair') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800 rounded transition-colors">
                        <i class="fas fa-sign-out-alt w-5"></i> Sair do Painel
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10 shadow-sm">
                <div class="flex items-center">
                    <h2 class="text-xl font-semibold text-gray-800">Módulo Super Admin</h2>
                    <span class="ml-3 px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200 mr-4">Visão Global</span>

                    {{-- Atalho para o CRM --}}
                    {{-- Abimael Borges | https://abimaelborges.adv.br | 2026-04-16 01:15 BRT --}}
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-orange-500/10 border border-orange-200 text-orange-700 hover:bg-orange-500 hover:text-white transition-all text-[10px] font-bold uppercase tracking-wider">
                        <i class="fas fa-store"></i>
                        Dashboard Loja
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600 text-right">
                        <div class="font-medium">{{ Auth::user()->nome }}</div>
                        <div class="text-xs text-indigo-600">Super Administrator</div>
                    </div>
                    <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->nome) . '&color=4F46E5&background=EEF2FF' }}" alt="Avatar" class="w-10 h-10 rounded-full border-2 border-indigo-200">
                </div>
            </header>

            <!-- Alerts -->
            <div class="px-6 pt-6">
                @if(session('success'))
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-md shadow-sm mb-4 flex items-start">
                        <div class="flex-shrink-0"><i class="fas fa-check-circle text-green-400"></i></div>
                        <div class="ml-3"><p class="text-sm text-green-700">{{ session('success') }}</p></div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md shadow-sm mb-4 flex items-start">
                        <div class="flex-shrink-0"><i class="fas fa-exclamation-circle text-red-400"></i></div>
                        <div class="ml-3"><p class="text-sm text-red-700">{{ session('error') }}</p></div>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <div class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 pb-20">
                {{ $slot }}
            </div>
        </main>
    </div>

</body>
</html>
