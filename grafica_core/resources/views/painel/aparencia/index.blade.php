{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-11 01:00
--}}
<x-layouts.app titulo="Aparência - {{ $configs['empresa_nome'] ?? 'Gráfica' }}">

    <div class="mb-8 p-6 bg-amber-50 rounded-2xl border border-amber-100">
        <h1 class="text-3xl font-black text-brand-secondary tracking-tight">Identidade da Loja</h1>
        <p class="text-sm text-amber-700 mt-1 font-medium">As configurações abaixo personalizam apenas o seu **Catálogo Público** e **Páginas de Venda**. O sistema CRM mantém a marca global {{ $configPlataforma['plataforma_nome'] ?? 'VaptCRM' }}.</p>
    </div>

    <form action="{{ route('admin.system.aparencia.update') }}" method="POST" enctype="multipart/form-data" class="max-w-3xl space-y-3">
        @csrf

        {{-- 1. IMAGEM DE CAPA DA LOJA --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" data-accordion>
            <button type="button" class="w-full flex items-center justify-between p-5 text-left hover:bg-slate-50/50 transition-colors" data-accordion-toggle>
                <div class="flex items-center gap-4">
                    <div class="h-11 w-11 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800">Capa do Catálogo</p>
                        <p class="text-xs text-slate-400 font-medium">Banner principal exibido para seus clientes</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold px-3 py-1 rounded-full {{ !empty($configs['aparencia_capa']) ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                        {{ !empty($configs['aparencia_capa']) ? 'Configurado' : 'Sem imagem' }}
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" data-accordion-icon fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </div>
            </button>
            <div class="hidden border-t border-slate-100" data-accordion-content>
                <div class="p-5 space-y-4">
                    @if(!empty($configs['aparencia_capa']))
                        <div class="rounded-xl overflow-hidden border border-slate-200 relative group">
                            <img src="{{ asset('storage/' . $configs['aparencia_capa']) }}" class="w-full h-40 object-cover" alt="Capa atual">
                            <label class="absolute top-3 right-3 flex items-center gap-1.5 bg-white/90 backdrop-blur rounded-lg px-3 py-1.5 text-xs text-rose-500 font-bold cursor-pointer shadow-sm hover:bg-white transition-colors">
                                <input type="checkbox" name="remover_aparencia_capa" value="1" class="rounded border-slate-300 text-rose-500 focus:ring-rose-500">
                                Remover
                            </label>
                        </div>
                    @else
                        <div class="h-40 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 flex flex-col items-center justify-center gap-2">
                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                            <p class="text-xs text-slate-400 font-medium">Nenhuma imagem de capa</p>
                        </div>
                    @endif
                    <input type="file" name="aparencia_capa" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20 cursor-pointer">
                    <div class="flex items-start gap-2 p-3 bg-blue-50/50 rounded-xl">
                        <svg class="w-4 h-4 text-blue-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                        <div class="text-[11px] text-blue-600 font-medium leading-relaxed">
                            <p><strong>Tamanho ideal:</strong> 1500 x 500 px (proporção 3:1)</p>
                            <p><strong>Peso máximo:</strong> 2MB por imagem</p>
                            <p><strong>Formatos:</strong> JPG, PNG ou WebP</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. LOGOTIPO DA SUA LOJA --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" data-accordion>
            <button type="button" class="w-full flex items-center justify-between p-5 text-left hover:bg-slate-50/50 transition-colors" data-accordion-toggle>
                <div class="flex items-center gap-4">
                    <div class="h-11 w-11 rounded-xl bg-rose-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800">Marca da Loja (Logos)</p>
                        <p class="text-xs text-slate-400 font-medium">Branding exibido no cabeçalho e rodapé público</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold px-3 py-1 rounded-full {{ !empty($configs['aparencia_logo']) ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                        {{ !empty($configs['aparencia_logo']) ? 'Configurado' : 'Sem logo' }}
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" data-accordion-icon fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </div>
            </button>
            <div class="hidden border-t border-slate-100" data-accordion-content>
                <div class="p-5 space-y-5">
                    {{-- Logo Principal --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Logo Principal da Loja</label>
                        @if(!empty($configs['aparencia_logo']))
                            <div class="mb-3 p-4 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-4">
                                <img src="{{ asset('storage/' . $configs['aparencia_logo']) }}" class="h-12 w-auto object-contain" alt="Logo atual">
                                <label class="flex items-center gap-2 text-xs text-rose-500 font-bold cursor-pointer">
                                    <input type="checkbox" name="remover_aparencia_logo" value="1" class="rounded border-slate-300 text-rose-500 focus:ring-rose-500">
                                    Remover
                                </label>
                            </div>
                        @endif
                        <input type="file" name="aparencia_logo" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-brand-primary/10 file:text-brand-primary hover:file:bg-brand-primary/20 cursor-pointer">
                        <p class="text-[11px] text-slate-400 mt-2">Exibida no topo do Catálogo Público.</p>
                    </div>

                    {{-- Logo do Rodapé --}}
                    <div class="pt-4 border-t border-slate-100">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Logo do Rodapé da Loja</label>
                        @if(!empty($configs['aparencia_logo_rodape']))
                            <div class="mb-3 p-4 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-4">
                                <img src="{{ asset('storage/' . $configs['aparencia_logo_rodape']) }}" class="h-10 w-auto object-contain grayscale" alt="Logo rodapé atual">
                                <label class="flex items-center gap-2 text-xs text-rose-500 font-bold cursor-pointer">
                                    <input type="checkbox" name="remover_aparencia_logo_rodape" value="1" class="rounded border-slate-300 text-rose-500 focus:ring-rose-500">
                                    Remover
                                </label>
                            </div>
                        @endif
                        <input type="file" name="aparencia_logo_rodape" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-slate-100 file:text-slate-600 hover:file:bg-slate-200 cursor-pointer">
                    </div>

                    {{-- Favicon --}}
                    <div class="pt-4 border-t border-slate-100">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Favicon da Loja (Browser)</label>
                        @if(!empty($configs['aparencia_favicon']))
                            <div class="mb-3 p-4 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-4">
                                <img src="{{ asset('storage/' . $configs['aparencia_favicon']) }}" class="h-8 w-8 object-contain" alt="Favicon atual">
                                <label class="flex items-center gap-2 text-xs text-rose-500 font-bold cursor-pointer">
                                    <input type="checkbox" name="remover_aparencia_favicon" value="1" class="rounded border-slate-300 text-rose-500 focus:ring-rose-500">
                                    Remover
                                </label>
                            </div>
                        @endif
                        <input type="file" name="aparencia_favicon" accept="image/png,image/x-icon,image/svg+xml" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-slate-100 file:text-slate-600 hover:file:bg-slate-200 cursor-pointer">
                        <p class="text-[11px] text-slate-400 mt-2">Ícone da aba. Se vazio, o catálogo usará o ícone do {{ $configPlataforma['plataforma_nome'] ?? 'VaptCRM' }}.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. DADOS DA EMPRESA (P/ DOCUMENTOS E PÁGINAS) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" data-accordion>
            <button type="button" class="w-full flex items-center justify-between p-5 text-left hover:bg-slate-50/50 transition-colors" data-accordion-toggle>
                <div class="flex items-center gap-4">
                    <div class="h-11 w-11 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800">Dados da Loja (Documentos)</p>
                        <p class="text-xs text-slate-400 font-medium">Nome, CNPJ, Endereço e Contatos da sua empresa</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold px-3 py-1 rounded-full {{ !empty($configs['empresa_nome']) ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                        {{ !empty($configs['empresa_nome']) ? 'Configurado' : 'Não configurado' }}
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" data-accordion-icon fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </div>
            </button>
            <div class="hidden border-t border-slate-100" data-accordion-content>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Nome da empresa</label>
                        <input type="text" name="empresa_nome" value="{{ $configs['empresa_nome'] ?? '' }}" placeholder="Ex: Gráfica Express Ltda" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Telefone</label>
                        <input type="text" name="empresa_telefone" value="{{ $configs['empresa_telefone'] ?? '' }}" placeholder="Ex: (11) 98765-4321" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Endereço completo</label>
                        <textarea name="empresa_endereco" rows="2" placeholder="Ex: Rua das Flores, 123, Sala 45, Centro" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">{{ $configs['empresa_endereco'] ?? '' }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Cidade / UF</label>
                            <input type="text" name="empresa_cidade_uf" value="{{ $configs['empresa_cidade_uf'] ?? '' }}" placeholder="Alagoinhas - BA" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">CEP</label>
                            <input type="text" name="empresa_cep" value="{{ $configs['empresa_cep'] ?? '' }}" placeholder="48000-000" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">CNPJ</label>
                        <input type="text" name="empresa_cnpj" value="{{ $configs['empresa_cnpj'] ?? '' }}" placeholder="Ex: 12.345.678/0001-99" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">E-mail</label>
                        <input type="email" name="empresa_email" value="{{ $configs['empresa_email'] ?? '' }}" placeholder="contato@suaempresa.com.br" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">WhatsApp (DDI + DDD + Número)</label>
                        <input type="text" name="empresa_whatsapp" value="{{ $configs['empresa_whatsapp'] ?? '' }}" placeholder="5575999279354" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Chave PIX (opcional)</label>
                        <input type="text" name="empresa_pix_chave" value="{{ $configs['empresa_pix_chave'] ?? '' }}" placeholder="email@exemplo.com.br ou CPF/CNPJ" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                        <p class="text-[11px] text-slate-400 mt-1">Será exibido apenas no PDF de Pedidos (não aparece em Orçamentos)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Tipo da Chave PIX</label>
                        <select name="empresa_pix_tipo" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                            <option value="">Selecione...</option>
                            <option value="cpf" {{ ($configs['empresa_pix_tipo'] ?? '') === 'cpf' ? 'selected' : '' }}>CPF</option>
                            <option value="cnpj" {{ ($configs['empresa_pix_tipo'] ?? '') === 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                            <option value="email" {{ ($configs['empresa_pix_tipo'] ?? '') === 'email' ? 'selected' : '' }}>E-mail</option>
                            <option value="telefone" {{ ($configs['empresa_pix_tipo'] ?? '') === 'telefone' ? 'selected' : '' }}>Telefone</option>
                            <option value="aleatoria" {{ ($configs['empresa_pix_tipo'] ?? '') === 'aleatoria' ? 'selected' : '' }}>Chave Aleatória</option>
                        </select>
                    </div>

                    {{-- Links sociais --}}
                    <div class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Site / URL</label>
                            <input type="url" name="empresa_site" value="{{ $configs['empresa_site'] ?? '' }}" placeholder="https://www.minhagrafica.com.br" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Instagram</label>
                            <input type="text" name="empresa_instagram" value="{{ $configs['empresa_instagram'] ?? '' }}" placeholder="https://instagram.com/suagrafica" class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">
                        </div>
                    </div>

                    <div class="flex items-start gap-2 p-3 bg-slate-50 rounded-xl mt-2">
                        <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                        <p class="text-[11px] text-slate-500 font-medium">Todos os campos são opcionais. Apenas os campos preenchidos serão exibidos nos PDFs.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. TEMA DO CATÁLOGO --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" data-accordion>
            <button type="button" class="w-full flex items-center justify-between p-5 text-left hover:bg-slate-50/50 transition-colors" data-accordion-toggle>
                <div class="flex items-center gap-4">
                    <div class="h-11 w-11 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800">Tema do Catálogo</p>
                        <p class="text-xs text-slate-400 font-medium">Como seus produtos são exibidos</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @php $layoutAtual = $configs['aparencia_layout_catalogo'] ?? 'grid'; @endphp
                    <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-600">
                        {{ $layoutAtual === 'grid' ? 'Grade' : ($layoutAtual === 'lista' ? 'Lista' : 'Misto') }}
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" data-accordion-icon fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </div>
            </button>
            <div class="hidden border-t border-slate-100" data-accordion-content>
                <div class="p-5 space-y-5">
                    {{-- Layout --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">Layout do Catálogo</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="aparencia_layout_catalogo" value="grid" {{ $layoutAtual === 'grid' ? 'checked' : '' }} class="peer sr-only">
                                <div class="peer-checked:ring-2 peer-checked:ring-brand-primary peer-checked:border-brand-primary rounded-xl border-2 border-slate-200 p-4 text-center transition-all hover:border-slate-300">
                                    <svg class="w-6 h-6 mx-auto mb-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                                    <span class="text-xs font-black text-slate-700">Grade</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="aparencia_layout_catalogo" value="lista" {{ $layoutAtual === 'lista' ? 'checked' : '' }} class="peer sr-only">
                                <div class="peer-checked:ring-2 peer-checked:ring-brand-primary peer-checked:border-brand-primary rounded-xl border-2 border-slate-200 p-4 text-center transition-all hover:border-slate-300">
                                    <svg class="w-6 h-6 mx-auto mb-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                    <span class="text-xs font-black text-slate-700">Lista</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="aparencia_layout_catalogo" value="misto" {{ $layoutAtual === 'misto' ? 'checked' : '' }} class="peer sr-only">
                                <div class="peer-checked:ring-2 peer-checked:ring-brand-primary peer-checked:border-brand-primary rounded-xl border-2 border-slate-200 p-4 text-center transition-all hover:border-slate-300">
                                    <svg class="w-6 h-6 mx-auto mb-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                                    <span class="text-xs font-black text-slate-700">Misto</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. TEXTO DO RODAPÉ --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" data-accordion>
            <button type="button" class="w-full flex items-center justify-between p-5 text-left hover:bg-slate-50/50 transition-colors" data-accordion-toggle>
                <div class="flex items-center gap-4">
                    <div class="h-11 w-11 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800">Texto do Rodapé</p>
                        <p class="text-xs text-slate-400 font-medium">Personalize os textos exibidos no catálogo</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold px-3 py-1 rounded-full {{ !empty($configs['aparencia_rodape_texto']) ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                        {{ !empty($configs['aparencia_rodape_texto']) ? 'Personalizado' : 'Padrão' }}
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" data-accordion-icon fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </div>
            </button>
            <div class="hidden border-t border-slate-100" data-accordion-content>
                <div class="p-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Texto exibido no rodapé do catálogo</label>
                    <textarea name="aparencia_rodape_texto" rows="3" maxlength="500" placeholder="Ex: Soluções gráficas rápidas, seguras e com qualidade premium para sua marca." class="w-full rounded-xl border-slate-200 focus:border-brand-primary text-sm">{{ $configs['aparencia_rodape_texto'] ?? '' }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Máximo 500 caracteres</p>
                </div>
            </div>
        </div>

        {{-- 6. CORES DO CATÁLOGO --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" data-accordion>
            <button type="button" class="w-full flex items-center justify-between p-5 text-left hover:bg-slate-50/50 transition-colors" data-accordion-toggle>
                <div class="flex items-center gap-4">
                    <div class="h-11 w-11 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800">Cores do Catálogo</p>
                        <p class="text-xs text-slate-400 font-medium">Cores para modo escuro e claro</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="h-6 w-6 rounded-full border-2 border-white shadow-sm" style="background-color: {{ $configs['aparencia_cor_primaria'] ?? '#FF7A00' }}"></span>
                        <span class="h-6 w-6 rounded-full border-2 border-white shadow-sm" style="background-color: {{ $configs['aparencia_cor_secundaria'] ?? '#1E293B' }}"></span>
                    </div>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" data-accordion-icon fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </div>
            </button>
            <div class="hidden border-t border-slate-100" data-accordion-content>
                <div class="p-5 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Cor Primária</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="aparencia_cor_primaria" value="{{ $configs['aparencia_cor_primaria'] ?? '#FF7A00' }}" class="h-11 w-14 rounded-xl border border-slate-200 cursor-pointer p-1">
                                <input type="text" value="{{ $configs['aparencia_cor_primaria'] ?? '#FF7A00' }}" class="flex-1 rounded-xl border-slate-200 text-sm font-mono font-bold text-slate-600 bg-slate-50" readonly id="hex_primaria">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Cor Secundária</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="aparencia_cor_secundaria" value="{{ $configs['aparencia_cor_secundaria'] ?? '#1E293B' }}" class="h-11 w-14 rounded-xl border border-slate-200 cursor-pointer p-1">
                                <input type="text" value="{{ $configs['aparencia_cor_secundaria'] ?? '#1E293B' }}" class="flex-1 rounded-xl border-slate-200 text-sm font-mono font-bold text-slate-600 bg-slate-50" readonly id="hex_secundaria">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Cor de Destaque</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="aparencia_cor_destaque" value="{{ $configs['aparencia_cor_destaque'] ?? '#F59E0B' }}" class="h-11 w-14 rounded-xl border border-slate-200 cursor-pointer p-1">
                                <input type="text" value="{{ $configs['aparencia_cor_destaque'] ?? '#F59E0B' }}" class="flex-1 rounded-xl border-slate-200 text-sm font-mono font-bold text-slate-600 bg-slate-50" readonly id="hex_destaque">
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-2" id="preview-cores">
                        <div class="h-10 w-10 rounded-xl shadow-inner border" style="background-color: {{ $configs['aparencia_cor_primaria'] ?? '#FF7A00' }}" id="prev_primaria"></div>
                        <div class="h-10 w-10 rounded-xl shadow-inner border" style="background-color: {{ $configs['aparencia_cor_secundaria'] ?? '#1E293B' }}" id="prev_secundaria"></div>
                        <div class="h-10 w-10 rounded-xl shadow-inner border" style="background-color: {{ $configs['aparencia_cor_destaque'] ?? '#F59E0B' }}" id="prev_destaque"></div>
                        <span class="text-xs text-slate-400 font-bold ml-2">Pré-visualização</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTÃO SALVAR (fixo no rodapé) --}}
        <div class="pt-4">
            <button type="submit" class="w-full sm:w-auto px-10 py-3.5 bg-brand-primary text-white font-black rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all active:scale-[0.98] text-sm">
                Salvar Configurações
            </button>
        </div>
    </form>

    <script>
        // Accordion toggle
        document.querySelectorAll('[data-accordion-toggle]').forEach(btn => {
            btn.addEventListener('click', () => {
                const accordion = btn.closest('[data-accordion]');
                const content = accordion.querySelector('[data-accordion-content]');
                const icon = btn.querySelector('[data-accordion-icon]');
                const isOpen = !content.classList.contains('hidden');

                if (isOpen) {
                    content.classList.add('hidden');
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    content.classList.remove('hidden');
                    icon.style.transform = 'rotate(180deg)';
                }
            });
        });

        // Sincroniza color picker com campo hex e preview
        document.querySelectorAll('input[type="color"]').forEach(picker => {
            const nome = picker.name.replace('aparencia_cor_', '');
            const hexField = document.getElementById('hex_' + nome);
            const preview = document.getElementById('prev_' + nome);

            if (hexField && preview) {
                picker.addEventListener('input', function () {
                    hexField.value = this.value;
                    preview.style.backgroundColor = this.value;
                });
            }
        });
    </script>
</x-layouts.app>
