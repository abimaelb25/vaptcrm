<x-layouts.app titulo="Acesso Restrito - {{ $configSite['empresa_nome'] ?? 'Gráfica' }}">
    <div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
        <div class="h-20 w-20 rounded-2xl bg-slate-100 flex items-center justify-center mb-6">
            <svg class="h-10 w-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
            </svg>
        </div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Painel não configurado para seu perfil</h1>
        <p class="text-slate-500 max-w-md mx-auto mb-8 font-medium">Seu perfil ({{ auth()->user()->perfil }}) ainda não possui um dashboard personalizado. Por favor, entre em contato com o administrador para habilitar as visualizações corretas.</p>
        
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="javascript:history.back()" class="px-6 py-3 rounded-xl bg-slate-100 text-slate-700 font-black text-sm hover:bg-slate-200 transition-all uppercase tracking-wider">Voltar</a>
            <a href="mailto:suporte@vaptcrm.com.br" class="px-6 py-3 rounded-xl bg-brand-primary text-white font-black text-sm shadow-lg shadow-brand-primary/30 hover:bg-orange-600 transition-all uppercase tracking-wider">Acionar Suporte</a>
        </div>
    </div>
</x-layouts.app>
