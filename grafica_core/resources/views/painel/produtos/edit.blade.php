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
                @if($config_precificacao?->precificacao_dinamica_ativa)
                <a href="#secao-precificacao" class="nav-item border-brand-primary text-brand-primary font-black"><i class="fas fa-calculator mr-2"></i> Custos e Preço</a>
                @endif
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
                    <div class="card-body p-6 space-y-6">
                        
                        <!-- Etapas de Produção -->
                        @if(isset($fasesProducao) && $fasesProducao->isNotEmpty())
                        @php
                            $etapasVinculadas = $produto->etapasProducao->keyBy('production_step_id');
                        @endphp
                        <div>
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i class="fas fa-tasks"></i> Etapas de Produção do Produto
                            </h4>
                            <p class="text-xs text-slate-500 mb-4">Selecione as etapas que se aplicam a este produto. Se nenhuma for selecionada, serão usadas as etapas padrão da loja.</p>
                            
                            <div id="container-etapas-producao" class="space-y-3">
                                @foreach($fasesProducao as $fase)
                                        @if($fase->steps->isNotEmpty())
                                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                                <div class="mb-3 border-b border-slate-100 pb-2">
                                                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-500">{{ $fase->nome }}</p>
                                                </div>

                                                <div class="space-y-2 border-l-2 border-slate-100 pl-4">
                                                    @foreach($fase->steps as $etapa)
                                                        @php
                                                            $vinculo = $etapasVinculadas->get($etapa->id);
                                                            $isChecked = $vinculo !== null;
                                                        @endphp
                                                        <div class="flex items-center gap-4 p-4 rounded-xl border transition-all etapa-row {{ $isChecked ? 'border-brand-primary/50 bg-brand-primary/5' : 'bg-slate-50 border-slate-200' }}" data-step-id="{{ $etapa->id }}">
                                                            <input type="checkbox" 
                                                                   class="etapa-checkbox w-5 h-5 text-brand-primary border-slate-300 rounded focus:ring-brand-primary"
                                                                   data-step-id="{{ $etapa->id }}"
                                                                   onchange="toggleEtapaFields(this)"
                                                                   {{ $isChecked ? 'checked' : '' }}>
                                                            <div class="flex-1">
                                                                <span class="font-black text-slate-700">{{ $etapa->nome }}</span>
                                                            </div>
                                                            <div class="flex items-center gap-3 etapa-fields {{ $isChecked ? '' : 'hidden' }}">
                                                                <div class="flex items-center gap-1">
                                                                    <label class="text-[10px] font-bold text-slate-400 uppercase">Ordem</label>
                                                                    <input type="number" 
                                                                           name="etapas_producao[{{ $etapa->id }}][ordem]" 
                                                                           class="w-16 input-modern !py-1 text-center text-xs"
                                                                           value="{{ $vinculo?->ordem ?? $etapa->ordem }}"
                                                                           min="0"
                                                                           {{ $isChecked ? '' : 'disabled' }}>
                                                                    <input type="hidden" name="etapas_producao[{{ $etapa->id }}][production_step_id]" value="{{ $etapa->id }}" {{ $isChecked ? '' : 'disabled' }}>
                                                                </div>
                                                                <div class="flex items-center gap-1">
                                                                    <label class="text-[10px] font-bold text-slate-400 uppercase">Tempo (min)</label>
                                                                    <input type="number" 
                                                                           name="etapas_producao[{{ $etapa->id }}][tempo_estimado_minutos]" 
                                                                           class="w-20 input-modern !py-1 text-center text-xs"
                                                                           value="{{ $vinculo?->tempo_estimado_minutos }}"
                                                                           placeholder="—"
                                                                           min="0"
                                                                           {{ $isChecked ? '' : 'disabled' }}>
                                                                </div>
                                                                <div class="flex items-center gap-2">
                                                                    <input type="checkbox" 
                                                                           name="etapas_producao[{{ $etapa->id }}][obrigatorio]" 
                                                                           value="1"
                                                                           class="w-4 h-4 text-brand-primary border-slate-300 rounded"
                                                                           {{ ($vinculo?->obrigatorio ?? true) ? 'checked' : '' }}
                                                                           {{ $isChecked ? '' : 'disabled' }}>
                                                                    <label class="text-[10px] font-bold text-slate-400 uppercase">Obrig.</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                            </div>
                        </div>
                        <hr class="border-slate-100">
                        @endif
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="label-form">Checklist Interno</label>
                                <textarea name="checklist_producao" rows="2" class="input-modern">{{ $produto->checklist_producao }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="label-form">Instruções de Produção</label>
                                <textarea name="instrucoes_internas" rows="3" class="input-modern">{{ $produto->instrucoes_internas }}</textarea>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 8.5 PRECIFICACAO DINAMICA (Ficha Técnica) -->
                @if($config_precificacao?->precificacao_dinamica_ativa)
                <section id="secao-precificacao" class="card-secao border-2 border-brand-primary/50 relative overflow-visible">
                    <div class="card-header bg-slate-50 border-b p-5 flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                                <i class="fas fa-calculator text-brand-primary"></i> Ficha Técnica e Preço Inteligente
                            </h2>
                            <p class="text-xs text-slate-500 font-bold mt-1">Cálculo dinâmico baseado no custo de materiais e taxas globais da loja.</p>
                        </div>
                        <span class="px-3 py-1 bg-brand-primary/10 text-brand-primary text-xs font-black rounded uppercase">Ativo</span>
                    </div>
                    <div class="card-body p-0 grid grid-cols-1 xl:grid-cols-12 gap-0 relative">
                        
                        <!-- Coluna da Esquerda (Formulário de Insumos/Serviços) -->
                        <div class="xl:col-span-8 p-6 space-y-8 border-r border-slate-100">
                            
                            <!-- Parametros da Ficha -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                                <div>
                                    <label class="label-form">Tempo Padrão (Min)</label>
                                    <input type="number" name="ficha_tecnica[tempo_producao_min]" id="ficha_tempo_producao_min" value="{{ $produto->fichaTecnica?->tempo_producao_min ?? 0 }}" class="input-modern trigger-simulacao" min="0">
                                </div>
                                <div>
                                    <label class="label-form">Qtd. da Receita Base</label>
                                    <input type="number" name="ficha_tecnica[quantidade_base]" id="ficha_quantidade_base" value="{{ $produto->fichaTecnica?->quantidade_base ?? 1 }}" class="input-modern trigger-simulacao" min="1">
                                </div>
                                <div>
                                    <label class="label-form">Perda Natural/Quebra (%)</label>
                                    <input type="number" step="0.01" name="ficha_tecnica[perda_percentual]" id="ficha_perda_percentual" value="{{ $produto->fichaTecnica?->perda_percentual ?? 0 }}" class="input-modern trigger-simulacao" min="0" max="100">
                                </div>
                            </div>

                            <!-- Insumos (Materiais) -->
                            <div>
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest"><i class="fas fa-box-open mr-1"></i> Materiais Consumidos</h4>
                                    <button type="button" onclick="adicionarInsumoFicha()" class="text-[10px] font-black px-3 py-1.5 bg-blue-600 text-white rounded-lg shadow-sm">+ Add Material</button>
                                </div>
                                <div id="container-ficha-insumos" class="space-y-3">
                                    @php $fichaInsumos = $produto->fichaTecnica?->insumos ?? collect(); @endphp
                                    @foreach($fichaInsumos as $index => $item)
                                    <div class="flex gap-2 items-center bg-white border border-slate-200 p-2 rounded-xl animate-fade-in row-insumo">
                                        <div class="flex-1">
                                            <select name="ficha_tecnica[insumos][{{ $index }}][insumo_id]" class="select-modern !py-2 !text-xs trigger-simulacao insumo-id-val">
                                                <option value="">Selecione um Insumo...</option>
                                                @foreach($insumos_loja as $insumo)
                                                    <option value="{{ $insumo->id }}" data-custo="{{ $insumo->custo_medio }}" {{ $item->insumo_id == $insumo->id ? 'selected' : '' }}>{{ $insumo->nome }} ({{ $insumo->unidade_medida }}) - R$ {{ number_format($insumo->custo_medio, 2, ',', '.') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-24 relative">
                                            <label class="text-[9px] font-black text-slate-400 uppercase absolute -top-4 left-1">Qtd</label>
                                            <input type="number" step="0.0001" name="ficha_tecnica[insumos][{{ $index }}][quantidade]" value="{{ $item->quantidade }}" class="input-modern !py-2 text-center text-xs trigger-simulacao insumo-qtd-val">
                                        </div>
                                        <div class="w-24 relative">
                                            <label class="text-[9px] font-black text-slate-400 uppercase absolute -top-4 left-1">Perda %</label>
                                            <input type="number" step="0.01" name="ficha_tecnica[insumos][{{ $index }}][fator_perda]" value="{{ $item->fator_perda }}" class="input-modern !py-2 text-center text-xs trigger-simulacao insumo-perda-val">
                                        </div>
                                        <button type="button" onclick="removerLinhaSimulacao(this)" class="p-2 text-slate-300 hover:text-red-500 transition-colors"><i class="fas fa-trash"></i></button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Serviços/Terceirização -->
                            <div>
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest"><i class="fas fa-cut mr-1"></i> Serviços e Processos</h4>
                                    <button type="button" onclick="adicionarServicoFicha()" class="text-[10px] font-black px-3 py-1.5 bg-emerald-600 text-white rounded-lg shadow-sm">+ Add Serviço</button>
                                </div>
                                <div id="container-ficha-servicos" class="space-y-3">
                                    @php $fichaServicos = $produto->fichaTecnica?->servicos ?? collect(); @endphp
                                    @foreach($fichaServicos as $index => $item)
                                    <div class="flex gap-2 items-center bg-white border border-slate-200 p-2 rounded-xl animate-fade-in row-servico">
                                        <div class="flex-1">
                                            <select name="ficha_tecnica[servicos][{{ $index }}][servico_producao_id]" class="select-modern !py-2 !text-xs trigger-simulacao servico-id-val">
                                                <option value="">Selecione um Serviço...</option>
                                                @foreach($servicos_loja as $servico)
                                                    <option value="{{ $servico->id }}" {{ $item->servico_producao_id == $servico->id ? 'selected' : '' }}>{{ $servico->nome }} ({{ $servico->tipo_cobranca }}) - R$ {{ number_format($servico->custo_base, 2, ',', '.') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-24 relative">
                                            <label class="text-[9px] font-black text-slate-400 uppercase absolute -top-4 left-1">Qtd/Tempo</label>
                                            <input type="number" step="0.0001" name="ficha_tecnica[servicos][{{ $index }}][quantidade]" value="{{ $item->quantidade }}" class="input-modern !py-2 text-center text-xs trigger-simulacao servico-qtd-val">
                                        </div>
                                        <button type="button" onclick="removerLinhaSimulacao(this)" class="p-2 text-slate-300 hover:text-red-500 transition-colors"><i class="fas fa-trash"></i></button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div>
                                <label class="label-form flex justify-between items-center">
                                    <span>Margem de Lucro Desejada (%) <span class="text-brand-primary">*</span></span>
                                    <span class="text-xs text-slate-400 font-medium">No carrinho, qual margem aplicar por padrão?</span>
                                </label>
                                <input type="number" step="0.01" name="margem_lucro" id="ficha_margem_lucro" value="{{ old('margem_lucro', $produto->margem_lucro > 0 ? $produto->margem_lucro : 20.0) }}" class="input-modern font-black text-brand-primary text-xl trigger-simulacao">
                            </div>

                        </div>

                        <!-- Coluna da Direita (Painel Fixo de Resumo de Custos) -->
                        <div class="xl:col-span-4 bg-slate-900 text-white p-6 relative">
                            <div class="sticky top-32 space-y-6">
                                <div>
                                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-1">Custo Projetado</h3>
                                    <p class="text-[11px] text-slate-500 font-bold mb-4">Simulação em tempo real, sem salvar.</p>
                                    
                                    <div class="space-y-3 font-medium text-sm">
                                        <div class="flex justify-between items-center border-b border-white/10 pb-2">
                                            <span class="text-slate-300"><i class="fas fa-box-open mr-2 text-blue-400"></i> Materiais</span>
                                            <span class="font-bold" id="sim-custo-materiais">R$ 0,00</span>
                                        </div>
                                        <div class="flex justify-between items-center border-b border-white/10 pb-2">
                                            <span class="text-slate-300"><i class="fas fa-cut mr-2 text-emerald-400"></i> Serviços</span>
                                            <span class="font-bold" id="sim-custo-servicos">R$ 0,00</span>
                                        </div>
                                        <div class="flex justify-between items-center border-b border-white/10 pb-2">
                                            <span class="text-slate-300" title="Proporcional ao tempo produtivo"><i class="fas fa-industry mr-2 text-purple-400"></i> C. Operacional Fixo</span>
                                            <span class="font-bold" id="sim-custo-indireto">R$ 0,00</span>
                                        </div>
                                        <div class="flex justify-between items-center border-b border-white/10 pb-2 text-orange-200">
                                            <span><i class="fas fa-percent mr-2"></i> Encargos da Loja</span>
                                            <span class="font-bold" id="sim-encargos-perc">0%</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-black/40 rounded-2xl p-4 border border-white/10">
                                    <p class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-1">Preço Mínimo / Empate</p>
                                    <p class="text-2xl font-black text-red-400" id="sim-preco-equilibrio">R$ 0,00</p>
                                    <p class="text-[10px] text-red-300/60 font-bold leading-tight mt-1">Abaixo disso você tem prejuízo absoluto no caixa.</p>
                                </div>

                                <div class="bg-brand-primary rounded-2xl p-4 border border-orange-400 shadow-xl shadow-brand-primary/20 relative overflow-hidden">
                                    <div class="absolute -right-4 -bottom-4 opacity-20"><i class="fas fa-coins text-8xl"></i></div>
                                    <p class="text-[10px] uppercase font-black tracking-widest text-orange-100 mb-1">Preço Sugerido (Venda)</p>
                                    <p class="text-3xl font-black text-white" id="sim-preco-sugerido">R$ 0,00</p>
                                    <p class="text-xs text-orange-100 font-bold leading-tight mt-1">Com lucro de <span id="sim-margem-lucro">0</span>%</p>
                                </div>

                                <div id="alerta-prejuizo" class="hidden bg-red-500/20 border border-red-500 p-3 rounded-xl text-red-200 text-xs font-bold flex items-start gap-2">
                                    <i class="fas fa-exclamation-triangle mt-0.5"></i>
                                    <p>O <strong>Preço Base Manual</strong> (R$ <span id="aviso-preco-manual">{{ number_format($produto->preco_base, 2, ',', '.') }}</span>) está menor que o Custo Mínimo. Você pode ter prejuízo se não atualizar!</p>
                                </div>

                                <button type="button" id="btn-recalcular-manual" class="w-full rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 py-3 text-sm font-black text-white transition">
                                    <i class="fas fa-sync-alt mr-2"></i> Forçar Simulação
                                </button>
                                <p class="text-center text-[10px] text-slate-500 font-bold">A simulação ocorre automaticamente ao digitar.</p>
                            </div>
                        </div>

                    </div>
                </section>
                @endif
                
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
        function toggleEtapaFields(checkbox) {
            const row = checkbox.closest('.etapa-row');
            const fields = row.querySelector('.etapa-fields');
            const inputs = fields.querySelectorAll('input');
            
            if (checkbox.checked) {
                fields.classList.remove('hidden');
                inputs.forEach(input => input.disabled = false);
                row.classList.add('border-brand-primary/50', 'bg-brand-primary/5');
                row.classList.remove('border-slate-200', 'bg-slate-50');
            } else {
                fields.classList.add('hidden');
                inputs.forEach(input => input.disabled = true);
                row.classList.remove('border-brand-primary/50', 'bg-brand-primary/5');
                row.classList.add('border-slate-200', 'bg-slate-50');
            }
        }

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

        // --- SISTEMA DE PRECIFICAÇÃO DINÂMICA (Ficha Técnica) ---
        @if($config_precificacao?->precificacao_dinamica_ativa)
        let fInsumoIdx = {{ $produto->fichaTecnica?->insumos->count() ?? 0 }};
        let fServicoIdx = {{ $produto->fichaTecnica?->servicos->count() ?? 0 }};

        function adicionarInsumoFicha() {
            const container = document.getElementById('container-ficha-insumos');
            const div = document.createElement('div');
            div.className = "flex gap-2 items-center bg-white border border-slate-200 p-2 rounded-xl animate-fade-in row-insumo";
            let idx = fInsumoIdx++;
            
            // Gerar options via JS (Clonando as do primeiro select ou carregando de var global)
            let options = '<option value="">Selecione um Insumo...</option>';
            @foreach($insumos_loja as $insumo)
                options += `<option value="{{ $insumo->id }}">{{ $insumo->nome }} ({{ $insumo->unidade_medida }})</option>`;
            @endforeach

            div.innerHTML = `
                <div class="flex-1">
                    <select name="ficha_tecnica[insumos][${idx}][insumo_id]" class="select-modern !py-2 !text-xs trigger-simulacao insumo-id-val">${options}</select>
                </div>
                <div class="w-24 relative"><input type="number" step="0.0001" name="ficha_tecnica[insumos][${idx}][quantidade]" value="1" class="input-modern !py-2 text-center text-xs trigger-simulacao insumo-qtd-val"></div>
                <div class="w-24 relative"><input type="number" step="0.01" name="ficha_tecnica[insumos][${idx}][fator_perda]" value="0" class="input-modern !py-2 text-center text-xs trigger-simulacao insumo-perda-val"></div>
                <button type="button" onclick="removerLinhaSimulacao(this)" class="p-2 text-slate-300 hover:text-red-500 transition-colors"><i class="fas fa-trash"></i></button>
            `;
            container.appendChild(div);
            atrelarEventosSimulacao();
        }

        function adicionarServicoFicha() {
            const container = document.getElementById('container-ficha-servicos');
            const div = document.createElement('div');
            div.className = "flex gap-2 items-center bg-white border border-slate-200 p-2 rounded-xl animate-fade-in row-servico";
            let idx = fServicoIdx++;
            
            let options = '<option value="">Selecione um Serviço...</option>';
            @foreach($servicos_loja as $servico)
                options += `<option value="{{ $servico->id }}">{{ $servico->nome }} ({{ $servico->tipo_cobranca }})</option>`;
            @endforeach

            div.innerHTML = `
                <div class="flex-1">
                    <select name="ficha_tecnica[servicos][${idx}][servico_producao_id]" class="select-modern !py-2 !text-xs trigger-simulacao servico-id-val">${options}</select>
                </div>
                <div class="w-24 relative"><input type="number" step="0.0001" name="ficha_tecnica[servicos][${idx}][quantidade]" value="1" class="input-modern !py-2 text-center text-xs trigger-simulacao servico-qtd-val"></div>
                <button type="button" onclick="removerLinhaSimulacao(this)" class="p-2 text-slate-300 hover:text-red-500 transition-colors"><i class="fas fa-trash"></i></button>
            `;
            container.appendChild(div);
            atrelarEventosSimulacao();
        }

        function removerLinhaSimulacao(btn) {
            btn.closest('div.animate-fade-in').remove();
            dispararSimulacaoTimeout();
        }

        function montarPayloadSimulacao() {
            const insumos = [];
            document.querySelectorAll('.row-insumo').forEach(row => {
                const id = row.querySelector('.insumo-id-val').value;
                if(id) {
                    insumos.push({
                        insumo_id: id,
                        quantidade: row.querySelector('.insumo-qtd-val').value || 0,
                        fator_perda: row.querySelector('.insumo-perda-val').value || 0
                    });
                }
            });

            const servicos = [];
            document.querySelectorAll('.row-servico').forEach(row => {
                const id = row.querySelector('.servico-id-val').value;
                if(id) {
                    servicos.push({
                        servico_producao_id: id,
                        quantidade: row.querySelector('.servico-qtd-val').value || 0,
                        fator_aplicacao: 1.0 // Padrão
                    });
                }
            });

            return {
                tempo_producao_min: document.getElementById('ficha_tempo_producao_min')?.value || 0,
                quantidade_base: document.getElementById('ficha_quantidade_base')?.value || 1,
                perda_percentual: document.getElementById('ficha_perda_percentual')?.value || 0,
                margem_lucro: document.getElementById('ficha_margem_lucro')?.value || 20,
                insumos: insumos,
                servicos: servicos
            };
        }

        let simulacaoTimer;
        function dispararSimulacaoTimeout() {
            clearTimeout(simulacaoTimer);
            simulacaoTimer = setTimeout(executarSimulacaoAPI, 600); // 600ms debounce
        }

        function formatBRL(valor) {
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);
        }

        async function executarSimulacaoAPI() {
            const btnForca = document.getElementById('btn-recalcular-manual');
            if(btnForca) { btnForca.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Calculando...'; btnForca.disabled = true; }
            
            const payload = montarPayloadSimulacao();
            
            try {
                const url = "{{ route('admin.catalog.produtos.pricing.simular', $produto->id) }}";
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const json = await response.json();
                
                if(json.success && json.data) {
                    const data = json.data;
                    document.getElementById('sim-custo-materiais').innerText = formatBRL(data.custo_insumos);
                    document.getElementById('sim-custo-servicos').innerText = formatBRL(data.custo_servicos);
                    document.getElementById('sim-custo-indireto').innerText = formatBRL(data.custo_indireto);
                    document.getElementById('sim-encargos-perc').innerText = data.encargos_percentual + '%';
                    
                    document.getElementById('sim-preco-equilibrio').innerText = formatBRL(data.preco_equilibrio);
                    document.getElementById('sim-preco-sugerido').innerText = formatBRL(data.preco_sugerido);
                    document.getElementById('sim-margem-lucro').innerText = data.margem_lucro_aplicada;

                    // Alerta de prejuízo com o preço manual (preco_base na página)
                    const precoManualAtualStr = document.querySelector('input[name="preco_base"]').value || 0;
                    const precoManualAtual = parseFloat(precoManualAtualStr);
                    const alertBox = document.getElementById('alerta-prejuizo');
                    
                    if(precoManualAtual > 0 && precoManualAtual < data.preco_equilibrio) {
                        alertBox.classList.remove('hidden');
                        document.getElementById('aviso-preco-manual').innerText = formatBRL(precoManualAtual).replace('R$', '');
                    } else {
                        alertBox.classList.add('hidden');
                    }
                }
            } catch (e) {
                console.error("Erro na simulação:", e);
            } finally {
                if(btnForca) { btnForca.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Forçar Simulação'; btnForca.disabled = false; }
            }
        }

        function atrelarEventosSimulacao() {
            document.querySelectorAll('.trigger-simulacao').forEach(el => {
                el.removeEventListener('input', dispararSimulacaoTimeout);
                el.removeEventListener('change', dispararSimulacaoTimeout);
                
                if(el.tagName === 'SELECT') {
                    el.addEventListener('change', dispararSimulacaoTimeout);
                } else {
                    el.addEventListener('input', dispararSimulacaoTimeout);
                }
            });
            const btnManual = document.getElementById('btn-recalcular-manual');
            if(btnManual) {
                btnManual.removeEventListener('click', executarSimulacaoAPI);
                btnManual.addEventListener('click', executarSimulacaoAPI);
            }
        }

        // Executar inicial
        document.addEventListener('DOMContentLoaded', () => {
            atrelarEventosSimulacao();
            const margemInput = document.getElementById('ficha_margem_lucro');
            if(margemInput) dispararSimulacaoTimeout(); // Simula ao carregar a página para popular os cards se a aba existir
        });
        @endif
    </script>
    @endpush
</x-layouts.app>
