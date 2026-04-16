<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Em Manutenção - VaptCRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .animate-pulse-slow { animation: pulse-slow 2s ease-in-out infinite; }
    </style>
    <meta http-equiv="refresh" content="60">
</head>
<body class="bg-gradient-to-br from-slate-800 to-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-lg w-full">
        <!-- Card Principal -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-purple-100">
            <!-- Header -->
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-8 sm:p-12 flex flex-col items-center justify-center relative overflow-hidden">
                <div class="absolute -top-10 -right-10 opacity-10">
                    <i class="fas fa-tools text-[200px] text-white"></i>
                </div>
                
                <div class="h-24 w-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mb-6 relative z-10 shadow-lg border-4 border-white/30 animate-pulse-slow">
                    <i class="fas fa-wrench text-4xl text-white"></i>
                </div>
                
                <h1 class="text-4xl font-black text-white text-center relative z-10 mb-2">Em Manutenção</h1>
                <p class="text-purple-100 text-center relative z-10">Estamos melhorando sua experiência</p>
            </div>

            <!-- Body -->
            <div class="p-6 sm:p-10 text-center">
                <p class="text-slate-600 text-lg leading-relaxed mb-6">
                    O sistema está passando por uma manutenção programada para implementar melhorias e atualizações. Voltaremos em breve!
                </p>

                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-100 rounded-xl p-5 mb-8">
                    <div class="flex items-center justify-center gap-3 mb-3">
                        <div class="h-3 w-3 bg-purple-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-semibold text-purple-700 uppercase tracking-wide">Tempo Estimado</span>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">
                        <i class="far fa-clock mr-2 text-purple-500"></i>
                        {{ $exception->getMessage() ?: 'Alguns minutos' }}
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        Esta página atualiza automaticamente a cada 60 segundos
                    </p>
                </div>

                <div class="flex flex-col gap-4">
                    <a href="javascript:location.reload()" 
                       class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-600 hover:to-indigo-600 transition-all shadow-md">
                        <i class="fas fa-sync-alt mr-2"></i> Verificar Novamente
                    </a>
                </div>

                <!-- Redes Sociais -->
                <div class="mt-8 pt-6 border-t border-slate-100">
                    <p class="text-sm text-slate-500 mb-4">Acompanhe as novidades:</p>
                    <div class="flex justify-center gap-4">
                        <a href="#" class="h-10 w-10 rounded-full bg-slate-100 hover:bg-purple-100 flex items-center justify-center text-slate-500 hover:text-purple-600 transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="h-10 w-10 rounded-full bg-slate-100 hover:bg-green-100 flex items-center justify-center text-slate-500 hover:text-green-600 transition-colors">
                            <i class="fab fa-whatsapp"></i>
                        </a>
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
