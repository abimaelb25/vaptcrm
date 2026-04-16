<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Bloqueado - VaptCRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-xl w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-red-100">
        <!-- Header / Banner -->
        <div class="bg-red-50 p-6 sm:p-10 flex flex-col items-center justify-center border-b border-red-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5">
                <i class="fas fa-lock text-9xl text-red-500"></i>
            </div>
            
            <div class="h-20 w-20 bg-red-100 rounded-full flex items-center justify-center mb-6 relative z-10 shadow-sm border border-red-200">
                <i class="fas fa-exclamation-triangle text-3xl text-red-600"></i>
            </div>
            
            <h1 class="text-3xl font-bold text-red-900 text-center relative z-10">Acesso Suspenso</h1>
            <p class="text-red-700 font-medium text-center mt-2 relative z-10">
                A sua loja <strong>{{ Auth::user()->loja->nome_fantasia ?? 'Desconhecida' }}</strong> encontra-se temporariamente bloqueada.
            </p>
        </div>

        <!-- Body -->
        <div class="p-6 sm:p-10">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-5 mb-8">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide border-b border-gray-200 pb-2 mb-3">Motivo do Bloqueio</h3>
                <p class="text-gray-800 font-medium text-lg">
                    {{ Auth::user()->loja->motivo_bloqueio ?? 'Inadimplência ou pendência administrativa.' }}
                </p>
                <div class="mt-4 text-sm text-gray-500 flex items-center">
                    <i class="far fa-clock mr-2"></i> Bloqueada em: {{ Auth::user()->loja->bloqueada_em ? Auth::user()->loja->bloqueada_em->format('d/m/Y \à\s H:i') : 'Data não registrada' }}
                </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 mb-3">Como regularizar?</h3>
            <p class="text-gray-600 mb-6 leading-relaxed">
                Para reativar o acesso ao seu painel e ao catálogo de vendas, é necessário regularizar a situação da sua assinatura. Nossa equipe financeira está à disposição para ajudar a resolver esta pendência rapidamente.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 mb-8">
                <a href="{{ route('admin.billing.index') }}" class="flex-1 flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors cursor-pointer">
                    <i class="fas fa-credit-card mr-2"></i> Pagar Assinatura
                </a>
                
                <a href="https://wa.me/5500000000000" target="_blank" class="flex-1 flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors cursor-pointer">
                    <i class="fab fa-whatsapp mr-2 text-green-500"></i> Falar com Suporte
                </a>
            </div>
            
            <div class="text-center pt-6 border-t border-gray-100">
                <form action="{{ route('auth.sair') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-800 flex items-center justify-center mx-auto transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i> Sair da Conta
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
