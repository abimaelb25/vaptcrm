<x-layouts.app titulo="Configurações Gerais - Gráfica Vapt Vupt">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-brand-secondary tracking-tight">Configurações Gerais</h1>
        <p class="text-slate-500 mt-1 font-medium">Personalize a identidade da sua loja e gerencie a portabilidade de dados.</p>
    </div>

    <!-- Interface Principal -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Coluna de Configurações (Esquerda/Centro) -->
        <div class="xl:col-span-2 space-y-8">
            <form action="{{ route('admin.system.config.update') }}" method="POST" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                @csrf
                <div class="p-8 space-y-10">
                    
                    <!-- Bloco 1: Identidade Visual -->
                    <fieldset>
                        <legend class="text-lg font-black text-slate-800 border-b border-slate-100 pb-2 w-full mb-6">Identidade da Marca</legend>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-black text-slate-700 mb-1">Nome da Gráfica / Loja</label>
                                <input type="text" name="loja_nome" value="{{ $configs['loja_nome'] ?? 'Gráfica Vapt Vupt' }}" placeholder="Ex: Gráfica Vapt Vupt" class="w-full rounded-xl border-slate-200 focus:border-brand-primary font-bold">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-black text-slate-700 mb-1">Slogan ou Subtítulo</label>
                                <input type="text" name="loja_subtitulo" value="{{ $configs['loja_subtitulo'] ?? 'Sua solução rápida em impressões' }}" placeholder="Ex: Impressão de alta qualidade em segundos" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-sm font-black text-slate-700 mb-1">Slug Público (URL da Loja)</label>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400 font-bold">meusite.com/</span>
                                    <input type="text" name="loja_slug" value="{{ $configs['loja_slug'] ?? 'vaptvupt' }}" placeholder="vaptvupt" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Bloco 2: Canais de Atendimento -->
                    <fieldset>
                        <legend class="text-lg font-black text-slate-800 border-b border-slate-100 pb-2 w-full mb-6">Contatos Oficiais</legend>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-black text-slate-700 mb-1">WhatsApp (DDI + DDD)</label>
                                <input type="text" name="contato_whatsapp" value="{{ $configs['contato_whatsapp'] ?? '' }}" placeholder="5575999279354" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-black text-slate-700 mb-1">E-mail Comercial</label>
                                <input type="email" name="contato_email" value="{{ $configs['contato_email'] ?? '' }}" placeholder="pedido@exemplo.com" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-black text-slate-700 mb-1">Link do Instagram</label>
                                <input type="text" name="links_sociais_instagram" value="{{ $configs['links_sociais_instagram'] ?? '' }}" placeholder="@suagrafica" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                            </div>
                        </div>
                        <div class="mt-6">
                            <label class="block text-sm font-black text-slate-700 mb-1">Endereço de Unidade Física</label>
                            <input type="text" name="endereco" value="{{ $configs['endereco'] ?? '' }}" placeholder="Rua, Número, Bairro, Alagoinhas - BA" class="w-full rounded-xl border-slate-200 focus:border-brand-primary">
                        </div>
                    </fieldset>
                    
                </div>

                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-10 py-4 bg-brand-primary text-white font-black rounded-2xl shadow-xl hover:-translate-y-1 transition-all active:scale-95">
                        Salvar Configurações
                    </button>
                </div>
            </form>
        </div>

        <!-- Coluna de Manutenção (Direita) -->
        <div class="space-y-8">
            
            <!-- Backup JSON -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-black text-slate-800">Backup em Nuvem</h2>
                </div>
                <p class="text-sm text-slate-500 mb-6 leading-relaxed">Baixe uma cópia completa do seu catálogo (Categorias, Produtos, Clientes) em formato JSON para portabilidade ou backup externo.</p>
                <a href="{{ route('admin.system.config.export') }}" class="flex items-center justify-center gap-2 w-full py-4 bg-slate-900 text-white font-black rounded-2xl hover:bg-black transition-all shadow-lg">
                    <span>Baixar Backup JSON</span>
                </a>
            </div>

            <!-- Importação JSON -->
            <div class="bg-emerald-50 rounded-3xl border border-emerald-100 p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-white text-emerald-500 flex items-center justify-center shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-black text-emerald-900">Restaurar Dados</h2>
                </div>
                <p class="text-xs text-emerald-700/70 mb-6 font-medium">⚠️ A importação atualizará registros existentes com o mesmo slug/email do arquivo.</p>
                
                <form action="{{ route('admin.system.config.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <input type="file" name="arquivo_json" accept=".json" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-emerald-500 file:text-white hover:file:bg-emerald-600 cursor-pointer" required>
                        <button type="submit" class="w-full py-4 bg-white text-emerald-600 font-black rounded-2xl border-2 border-emerald-100 hover:bg-emerald-100 transition-all shadow-sm">
                            Iniciar Importação
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-layouts.app>

