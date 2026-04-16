{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 15/04/2026 (Evolução Profissional do Cadastro de Produtos)
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary flex items-center gap-3">
                <span class="p-2 bg-brand-primary/10 rounded-xl text-brand-primary">📦</span>
                Novo Produto Profissional
            </h1>
            <p class="text-slate-500 font-medium">Modelagem técnica e comercial adaptada para o mercado gráfico.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.catalog.produtos.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <button type="submit" form="form-produto" class="rounded-xl bg-gradient-to-r from-brand-primary to-orange-500 px-6 py-2.5 text-sm font-black text-white shadow-lg shadow-brand-primary/20 hover:scale-105 transition-all">
                Salvar Produto 🚀
            </button>
        </div>
    </div>

    @if(session('erro'))
        <div class="mb-6 rounded-2xl bg-red-50 border border-red-100 p-4 text-red-600 font-bold flex items-center gap-3 animate-fade-in">
            <i class="fas fa-exclamation-triangle text-xl"></i> {{ session('erro') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Navegação de Seções (Sticky Desktop) -->
        <aside class="lg:col-span-3 hidden lg:block sticky top-24">
            <nav class="space-y-1 bg-white/50 backdrop-blur-md p-2 rounded-2xl border border-slate-200" id="nav-secoes">
                <a href="#secao-identificacao" class="nav-item active">Identificação</a>
                <a href="#secao-modo" class="nav-item">Modo de Cadastro</a>
                <a href="#secao-comercial" class="nav-item">Venda e Preço</a>
                <a href="#secao-tecnica" class="nav-item hidden-simple">Especificações Técnicas</a>
                <a href="#secao-materiais" class="nav-item hidden-simple">Materiais</a>
                <a href="#secao-acabamentos" class="nav-item hidden-simple">Acabamentos</a>
                <a href="#secao-variacoes" class="nav-item hidden-simple">Variações e Quantidades</a>
                <a href="#secao-producao" class="nav-item">Produção</a>
                <a href="#secao-marketing" class="nav-item">Marketing e SEO</a>
                <a href="#secao-galeria" class="nav-item">Galeria Visual</a>
            </nav>

            <div class="mt-6 p-4 rounded-2xl border border-brand-primary/20 bg-brand-primary/5">
                <h4 class="text-xs font-black text-brand-primary uppercase tracking-widest mb-2">Completude do Cadastro</h4>
                <div class="w-full bg-slate-200 rounded-full h-1.5 mb-2">
                    <div class="bg-brand-primary h-1.5 rounded-full" style="width: 15%"></div>
                </div>
                <p class="text-[10px] text-slate-500 font-bold leading-tight">Preencha os campos obrigatórios para liberar a visibilidade completa.</p>
            </div>
        </aside>

        <!-- Formulário Principal -->
        <div class="lg:col-span-9">
            <form id="form-produto" action="{{ route('admin.catalog.produtos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8 pb-20">
                @csrf
                
                <!-- 1. IDENTIFICAÇÃO COMERCIAL -->
                <section id="secao-identificacao" class="card-secao">
                    <div class="card-header bg-slate-50 border-b p-5">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-tag text-brand-primary"></i> Identificação Comercial
                        </h2>
                    </div>
                    <div class="card-body p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="label-form">Nome do Produto <span class="text-red-500">*</span></label>
                            <input type="text" name="nome" value="{{ old('nome') }}" required placeholder="Ex: Panfleto Promocional" class="input-modern">
                        </div>
                        <div class="md:col-span-2">
                            <label class="label-form">Subtítulo Comercial</label>
                            <input type="text" name="subtitulo_comercial" value="{{ old('subtitulo_comercial') }}" placeholder="Ex: Cor Brilhante e Papel de Alta Gramatura" class="input-modern">
                        </div>
                        <div>
                            <label class="label-form">Categoria <span class="text-red-500">*</span></label>
                            <select name="categoria_id" required class="select-modern">
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label-form">Visibilidade na Loja <span class="text-red-500">*</span></label>
                            <select name="visibilidade" required class="select-modern">
                                <option value="ambos">Híbrido (Interno e Catálogo)</option>
                                <option value="interno">Somente Interno (Painel/PDV)</option>
                                <option value="publico">Somente Catálogo Público</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- 2. MODO DE CADASTRO -->
                <section id="secao-modo" class="card-secao border-2 border-brand-primary shadow-xl shadow-brand-primary/5">
                    <div class="card-header bg-brand-primary p-5 text-white">
                        <h2 class="text-lg font-black flex items-center gap-2">
                            <i class="fas fa-layer-group"></i> Modo de Cadastro e Segmento
                        </h2>
                        <p class="text-xs font-bold text-white/80">O modo de cadastro define a complexidade da precificação e campos técnicos.</p>
                    </div>
                    <div class="card-body p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="label-form">Tipo de Modelagem <span class="text-red-500">*</span></label>
                            <select name="modelo_cadastro" id="modelo_cadastro" class="select-modern !border-brand-primary !ring-1 !ring-brand-primary/20">
                                <option value="simples" {{ old('modelo_cadastro') == 'simples' ? 'selected' : '' }}>🟢 Nível 1 - Produto Simples (Preço Fixo)</option>
                                <option value="configuravel" {{ old('modelo_cadastro') == 'configuravel' ? 'selected' : '' }} @if(!$canAdvanced) disabled @endif>🔵 Nível 2 - Configurável (M2 / Variações) @if(!$canAdvanced) 🔒 PRO @endif</option>
                                <option value="tecnico" {{ old('modelo_cadastro') == 'tecnico' ? 'selected' : '' }} @if(!$canTechnical) disabled @endif>🟣 Nível 3 - Técnico (Avancado / Indústria) @if(!$canTechnical) 🔒 PREMIUM @endif</option>
                            </select>
                            <p class="mt-2 text-[10px] font-bold text-slate-400 uppercase leading-none" id="modelo-info-text">Ideal para serviços rápidos e produtos de balcão.</p>
                        </div>
                        <div>
                            <label class="label-form">Segmento de Mercado</label>
                            <select name="segmento" class="select-modern">
                                <option value="grafica_rapida">Gráfica Rápida</option>
                                <option value="comunicacao_visual">Comunicação Visual</option>
                                <option value="grafica_industrial">Indústria Gráfica / Offset</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- 3. DADOS BÁSICOS DE VENDA -->
                <section id="secao-comercial" class="card-secao">
                    <div class="card-header bg-slate-50 border-b p-5">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-hand-holding-usd text-brand-primary"></i> Venda e Preço
                        </h2>
                    </div>
                    <div class="card-body p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="label-form">Preço de Venda Base (R$)</label>
                                <input type="number" step="0.01" name="preco_base" value="{{ old('preco_base', '0.00') }}" class="input-modern font-black text-brand-primary">
                            </div>
                            <div>
                                <label class="label-form">Unidade de Venda</label>
                                <select name="unidade_venda" class="select-modern">
                                    <option value="unidade">Por Unidade</option>
                                    <option value="cento">Por Cento (100)</option>
                                    <option value="milheiro">Por Milheiro (1000)</option>
                                    <option value="m2">Por Metro Quadrado (m²)</option>
                                    <option value="metro_linear">Por Metro Linear (m)</option>
                                    <option value="pacote">Por Pacote</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-form">Prazo de Produção</label>
                                <input type="text" name="prazo_estimado" value="{{ old('prazo_estimado') }}" placeholder="Ex: 2 dias úteis" class="input-modern">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="flex items-start gap-3">
                                <input type="hidden" name="exige_arte" value="0">
                                <input type="checkbox" name="exige_arte" id="exige_arte" value="1" class="mt-1 w-5 h-5 text-brand-primary border-slate-300 rounded focus:ring-brand-primary" {{ old('exige_arte') ? 'checked' : '' }}>
                                <div>
                                    <label for="exige_arte" class="font-bold text-slate-800 cursor-pointer">Requer arte do cliente?</label>
                                    <p class="text-xs text-slate-500 font-medium">Exibe campo de upload opcional no catálogo.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <input type="hidden" name="oferece_design" value="0">
                                <input type="checkbox" name="oferece_design" id="oferece_design" value="1" class="mt-1 w-5 h-5 text-brand-primary border-slate-300 rounded focus:ring-brand-primary" {{ old('oferece_design') ? 'checked' : '' }}>
                                <div>
                                    <label for="oferece_design" class="font-bold text-slate-800 cursor-pointer">Oferecer serviço de criação?</label>
                                    <p class="text-xs text-slate-500 font-medium">Ativa cobrança adicional de design.</p>
                                </div>
                            </div>
                        </div>

                        <div id="row-arte" class="grid grid-cols-1 md:grid-cols-2 gap-5 {{ old('oferece_design') || old('exige_arte') ? '' : 'hidden' }}">
                            <div>
                                <label class="label-form">Preço de Venda da Arte (R$)</label>
                                <input type="number" step="0.01" name="preco_arte" value="{{ old('preco_arte', '0.00') }}" class="input-modern">
                            </div>
                            <div>
                                <label class="label-form">Custo Interno da Arte (R$)</label>
                                <input type="number" step="0.01" name="custo_design" value="{{ old('custo_design', '0.00') }}" class="input-modern">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 4. ESPECIFICAÇÕES TÉCNICAS (Hidden for Simple) -->
                <section id="secao-tecnica" class="card-secao secao-avancada hidden">
                    <div class="card-header bg-slate-50 border-b p-5">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-microchip text-brand-primary"></i> Especificações Técnicas
                        </h2>
                    </div>
                    <div class="card-body p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                            <div>
                                <label class="label-form">Largura (mm)</label>
                                <input type="number" step="0.01" name="largura" id="largura" class="input-modern">
                            </div>
                            <div>
                                <label class="label-form">Altura (mm)</label>
                                <input type="number" step="0.01" name="altura" id="altura" class="input-modern">
                            </div>
                            <div>
                                <label class="label-form">Área Total (m²)</label>
                                <input type="text" name="area_m2" id="area_m2" readonly class="input-modern bg-slate-50 font-bold">
                            </div>
                            <div>
                                <label class="label-form">Gramatura (g)</label>
                                <input type="number" name="gramatura" class="input-modern" placeholder="Ex: 250">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="label-form">Orientação</label>
                                <select name="orientacao" class="select-modern">
                                    <option value="vertical">Vertical (Em pé)</option>
                                    <option value="horizontal">Horizontal (Deitado)</option>
                                    <option value="quadrado">Quadrado</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-form">Cores de Impressão</label>
                                <input type="text" name="cor_impressao" placeholder="Ex: 4x0, 4x4, 1x0" class="input-modern">
                            </div>
                            <div>
                                <label class="label-form">Tipo de Impressão</label>
                                <input type="text" name="tipo_impressao" placeholder="Ex: Digital, Silk, UV" class="input-modern">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 5. MATERIAIS -->
                <section id="secao-materiais" class="card-secao secao-avancada hidden">
                    <div class="card-header bg-slate-50 border-b p-5 flex justify-between items-center">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-scroll text-brand-primary"></i> Materiais e Substratos
                        </h2>
                        <button type="button" onclick="adicionarLinha('container-materiais', 'materiais')" class="text-xs font-black px-3 py-1.5 bg-brand-primary text-white rounded-lg">+ Add Material</button>
                    </div>
                    <div class="card-body p-6">
                        <div id="container-materiais" class="space-y-3">
                            <!-- Injetado via JS -->
                        </div>
                    </div>
                </section>

                <!-- 6. ACABAMENTOS -->
                <section id="secao-acabamentos" class="card-secao secao-avancada hidden">
                    <div class="card-header bg-slate-50 border-b p-5 flex justify-between items-center">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-magic text-brand-primary"></i> Acabamentos Adicionais
                        </h2>
                        <button type="button" onclick="adicionarLinha('container-acabamentos', 'acabamentos')" class="text-xs font-black px-3 py-1.5 bg-brand-primary text-white rounded-lg">+ Add Acabamento</button>
                    </div>
                    <div class="card-body p-6">
                        <div id="container-acabamentos" class="space-y-3">
                            <!-- Injetado via JS -->
                        </div>
                    </div>
                </section>

                <!-- 7. VARIAÇÕES E QUANTIDADES -->
                <section id="secao-variacoes" class="card-secao secao-avancada hidden">
                    <div class="card-header bg-slate-100 border-b p-5 flex justify-between items-center">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-cubes text-brand-primary"></i> Grupos de Variação & Faixas
                        </h2>
                        <div class="flex gap-2">
                            <button type="button" onclick="adicionarGrupoVariacao()" class="text-xs font-black px-3 py-1.5 bg-blue-600 text-white rounded-lg">+ Novo Grupo</button>
                            <button type="button" onclick="adicionarFaixaQuantidade()" class="text-xs font-black px-3 py-1.5 bg-emerald-600 text-white rounded-lg secao-tecnica-only hidden">+ Nova Faixa</button>
                        </div>
                    </div>
                    <div class="card-body p-6 space-y-8">
                        <div>
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Variações Estruturadas</h4>
                            <div id="container-grupos-variacao" class="space-y-6">
                                <!-- Injetado via JS -->
                            </div>
                        </div>

                        <div class="secao-tecnica-only hidden pt-6 border-t">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Faixas de Tiragem (Preço x Quantidade)</h4>
                            <div id="container-faixas-quantidade" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                                <!-- Injetado via JS -->
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 8. PRODUÇÃO -->
                <section id="secao-producao" class="card-secao">
                    <div class="card-header bg-slate-50 border-b p-5">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-industry text-brand-primary"></i> Produção Interna
                        </h2>
                    </div>
                    <div class="card-body p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="label-form">Modo de Produção</label>
                            <select name="modo_producao" class="select-modern">
                                <option value="digital">Impressão Digital</option>
                                <option value="offset">Impressão Offset</option>
                                <option value="comunicacao_visual">Comunicação Visual</option>
                                <option value="terceirizado">Serviço Terceirizado</option>
                                <option value="outro">Outro Modo</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="label-form">Checklist de Verificação (Produção)</label>
                            <textarea name="checklist_producao" rows="2" class="input-modern" placeholder="Ex: Conferir margem; Verificar sangria; Checar ilhós"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="label-form">Instruções Técnicas de Impressão</label>
                            <textarea name="instrucoes_internas" rows="3" class="input-modern" placeholder="Instruções para o operador da máquina..."></textarea>
                        </div>
                    </div>
                </section>

                <!-- 9. MARKETING E SEO -->
                <section id="secao-marketing" class="card-secao">
                    <div class="card-header bg-slate-50 border-b p-5">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-bullhorn text-brand-primary"></i> Marketing, Catálogo e SEO
                        </h2>
                    </div>
                    <div class="card-body p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="label-form">Badge Comercial</label>
                                <input type="text" name="badge_comercial" placeholder="Ex: Oferta, Top 1" class="input-modern">
                            </div>
                            <div class="md:col-span-2">
                                <label class="label-form">Chamada de Efeito (Curta)</label>
                                <input type="text" name="frase_efeito" placeholder="Ex: Impacte seus clientes com qualidade!" class="input-modern">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-orange-50 rounded-2xl border border-orange-100">
                            <div class="flex items-start gap-3">
                                <input type="hidden" name="destaque" value="0">
                                <input type="checkbox" name="destaque" id="destaque" value="1" class="mt-1 w-5 h-5 text-brand-primary border-slate-300 rounded focus:ring-brand-primary" {{ old('destaque') ? 'checked' : '' }}>
                                <div>
                                    <label for="destaque" class="font-bold text-slate-800 cursor-pointer">Destacar na Home?</label>
                                    <p class="text-xs text-orange-600 font-medium">Produto aparecerá no topo do catálogo.</p>
                                </div>
                            </div>
                            <div>
                                <label class="label-form">Ordem de Exibição</label>
                                <input type="number" name="ordem_exibicao" value="0" class="input-modern">
                            </div>
                        </div>

                        <div class="space-y-4 pt-4 border-t">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">Otimização de Buscas (SEO)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="label-form">Meta Title (Título do Google)</label>
                                    <input type="text" name="meta_title" maxlength="70" class="input-modern">
                                </div>
                                <div>
                                    <label class="label-form">Meta Description</label>
                                    <input type="text" name="meta_description" maxlength="160" class="input-modern">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 10. GALERIA VISUAL -->
                <section id="secao-galeria" class="card-secao">
                    <div class="card-header bg-slate-50 border-b p-5">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-images text-brand-primary"></i> Galeria Visual
                        </h2>
                    </div>
                    <div class="card-body p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="label-form">Imagem Vitrine (A capa) <span class="text-red-500">*</span></label>
                            <label class="upload-box relative overflow-hidden col-span-2 h-48 border-brand-primary/30 bg-slate-50 hover:bg-white hover:border-brand-primary">
                                <div class="text-center group-hover:scale-110 transition">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-slate-300 mb-3"></i>
                                    <span class="block text-xs font-black text-slate-500">Clique para selecionar imagem</span>
                                </div>
                                <input type="file" name="imagem_destaque" accept="image/*" class="hidden" required>
                            </label>
                        </div>
                        <div>
                            <label class="label-form">Imagens da Galeria (Múltiplas)</label>
                            <label class="upload-box relative overflow-hidden col-span-2 h-48 border-slate-200 bg-slate-50 hover:bg-white hover:border-slate-300">
                                <div class="text-center">
                                    <i class="fas fa-images text-4xl text-slate-200 mb-3"></i>
                                    <span class="block text-xs font-black text-slate-400">Arraste outras imagens aqui</span>
                                </div>
                                <input type="file" name="imagens_adicionais[]" accept="image/*" multiple class="hidden">
                            </label>
                        </div>
                        <div class="md:col-span-2">
                            <label class="label-form">Descrição Completa / Ficha Técnica</label>
                            <textarea name="descricao_completa" rows="6" class="input-modern" placeholder="Escreva o texto descritivo longo que aparecerá para o cliente no site..."></textarea>
                        </div>
                    </div>
                </section>

                <!-- Footer de Ações Mobile -->
                <div class="fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-md p-4 border-t border-slate-200 lg:hidden flex gap-3 shadow-[0_-5px_15px_rgba(0,0,0,0.05)]">
                    <button type="submit" class="flex-1 rounded-xl bg-brand-primary py-3 text-sm font-black text-white shadow-lg">Salvar Produto 🚀</button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
    <style>
        .card-secao { @apply bg-white rounded-3xl border border-slate-200 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden transition-all duration-300; }
        .nav-item { @apply flex items-center px-4 py-3 text-sm font-black text-slate-500 rounded-xl transition-all duration-300 border border-transparent; }
        .nav-item:hover { @apply text-brand-primary bg-brand-primary/5; }
        .nav-item.active { @apply text-white bg-brand-secondary border-brand-secondary shadow-lg shadow-brand-secondary/10; }
        
        .input-modern { @apply w-full rounded-2xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm focus:border-brand-primary focus:bg-orange-50/20 focus:ring-4 focus:ring-brand-primary/10 focus:outline-none transition-all duration-200; hover:border-slate-300 }
        .select-modern { @apply w-full rounded-2xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-800 shadow-sm focus:border-brand-primary focus:bg-orange-50/20 focus:ring-4 focus:ring-brand-primary/10 focus:outline-none transition-all duration-200 appearance-none cursor-pointer; hover:border-slate-300 }
        .label-form { @apply mb-2 block text-[11px] font-black text-slate-500 uppercase tracking-wider pl-1; }
        
        .upload-box { @apply relative cursor-pointer w-full rounded-2xl border-2 border-dashed flex flex-col items-center justify-center transition-all duration-300 overflow-hidden bg-slate-50/50; }
        .upload-box.dragover { @apply border-brand-primary bg-brand-primary/5 scale-[1.02] shadow-lg shadow-brand-primary/10; }
        
        .nav-item.hidden-simple.show { @apply block; }
        .nav-item.hidden-simple { display: none; }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Script de Drag n Drop (Galeria)
            const inputVitrine = document.querySelector('input[name="imagem_destaque"]');
            const dropzoneVitrine = inputVitrine.closest('.upload-box');

            const inputGaleria = document.querySelector('input[name="imagens_adicionais[]"]');
            const dropzoneGaleria = inputGaleria.closest('.upload-box');

            // Eventos Visuais de Drag
            [dropzoneVitrine, dropzoneGaleria].forEach(dz => {
                dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('dragover'); });
                dz.addEventListener('dragleave', e => { e.preventDefault(); dz.classList.remove('dragover'); });
                dz.addEventListener('drop', e => { e.preventDefault(); dz.classList.remove('dragover'); });
            });

            // Preview Vitrine
            inputVitrine.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        dropzoneVitrine.innerHTML = `
                            <img src="${e.target.result}" class="absolute inset-0 w-full h-full object-cover z-0">
                            <div class="absolute inset-0 bg-black/40 flex-col items-center justify-center opacity-0 hover:opacity-100 transition-opacity z-10 flex">
                                <span class="text-white text-xs font-bold uppercase tracking-widest bg-black/60 px-3 py-1 rounded shadow-lg"><i class="fas fa-sync-alt mr-1"></i> Trocar Imagem</span>
                            </div>
                        `;
                        dropzoneVitrine.appendChild(inputVitrine); // Recoloque o input
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Preview Galeria Múltipla
            inputGaleria.addEventListener('change', function(e) {
                const files = e.target.files;
                if(files.length > 0) {
                    let htmlPreview = '<div class="absolute inset-x-2 inset-y-2 z-0 flex flex-wrap gap-2 overflow-y-auto content-start justify-center p-2">';
                    
                    Array.from(files).slice(0, 12).forEach(file => {
                        htmlPreview += `<div class="h-16 w-16 bg-white rounded-lg border-2 border-white shadow-md relative overflow-hidden animate-fade-in"><img src="${URL.createObjectURL(file)}" class="w-full h-full object-cover"></div>`;
                    });
                    
                    htmlPreview += '</div><div class="absolute inset-0 bg-black/70 flex-col items-center justify-center opacity-0 hover:opacity-100 transition-opacity z-10 flex text-white text-xs font-bold uppercase tracking-widest"><i class="fas fa-plus mb-1 text-lg"></i> Adicionar Mais</div>';
                    
                    dropzoneGaleria.innerHTML = htmlPreview;
                    dropzoneGaleria.appendChild(inputGaleria);
                }
            });

            const modeloSelect = document.getElementById('modelo_cadastro');
            const exigeArteCheck = document.getElementById('exige_arte');
            const ofereceDesignCheck = document.getElementById('oferece_design');
            const rowArte = document.getElementById('row-arte');
            
            // Sync Arte Row
            const syncArte = () => {
                rowArte.classList.toggle('hidden', !exigeArteCheck.checked && !ofereceDesignCheck.checked);
            };
            exigeArteCheck.addEventListener('change', syncArte);
            ofereceDesignCheck.addEventListener('change', syncArte);

            // Sync Modelo
            const syncModelo = () => {
                const modo = modeloSelect.value;
                const infoText = document.getElementById('modelo-info-text');
                const secoesAvancadas = document.querySelectorAll('.secao-avancada');
                const secoesTecnicas = document.querySelectorAll('.secao-tecnica-only');
                const navItemsAvancados = document.querySelectorAll('.nav-item.hidden-simple');

                if (modo === 'simples') {
                    infoText.innerText = "Ideal para serviços rápidos e produtos de balcão.";
                    secoesAvancadas.forEach(s => s.classList.add('hidden'));
                    navItemsAvancados.forEach(i => i.classList.remove('show'));
                } else if(modo === 'configuravel') {
                    infoText.innerText = "Habilita variações, materiais e acabamentos complexos.";
                    secoesAvancadas.forEach(s => s.classList.remove('hidden'));
                    secoesTecnicas.forEach(s => s.classList.add('hidden'));
                    navItemsAvancados.forEach(i => i.classList.add('show'));
                } else {
                    infoText.innerText = "Modo Industrial: Libera precificação por faixa, custos internos e campos técnicos.";
                    secoesAvancadas.forEach(s => s.classList.remove('hidden'));
                    secoesTecnicas.forEach(s => s.classList.remove('hidden'));
                    navItemsAvancados.forEach(i => i.classList.add('show'));
                }
            };
            modeloSelect.addEventListener('change', syncModelo);
            syncModelo();

            // Auto-calc Area
            const largura = document.getElementById('largura');
            const altura = document.getElementById('altura');
            const area = document.getElementById('area_m2');
            const calcArea = () => {
                if (largura.value && altura.value) {
                    area.value = ((largura.value * altura.value) / 1000000).toFixed(4) + ' m²';
                }
            };
            largura.addEventListener('input', calcArea);
            altura.addEventListener('input', calcArea);
        });

        let materialIdx = 0;
        let acabamentoIdx = 0;
        let faixaIdx = 0;
        let grupoIdx = 0;

        function adicionarLinha(idContainer, nome) {
            const container = document.getElementById(idContainer);
            const div = document.createElement('div');
            div.className = "flex gap-3 animate-fade-in";
            let idx = (nome === 'materiais') ? materialIdx++ : acabamentoIdx++;
            
            div.innerHTML = `
                <input type="text" name="${nome}[${idx}][nome]" placeholder="Nome do ${nome}" class="flex-1 input-modern !py-2">
                <input type="number" step="0.01" name="${nome}[${idx}][preco_ajuste]" placeholder="Ajuste R$" class="w-32 input-modern !py-2">
                ${nome === 'acabamentos' ? `<input type="number" name="${nome}[${idx}][prazo_ajuste]" placeholder="D+ Dias" class="w-24 input-modern !py-2">` : ''}
                <button type="button" onclick="this.parentElement.remove()" class="p-2 text-red-400 hover:text-red-500"><i class="fas fa-trash"></i></button>
            `;
            container.appendChild(div);
        }

        function adicionarFaixaQuantidade() {
            const container = document.getElementById('container-faixas-quantidade');
            const div = document.createElement('div');
            div.className = "p-4 bg-slate-50 rounded-xl border border-slate-200 grid grid-cols-2 gap-3 relative";
            let idx = faixaIdx++;
            
            div.innerHTML = `
                <div class="col-span-2 flex justify-between items-center mb-1">
                    <span class="font-black text-slate-400">FAIXA #${idx+1}</span>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-400"><i class="fas fa-times"></i></button>
                </div>
                <div>
                    <label class="label-form">Qtd Mínima</label>
                    <input type="number" name="faixas[${idx}][quantidade_minima]" class="input-modern !py-1 text-center">
                </div>
                <div>
                    <label class="label-form">Preço Unitário (R$)</label>
                    <input type="number" step="0.0001" name="faixas[${idx}][preco_unitario]" class="input-modern !py-1 text-center font-bold text-emerald-600">
                </div>
            `;
            container.appendChild(div);
        }

        function adicionarGrupoVariacao() {
            const container = document.getElementById('container-grupos-variacao');
            const div = document.createElement('div');
            div.className = "p-5 bg-white border border-slate-200 rounded-2xl shadow-sm relative group animate-fade-in";
            let idxGroup = grupoIdx++;

            div.innerHTML = `
                <button type="button" onclick="this.parentElement.remove()" class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition text-red-300 hover:text-red-500"><i class="fas fa-trash"></i></button>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label-form">Nome do Grupo</label>
                        <input type="text" name="grupos_variacao[${idxGroup}][nome_grupo]" placeholder="Ex: Papel, Cor, Formato" class="input-modern !py-2">
                    </div>
                    <div>
                        <label class="label-form">Tipo de Seleção</label>
                        <select name="grupos_variacao[${idxGroup}][tipo_exibicao]" class="select-modern !py-2">
                            <option value="select">Lista de Seleção (Dropdown)</option>
                            <option value="radio">Escolha Única (Botões)</option>
                            <option value="color">Cores</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-2 mt-4 pl-4 border-l-2 border-slate-100">
                    <div id="opcoes-grupo-${idxGroup}" class="space-y-2">
                        <!-- Opções -->
                    </div>
                    <button type="button" onclick="adicionarOpcaoVariacao(${idxGroup})" class="text-[10px] font-black text-blue-500 hover:text-blue-700 uppercase mt-2">+ Add Opção</button>
                </div>
            `;
            container.appendChild(div);
            adicionarOpcaoVariacao(idxGroup); // Add first option
        }

        let optionIdx = 0;
        function adicionarOpcaoVariacao(idxGroup) {
            const container = document.getElementById(`opcoes-grupo-${idxGroup}`);
            const div = document.createElement('div');
            div.className = "flex items-center gap-2";
            let idxOpt = optionIdx++;

            div.innerHTML = `
                <input type="text" name="grupos_variacao[${idxGroup}][opcoes][${idxOpt}][nome_opcao]" placeholder="Ex: Couchê 250g" class="flex-1 input-modern !py-1 text-xs">
                <input type="number" step="0.01" name="grupos_variacao[${idxGroup}][opcoes][${idxOpt}][acrescimo_preco]" placeholder="R$" class="w-20 input-modern !py-1 text-xs text-center">
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-300 hover:text-red-400"><i class="fas fa-times"></i></button>
            `;
            container.appendChild(div);
        }
    </script>
    @endpush
</x-layouts.app>
