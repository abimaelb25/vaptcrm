{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 15/04/2026 (Evolução Profissional do Cadastro de Produtos)
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 bg-brand-primary/10 text-brand-primary text-[10px] font-black rounded uppercase tracking-widest">Editando ID #{{ $produto->id }}</span>
                @if($produto->modelo_cadastro == 'simples')
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded uppercase tracking-widest">Nível 1 - Simples</span>
                @elseif($produto->modelo_cadastro == 'configuravel')
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-black rounded uppercase tracking-widest">Nível 2 - Configurável</span>
                @else
                    <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-[10px] font-black rounded uppercase tracking-widest">Nível 3 - Técnico</span>
                @endif
            </div>
            <h1 class="text-3xl font-black text-brand-secondary flex items-center gap-3">
                {{ $produto->nome }}
            </h1>
            <p class="text-slate-500 font-medium">Gerencie especificações técnicas, materiais e regras de precificação.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.catalog.produtos.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <button type="submit" form="form-produto" class="rounded-xl bg-slate-800 px-6 py-2.5 text-sm font-black text-white shadow-lg hover:bg-slate-900 transition-all">
                Salvar Alterações
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
            
            <form action="{{ route('admin.catalog.produtos.duplicate', ['produto' => $produto->id]) }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="w-full rounded-xl border border-blue-200 bg-blue-50/50 p-3 text-xs font-black text-blue-600 hover:bg-blue-100 transition flex items-center justify-center gap-2">
                    <i class="fas fa-copy"></i> Duplicar este Produto
                </button>
            </form>
        </aside>

        <!-- Formulário Principal -->
        <div class="lg:col-span-9">
            <form id="form-produto" action="{{ route('admin.catalog.produtos.update', $produto->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8 pb-20">
                @csrf
                @method('PATCH')
                
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
                            <input type="text" name="nome" value="{{ old('nome', $produto->nome) }}" required class="input-modern">
                        </div>
                        <div class="md:col-span-2">
                            <label class="label-form">Subtítulo Comercial</label>
                            <input type="text" name="subtitulo_comercial" value="{{ old('subtitulo_comercial', $produto->subtitulo_comercial) }}" placeholder="Ex: Acabamento Premium" class="input-modern">
                        </div>
                        <div>
                            <label class="label-form">Categoria <span class="text-red-500">*</span></label>
                            <select name="categoria_id" required class="select-modern">
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}" {{ $produto->categoria_id == $cat->id ? 'selected' : '' }}>{{ $cat->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label-form">Visibilidade na Loja <span class="text-red-500">*</span></label>
                            <select name="visibilidade" required class="select-modern">
                                <option value="ambos" {{ $produto->visibilidade == 'ambos' ? 'selected' : '' }}>Híbrido (Interno e Catálogo)</option>
                                <option value="interno" {{ $produto->visibilidade == 'interno' ? 'selected' : '' }}>Somente Interno (Painel/PDV)</option>
                                <option value="publico" {{ $produto->visibilidade == 'publico' ? 'selected' : '' }}>Somente Catálogo Público</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- 2. MODO DE CADASTRO -->
                <section id="secao-modo" class="card-secao border-2 border-brand-primary">
                    <div class="card-header bg-brand-primary p-5 text-white">
                        <h2 class="text-lg font-black flex items-center gap-2">
                            <i class="fas fa-layer-group"></i> Modo de Cadastro e Segmento
                        </h2>
                    </div>
                    <div class="card-body p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="label-form">Tipo de Modelagem <span class="text-red-500">*</span></label>
                            <select name="modelo_cadastro" id="modelo_cadastro" class="select-modern !border-brand-primary">
                                <option value="simples" {{ old('modelo_cadastro', $produto->modelo_cadastro) == 'simples' ? 'selected' : '' }}>🟢 Nível 1 - Produto Simples</option>
                                <option value="configuravel" {{ old('modelo_cadastro', $produto->modelo_cadastro) == 'configuravel' ? 'selected' : '' }} @if(!$canAdvanced) disabled @endif>🔵 Nível 2 - Configurável @if(!$canAdvanced) 🔒 PRO @endif</option>
                                <option value="tecnico" {{ old('modelo_cadastro', $produto->modelo_cadastro) == 'tecnico' ? 'selected' : '' }} @if(!$canTechnical) disabled @endif>🟣 Nível 3 - Técnico @if(!$canTechnical) 🔒 PREMIUM @endif</option>
                            </select>
                            <p class="mt-2 text-[10px] font-bold text-slate-400 uppercase leading-none" id="modelo-info-text">...</p>
                        </div>
                        <div>
                            <label class="label-form">Segmento Recomendado</label>
                            <select name="segmento" class="select-modern">
                                <option value="grafica_rapida" {{ $produto->segmento == 'grafica_rapida' ? 'selected' : '' }}>Gráfica Rápida</option>
                                <option value="comunicacao_visual" {{ $produto->segmento == 'comunicacao_visual' ? 'selected' : '' }}>Comunicação Visual</option>
                                <option value="grafica_industrial" {{ $produto->segmento == 'grafica_industrial' ? 'selected' : '' }}>Indústria Gráfica / Offset</option>
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
                                <input type="number" step="0.01" name="preco_base" value="{{ old('preco_base', $produto->preco_base) }}" class="input-modern font-black text-brand-primary text-lg">
                            </div>
                            <div>
                                <label class="label-form">Unidade de Venda</label>
                                <select name="unidade_venda" class="select-modern">
                                    <option value="unidade" {{ $produto->unidade_venda == 'unidade' ? 'selected' : '' }}>Por Unidade</option>
                                    <option value="cento" {{ $produto->unidade_venda == 'cento' ? 'selected' : '' }}>Por Cento</option>
                                    <option value="milheiro" {{ $produto->unidade_venda == 'milheiro' ? 'selected' : '' }}>Por Milheiro</option>
                                    <option value="m2" {{ $produto->unidade_venda == 'm2' ? 'selected' : '' }}>Por m²</option>
                                    <option value="metro_linear" {{ $produto->unidade_venda == 'metro_linear' ? 'selected' : '' }}>Por Metro Linear</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-form">Prazo de Produção</label>
                                <input type="text" name="prazo_estimado" value="{{ old('prazo_estimado', $produto->prazo_estimado) }}" class="input-modern">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="flex items-start gap-3">
                                <input type="hidden" name="exige_arte" value="0">
                                <input type="checkbox" name="exige_arte" id="exige_arte" value="1" class="mt-1 w-5 h-5 text-brand-primary border-slate-300 rounded focus:ring-brand-primary" {{ old('exige_arte', $produto->exige_arte) ? 'checked' : '' }}>
                                <label for="exige_arte" class="font-bold text-slate-800 cursor-pointer">Requer arte do cliente?</label>
                            </div>
                            <div class="flex items-start gap-3">
                                <input type="hidden" name="oferece_design" value="0">
                                <input type="checkbox" name="oferece_design" id="oferece_design" value="1" class="mt-1 w-5 h-5 text-brand-primary border-slate-300 rounded focus:ring-brand-primary" {{ old('oferece_design', $produto->oferece_design) ? 'checked' : '' }}>
                                <label for="oferece_design" class="font-bold text-slate-800 cursor-pointer">Oferecer criação?</label>
                            </div>
                        </div>

                        <div id="row-arte" class="grid grid-cols-1 md:grid-cols-2 gap-5 {{ old('oferece_design', $produto->oferece_design) || old('exige_arte', $produto->exige_arte) ? '' : 'hidden' }}">
                            <div>
                                <label class="label-form">Preço Venda Arte (R$)</label>
                                <input type="number" step="0.01" name="preco_arte" value="{{ old('preco_arte', $produto->preco_arte) }}" class="input-modern">
                            </div>
                            <div>
                                <label class="label-form">Custo Interno Arte (R$)</label>
                                <input type="number" step="0.01" name="custo_design" value="{{ old('custo_design', $produto->custo_design) }}" class="input-modern">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 4. ESPECIFICAÇÕES TÉCNICAS -->
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
                                <input type="number" step="0.01" name="largura" id="largura" value="{{ old('largura', $produto->largura) }}" class="input-modern">
                            </div>
                            <div>
                                <label class="label-form">Altura (mm)</label>
                                <input type="number" step="0.01" name="altura" id="altura" value="{{ old('altura', $produto->altura) }}" class="input-modern">
                            </div>
                            <div>
                                <label class="label-form">Área Total (m²)</label>
                                <input type="text" name="area_m2" id="area_m2" readonly value="{{ $produto->area_m2 }}" class="input-modern bg-slate-50 font-bold">
                            </div>
                            <div>
                                <label class="label-form">Gramatura (g)</label>
                                <input type="number" name="gramatura" value="{{ old('gramatura', $produto->gramatura) }}" class="input-modern">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="label-form">Orientação</label>
                                <select name="orientacao" class="select-modern">
                                    <option value="vertical" {{ $produto->orientacao == 'vertical' ? 'selected' : '' }}>Vertical</option>
                                    <option value="horizontal" {{ $produto->orientacao == 'horizontal' ? 'selected' : '' }}>Horizontal</option>
                                    <option value="quadrado" {{ $produto->orientacao == 'quadrado' ? 'selected' : '' }}>Quadrado</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-form">Cores Impressão</label>
                                <input type="text" name="cor_impressao" value="{{ $produto->cor_impressao }}" class="input-modern">
                            </div>
                            <div>
                                <label class="label-form">Tipo Impressão</label>
                                <input type="text" name="tipo_impressao" value="{{ $produto->tipo_impressao }}" class="input-modern">
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
                            @foreach($produto->materiais as $index => $material)
                                <div class="flex gap-3 animate-fade-in" id="material-db-{{ $material->id }}">
                                    <input type="text" name="materiais[{{ $index }}][nome]" value="{{ $material->nome }}" class="flex-1 input-modern !py-2">
                                    <input type="number" step="0.01" name="materiais[{{ $index }}][preco_ajuste]" value="{{ $material->preco_ajuste }}" class="w-32 input-modern !py-2">
                                    <button type="button" onclick="this.parentElement.remove()" class="p-2 text-red-400"><i class="fas fa-trash"></i></button>
                                </div>
                            @endforeach
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
                            @foreach($produto->acabamentos as $index => $acab)
                                <div class="flex gap-3 animate-fade-in">
                                    <input type="text" name="acabamentos[{{ $index }}][nome]" value="{{ $acab->nome }}" class="flex-1 input-modern !py-2">
                                    <input type="number" step="0.01" name="acabamentos[{{ $index }}][preco_ajuste]" value="{{ $acab->preco_ajuste }}" class="w-32 input-modern !py-2">
                                    <input type="number" name="acabamentos[{{ $index }}][prazo_ajuste]" value="{{ $acab->prazo_ajuste }}" class="w-24 input-modern !py-2">
                                    <button type="button" onclick="this.parentElement.remove()" class="p-2 text-red-400"><i class="fas fa-trash"></i></button>
                                </div>
                            @endforeach
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
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Grupos de Customização</h4>
                            <div id="container-grupos-variacao" class="space-y-6">
                                <!-- Grupos Injetáveis via Script (Carregados do DB) -->
                            </div>
                        </div>

                        <div class="secao-tecnica-only hidden pt-6 border-t">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Preços por Faixa de Quantidade</h4>
                            <div id="container-faixas-quantidade" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($produto->faixasQuantidade as $index => $faixa)
                                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 grid grid-cols-2 gap-3 relative">
                                        <div class="col-span-2 flex justify-between items-center mb-1">
                                            <span class="font-black text-slate-400 uppercase text-[10px]">Faixa Existente</span>
                                            <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-400"><i class="fas fa-times"></i></button>
                                        </div>
                                        <div>
                                            <label class="label-form text-[9px]">Qtd Mínima</label>
                                            <input type="number" name="faixas[{{ $index }}][quantidade_minima]" value="{{ $faixa->quantidade_minima }}" class="input-modern !py-1 text-center">
                                        </div>
                                        <div>
                                            <label class="label-form text-[9px]">Unitário (R$)</label>
                                            <input type="number" step="0.0001" name="faixas[{{ $index }}][preco_unitario]" value="{{ $faixa->preco_unitario }}" class="input-modern !py-1 text-center font-bold text-emerald-600">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 8. PRODUÇÃO -->
                <section id="secao-producao" class="card-secao">
                    <div class="card-header bg-slate-50 border-b p-5 text-slate-800">
                        <h2 class="text-lg font-black flex items-center gap-2"><i class="fas fa-industry text-brand-primary"></i> Produção</h2>
                    </div>
                    <div class="card-body p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="label-form">Checklist Interno</label>
                            <textarea name="checklist_producao" rows="2" class="input-modern">{{ $produto->checklist_producao }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="label-form">Instruções de Produção</label>
                            <textarea name="instrucoes_internas" rows="3" class="input-modern">{{ $produto->instrucoes_internas }}</textarea>
                        </div>
                    </div>
                </section>

                <!-- 9. MARKETING E SEO -->
                <section id="secao-marketing" class="card-secao">
                    <div class="card-header bg-slate-50 border-b p-5">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-bullhorn text-brand-primary"></i> Marketing e SEO
                        </h2>
                    </div>
                    <div class="card-body p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="label-form">Badge Comercial</label>
                                <input type="text" name="badge_comercial" value="{{ $produto->badge_comercial }}" class="input-modern">
                            </div>
                            <div class="md:col-span-2">
                                <label class="label-form">Frase de Efeito</label>
                                <input type="text" name="frase_efeito" value="{{ $produto->frase_efeito }}" class="input-modern">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t">
                            <div>
                                <label class="label-form">Destaque na Home</label>
                                <select name="destaque" class="select-modern">
                                    <option value="0" {{ !$produto->destaque ? 'selected' : '' }}>Não destacar</option>
                                    <option value="1" {{ $produto->destaque ? 'selected' : '' }}>Sim, destacar</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-form">Ordem Exibição</label>
                                <input type="number" name="ordem_exibicao" value="{{ $produto->ordem_exibicao }}" class="input-modern">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 10. GALERIA VISUAL -->
                <section id="secao-galeria" class="card-secao">
                    <div class="card-header bg-slate-50 border-b p-5">
                        <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            <i class="fas fa-images text-brand-primary"></i> Galeria
                        </h2>
                    </div>
                    <div class="card-body p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="label-form">Banner Vitrine (Atual)</label>
                            <div class="mb-4 aspect-video rounded-2xl border bg-slate-100 overflow-hidden relative group">
                                @if($produto->imagem_principal)
                                    <img src="{{ asset('storage/' . $produto->imagem_principal) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="flex items-center justify-center h-full text-slate-300">Sem Foto</div>
                                @endif
                            </div>
                            <label class="upload-box relative overflow-hidden h-24 border-brand-primary/20 bg-brand-primary/5 hover:bg-brand-primary/10">
                                <span class="text-xs font-black text-brand-primary">Trocar Imagem Utama</span>
                                <input type="file" name="imagem_destaque" accept="image/*" class="hidden">
                            </label>
                        </div>
                        <div>
                            <label class="label-form">Adicionar à Galeria</label>
                            <div class="grid grid-cols-4 gap-2 mb-4">
                                @foreach($produto->imagens as $image)
                                    <img src="{{ asset('storage/' . $image->caminho) }}" class="h-16 w-full object-cover rounded-lg border shadow-sm">
                                @endforeach
                            </div>
                            <label class="upload-box relative overflow-hidden h-24 border-slate-200 bg-slate-50">
                                <span class="text-xs font-black text-slate-400">+ Add Fotos</span>
                                <input type="file" name="imagens_adicionais[]" accept="image/*" multiple class="hidden">
                            </label>
                        </div>
                        <div class="md:col-span-2">
                            <label class="label-form">Descrição Completa / Comercial</label>
                            <textarea name="descricao_completa" rows="6" class="input-modern">{{ $produto->descricao_completa }}</textarea>
                        </div>
                    </div>
                </section>

                <div class="p-6 bg-slate-900 rounded-3xl text-white flex justify-between items-center shadow-2xl">
                    <div>
                        <h4 class="font-black text-lg">Pronto para salvar?</h4>
                        <p class="text-xs text-white/60 font-medium">As alterações serão aplicadas em todos os canais de venda.</p>
                    </div>
                    <button type="submit" class="bg-brand-primary hover:bg-orange-500 text-white font-black px-8 py-3 rounded-xl transition-all shadow-lg shadow-brand-primary/20">Gravar Produto ✅</button>
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
        
        .nav-item.hidden-simple { display: none; }
        .nav-item.hidden-simple.show { @apply block; }
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
                if(!dz) return;
                dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('dragover'); });
                dz.addEventListener('dragleave', e => { e.preventDefault(); dz.classList.remove('dragover'); });
                dz.addEventListener('drop', e => { e.preventDefault(); dz.classList.remove('dragover'); });
            });

            // Preview Vitrine
            if(inputVitrine && dropzoneVitrine) {
                inputVitrine.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            dropzoneVitrine.innerHTML = `
                                <img src="${e.target.result}" class="absolute inset-0 w-full h-full object-cover z-0 rounded-2xl">
                                <div class="absolute inset-0 bg-black/40 flex-col items-center justify-center opacity-0 hover:opacity-100 transition-opacity z-10 flex rounded-2xl">
                                    <span class="text-white text-xs font-bold uppercase tracking-widest bg-black/60 px-3 py-1 rounded shadow-lg"><i class="fas fa-sync-alt mr-1"></i> Trocar Imagem</span>
                                </div>
                            `;
                            dropzoneVitrine.appendChild(inputVitrine); // Recoloque o input
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Preview Galeria Múltipla
            if(inputGaleria && dropzoneGaleria) {
                inputGaleria.addEventListener('change', function(e) {
                    const files = e.target.files;
                    if(files.length > 0) {
                        let htmlPreview = '<div class="absolute inset-x-2 inset-y-2 z-0 flex flex-wrap gap-2 overflow-y-auto content-start justify-center p-2 rounded-2xl">';
                        
                        Array.from(files).slice(0, 12).forEach(file => {
                            htmlPreview += `<div class="h-16 w-16 bg-white rounded-lg border-2 border-white shadow-md relative overflow-hidden animate-fade-in"><img src="${URL.createObjectURL(file)}" class="w-full h-full object-cover"></div>`;
                        });
                        
                        htmlPreview += '</div><div class="absolute inset-0 bg-black/70 flex-col items-center justify-center opacity-0 hover:opacity-100 transition-opacity z-10 flex text-white text-xs font-bold uppercase tracking-widest rounded-2xl"><i class="fas fa-plus mb-1 text-lg"></i> Substituir Seleção</div>';
                        
                        dropzoneGaleria.innerHTML = htmlPreview;
                        dropzoneGaleria.appendChild(inputGaleria);
                    }
                });
            }

            const modeloSelect = document.getElementById('modelo_cadastro');
            const exigeArteCheck = document.getElementById('exige_arte');
            const ofereceDesignCheck = document.getElementById('oferece_design');
            const rowArte = document.getElementById('row-arte');
            
            const syncArte = () => rowArte.classList.toggle('hidden', !exigeArteCheck.checked && !ofereceDesignCheck.checked);
            exigeArteCheck.addEventListener('change', syncArte);
            ofereceDesignCheck.addEventListener('change', syncArte);

            const syncModelo = () => {
                const modo = modeloSelect.value;
                const infoText = document.getElementById('modelo-info-text');
                const secoesAvancadas = document.querySelectorAll('.secao-avancada');
                const secoesTecnicas = document.querySelectorAll('.secao-tecnica-only');
                const navItemsAvancados = document.querySelectorAll('.nav-item.hidden-simple');

                if (modo === 'simples') {
                    infoText.innerText = "Modo Gráfica Rápida: Preços estáticos e cadastro simplificado.";
                    secoesAvancadas.forEach(s => s.classList.add('hidden'));
                    navItemsAvancados.forEach(i => i.classList.remove('show'));
                } else {
                    infoText.innerText = modo === 'configuravel' ? "Modo Comunicação Visual: M2, Materiais e Variações." : "Modo Industrial: Máxima precisão técnica e faixas de preço.";
                    secoesAvancadas.forEach(s => s.classList.remove('hidden'));
                    secoesTecnicas.forEach(s => modo === 'tecnico' ? s.classList.remove('hidden') : s.classList.add('hidden'));
                    navItemsAvancados.forEach(i => i.classList.add('show'));
                }
            };
            modeloSelect.addEventListener('change', syncModelo);
            syncModelo();

            // Load Existing Variation Groups
            const dbGroups = @json($produto->gruposVariacao->load('opcoes'));
            dbGroups.forEach(g => adicionarGrupoVariacao(g));
        });

        let materialIdx = {{ $produto->materiais->count() }};
        let acabamentoIdx = {{ $produto->acabamentos->count() }};
        let faixaIdx = {{ $produto->faixasQuantidade->count() }};
        let grupoIdx = 0;
        let optionIdx = 0;

        function adicionarLinha(idContainer, nome) {
            const container = document.getElementById(idContainer);
            const div = document.createElement('div');
            div.className = "flex gap-3 animate-fade-in";
            let idx = (nome === 'materiais') ? materialIdx++ : acabamentoIdx++;
            div.innerHTML = `
                <input type="text" name="${nome}[${idx}][nome]" placeholder="Nome do ${nome}" class="flex-1 input-modern !py-2">
                <input type="number" step="0.01" name="${nome}[${idx}][preco_ajuste]" placeholder="R$" class="w-32 input-modern !py-2">
                ${nome === 'acabamentos' ? `<input type="number" name="${nome}[${idx}][prazo_ajuste]" placeholder="Dias" class="w-24 input-modern !py-2">` : ''}
                <button type="button" onclick="this.parentElement.remove()" class="p-2 text-red-400"><i class="fas fa-trash"></i></button>
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
                    <span class="font-black text-slate-400 text-[10px]">NOVA FAIXA</span>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-400"><i class="fas fa-times"></i></button>
                </div>
                <div><label class="label-form text-[9px]">Mínima</label><input type="number" name="faixas[${idx}][quantidade_minima]" class="input-modern !py-1 text-center"></div>
                <div><label class="label-form text-[9px]">R$ Unitário</label><input type="number" step="0.0001" name="faixas[${idx}][preco_unitario]" class="input-modern !py-1 text-center font-bold text-emerald-600"></div>
            `;
            container.appendChild(div);
        }

        function adicionarGrupoVariacao(data = null) {
            const container = document.getElementById('container-grupos-variacao');
            const div = document.createElement('div');
            div.className = "p-5 bg-white border border-slate-200 rounded-2xl shadow-sm relative group animate-fade-in";
            let idxGroup = grupoIdx++;

            div.innerHTML = `
                <button type="button" onclick="this.parentElement.remove()" class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition text-red-300 hover:text-red-500"><i class="fas fa-trash"></i></button>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="label-form">Nome do Grupo</label><input type="text" name="grupos_variacao[${idxGroup}][nome_grupo]" value="${data ? data.nome_grupo : ''}" class="input-modern !py-2"></div>
                    <div><label class="label-form">Tipo Seleção</label><select name="grupos_variacao[${idxGroup}][tipo_exibicao]" class="select-modern !py-2">
                        <option value="select" ${data?.tipo_exibicao === 'select' ? 'selected' : ''}>Dropdown</option>
                        <option value="radio" ${data?.tipo_exibicao === 'radio' ? 'selected' : ''}>Botões</option>
                        <option value="color" ${data?.tipo_exibicao === 'color' ? 'selected' : ''}>Cores</option>
                    </select></div>
                </div>
                <div class="space-y-2 mt-4 pl-4 border-l-2 border-slate-100">
                    <div id="opcoes-grupo-${idxGroup}" class="space-y-2"></div>
                    <button type="button" onclick="adicionarOpcaoVariacao(${idxGroup})" class="text-[10px] font-black text-blue-500 uppercase mt-2">+ Add Opção</button>
                </div>
            `;
            container.appendChild(div);
            if(data && data.opcoes) {
                data.opcoes.forEach(o => adicionarOpcaoVariacao(idxGroup, o));
            } else {
                adicionarOpcaoVariacao(idxGroup);
            }
        }

        function adicionarOpcaoVariacao(idxGroup, data = null) {
            const container = document.getElementById(`opcoes-grupo-${idxGroup}`);
            const div = document.createElement('div');
            div.className = "flex items-center gap-2";
            let idxOpt = optionIdx++;
            div.innerHTML = `
                <input type="text" name="grupos_variacao[${idxGroup}][opcoes][${idxOpt}][nome_opcao]" value="${data ? data.nome_opcao : ''}" class="flex-1 input-modern !py-1 text-xs">
                <input type="number" step="0.01" name="grupos_variacao[${idxGroup}][opcoes][${idxOpt}][acrescimo_preco]" value="${data ? data.acrescimo_preco : '0.00'}" class="w-20 input-modern !py-1 text-xs text-center text-emerald-600 font-bold">
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-300 hover:text-red-400"><i class="fas fa-times"></i></button>
            `;
            container.appendChild(div);
        }
    </script>
    @endpush
</x-layouts.app>
