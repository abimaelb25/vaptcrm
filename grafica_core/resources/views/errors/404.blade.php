<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Não Encontrada - VaptCRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-lg w-full">
        <!-- Card Principal -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-blue-100">
            <!-- Header -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-8 sm:p-12 flex flex-col items-center justify-center border-b border-blue-100 relative overflow-hidden">
                <div class="absolute -top-10 -right-10 opacity-5">
                    <i class="fas fa-map-signs text-[200px] text-blue-500"></i>
                </div>
                
                <div class="h-24 w-24 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mb-6 relative z-10 shadow-lg border-4 border-white">
                    <i class="fas fa-search text-4xl text-blue-600"></i>
                </div>
                
                <h1 class="text-5xl font-black text-blue-600 text-center relative z-10 mb-2">404</h1>
                <h2 class="text-xl font-bold text-slate-800 text-center relative z-10">Página Não Encontrada</h2>
            </div>

            <!-- Body -->
            <div class="p-6 sm:p-10 text-center">
                <p class="text-slate-600 text-lg leading-relaxed mb-8">
                    A página que você está procurando não existe ou foi movida. Verifique o endereço digitado ou navegue para outra seção.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="javascript:history.back()" 
                       class="inline-flex items-center justify-center px-6 py-3 border border-slate-300 text-base font-semibold rounded-xl text-slate-700 bg-white hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Voltar
                    </a>
                    
                    <a href="{{ url('/') }}" 
                       class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 transition-all shadow-md">
                        <i class="fas fa-home mr-2"></i> Ir para o Início
                    </a>
                </div>

                <!-- Sugestões -->
                <div class="mt-10 pt-6 border-t border-slate-100">
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Sugestões</p>
                    <div class="flex flex-wrap gap-2 justify-center">
                        <a href="{{ url('/catalogo') }}" class="text-sm px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-full transition-colors">
                            <i class="fas fa-shopping-bag mr-1"></i> Catálogo
                        </a>
                        @auth
                        <a href="{{ route('admin.dashboard') }}" class="text-sm px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-full transition-colors">
                            <i class="fas fa-tachometer-alt mr-1"></i> Painel
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="text-sm px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-full transition-colors">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-sm text-slate-400">
                <i class="fas fa-shield-check mr-1"></i> VaptCRM - Sistema de Gestão
            </p>
        </div>
    </div>

</body>
</html>
