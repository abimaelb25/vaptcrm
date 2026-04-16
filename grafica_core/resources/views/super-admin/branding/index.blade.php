{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-16 00:10
--}}
<x-layouts.super-admin>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Identidade Visual da Plataforma</h1>
        <p class="text-sm text-gray-500 mt-1">Configure a marca global do VaptCRM (SaaS)</p>
    </div>

    <form action="{{ route('superadmin.branding.update') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Coluna de Configurações --}}
            <div class="md:col-span-2 space-y-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-paints-roller text-indigo-500"></i>
                        Marca e Nomes
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nome da Plataforma</label>
                            <input type="text" name="plataforma_nome" value="{{ $configs['plataforma_nome'] ?? 'VaptCRM' }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            <p class="text-xs text-gray-400 mt-1">Exibido no título das abas e rodapés institucionais.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Cor Primária</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="plataforma_cor_primaria" value="{{ $configs['plataforma_cor_primaria'] ?? '#FF7A00' }}" class="h-10 w-12 rounded border-gray-300 cursor-pointer">
                                    <input type="text" value="{{ $configs['plataforma_cor_primaria'] ?? '#FF7A00' }}" class="flex-1 rounded-lg border-gray-300 text-sm font-mono" readonly>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Cor Secundária</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="plataforma_cor_secundaria" value="{{ $configs['plataforma_cor_secundaria'] ?? '#1E293B' }}" class="h-10 w-12 rounded border-gray-300 cursor-pointer">
                                    <input type="text" value="{{ $configs['plataforma_cor_secundaria'] ?? '#1E293B' }}" class="flex-1 rounded-lg border-gray-300 text-sm font-mono" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-headset text-indigo-500"></i>
                        Suporte e Contato
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">E-mail de Suporte</label>
                            <input type="email" name="plataforma_email_suporte" value="{{ $configs['plataforma_email_suporte'] ?? '' }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">WhatsApp de Suporte</label>
                            <input type="text" name="plataforma_whatsapp_suporte" value="{{ $configs['plataforma_whatsapp_suporte'] ?? '' }}" placeholder="5575999999999" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

            </div>

            {{-- Coluna de Imagens --}}
            <div class="space-y-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
                    <label class="block text-sm font-bold text-gray-700 mb-4">Logo da Plataforma</label>
                    <div class="mb-4 flex justify-center">
                        @if(!empty($configs['plataforma_logo']))
                            <img src="{{ asset('storage/' . $configs['plataforma_logo']) }}" class="h-16 w-auto object-contain" alt="Logo">
                        @else
                            <div class="h-16 w-32 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs">Sem logo</div>
                        @endif
                    </div>
                    <input type="file" name="plataforma_logo" class="text-xs file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
                    <label class="block text-sm font-bold text-gray-700 mb-4">Favicon (32x32px)</label>
                    <div class="mb-4 flex justify-center">
                        @if(!empty($configs['plataforma_favicon']))
                            <img src="{{ asset('storage/' . $configs['plataforma_favicon']) }}" class="h-8 w-8 object-contain" alt="Favicon">
                        @else
                            <div class="h-8 w-8 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-[10px]">Icon</div>
                        @endif
                    </div>
                    <input type="file" name="plataforma_favicon" class="text-xs file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                    <h4 class="text-sm font-bold text-blue-800 mb-2">Dica de White Label</h4>
                    <p class="text-xs text-blue-700 leading-relaxed">
                        Essas configurações definem o que os usuários das lojas verão no cabeçalho do painel CRM. 
                        A logo da loja será exibida apenas no catálogo público e áreas contextuais.
                    </p>
                </div>

            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-lg">
                Salvar Identidade Global
            </button>
        </div>
    </form>
</x-layouts.super-admin>
