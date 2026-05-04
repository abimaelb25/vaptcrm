@php
/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 10:25
| Descrição: Formulário de Criação/Edição de Ocorrências RH
*/
@endphp
<x-layouts.app titulo="{{ $ocorrencia->exists ? 'Editar Ocorrência' : 'Nova Ocorrência' }} - {{ $funcionario->nome_completo }}">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.system.equipe.ocorrencias.index', $funcionario->id) }}" class="text-xs font-black text-slate-400 hover:text-brand-primary mb-2 inline-flex items-center gap-2 uppercase tracking-tighter transition-colors">
                <i class="fas fa-arrow-left"></i> Voltar para Ocorrências
            </a>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">
                {{ $ocorrencia->exists ? 'Editar Ocorrência' : 'Nova Ocorrência RH' }}
            </h1>
            <p class="text-slate-500 font-medium">{{ $funcionario->nome_completo }} • {{ $funcionario->cargo_interno ?: $funcionario->cargo_formal ?: '-' }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl animate-in shake duration-500">
            <ul class="list-disc list-inside text-sm text-red-600 font-bold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $ocorrencia->exists ? route('admin.system.equipe.ocorrencias.update', [$funcionario->id, $ocorrencia->id]) : route('admin.system.equipe.ocorrencias.store', $funcionario->id) }}" 
          method="POST"
          x-data="formOcorrencia({{ old('tipo', $ocorrencia->tipo ? json_encode($ocorrencia->tipo) : 'null') }})"
          @submit="handleSubmit">
        @csrf
        @if($ocorrencia->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- SIDEBAR -->
            <aside class="lg:col-span-1">
                <div class="sticky top-6 bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-tight mb-6 border-b border-slate-100 pb-4">Informações</h2>
                    
                    <div class="space-y-4 text-sm">
                        <div>
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Colaborador</span>
                            <span class="block font-bold text-slate-800">{{ $funcionario->nome_completo }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Funcional</span>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-black text-slate-700 bg-slate-100">
                                {{ $funcionario->status_funcional }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Admissão</span>
                            <span class="block font-bold text-slate-800">
                                {{ $funcionario->data_admissao?->format('d/m/Y') ?? 'N/A' }}
                            </span>
                        </div>
                        @if($ocorrencia->exists)
                            <div class="pt-4 mt-4 border-t border-slate-100">
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">ID da Ocorrência</span>
                                <span class="block font-mono text-xs text-slate-600">#{{ $ocorrencia->id }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Criado em</span>
                                <span class="block text-xs text-slate-600">{{ $ocorrencia->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </aside>

            <!-- FORMULÁRIO PRINCIPAL -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Tipo de Ocorrência -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h2 class="text-lg font-black text-slate-800 mb-6 border-b border-slate-100 pb-4">Classificação</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-[11px] uppercase font-black text-slate-400 mb-3 block">Tipo de Ocorrência *</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach([
                                    'advertencia' => ['icon' => 'exclamation-triangle', 'label' => 'Advertência', 'color' => 'orange'],
                                    'suspensao' => ['icon' => 'ban', 'label' => 'Suspensão', 'color' => 'red'],
                                    'falta' => ['icon' => 'calendar-times', 'label' => 'Falta', 'color' => 'amber'],
                                    'atestado' => ['icon' => 'file-medical', 'label' => 'Atestado', 'color' => 'blue'],
                                    'desligamento' => ['icon' => 'sign-out-alt', 'label' => 'Desligamento', 'color' => 'slate'],
                                ] as $tipo => $config)
                                    <label class="relative">
                                        <input type="radio" name="tipo" value="{{ $tipo }}" 
                                               {{ old('tipo', $ocorrencia->tipo) === $tipo ? 'checked' : '' }}
                                               @change="tipoMudou('{{ $tipo }}')"
                                               required class="sr-only">
                                        <div class="p-3 rounded-xl border-2 transition-all cursor-pointer
                                                    {{ old('tipo', $ocorrencia->tipo) === $tipo 
                                                        ? 'border-brand-primary bg-brand-primary/5' 
                                                        : 'border-slate-200 bg-white hover:border-slate-300' }}">
                                            <div class="text-center">
                                                <div class="text-2xl mb-2">
                                                    <i class="fas fa-{{ $config['icon'] }}"></i>
                                                </div>
                                                <div class="text-xs font-bold text-slate-700">{{ $config['label'] }}</div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Subtipo (dinâmico conforme o tipo) -->
                        <div x-show="showSubtipo" class="pt-4 border-t border-slate-100">
                            <label class="text-[11px] uppercase font-black text-slate-400 mb-3 block">Especifique *</label>
                            <select name="subtipo" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm bg-slate-50/30 focus:bg-white transition-all appearance-none cursor-pointer">
                                <option value="">Selecione a especificação...</option>
                                
                                <optgroup label="Advertência" x-show="tipo === 'advertencia'">
                                    <option value="verbal" {{ old('subtipo', $ocorrencia->subtipo) === 'verbal' ? 'selected' : '' }}>Advertência Verbal</option>
                                    <option value="escrita" {{ old('subtipo', $ocorrencia->subtipo) === 'escrita' ? 'selected' : '' }}>Advertência Escrita</option>
                                </optgroup>

                                <optgroup label="Falta" x-show="tipo === 'falta'">
                                    <option value="injustificada" {{ old('subtipo', $ocorrencia->subtipo) === 'injustificada' ? 'selected' : '' }}>Falta Injustificada</option>
                                    <option value="justificada" {{ old('subtipo', $ocorrencia->subtipo) === 'justificada' ? 'selected' : '' }}>Falta Justificada</option>
                                </optgroup>

                                <optgroup label="Desligamento" x-show="tipo === 'desligamento'">
                                    <option value="pedido_demissao" {{ old('subtipo', $ocorrencia->subtipo) === 'pedido_demissao' ? 'selected' : '' }}>Pedido de Demissão</option>
                                    <option value="sem_justa_causa" {{ old('subtipo', $ocorrencia->subtipo) === 'sem_justa_causa' ? 'selected' : '' }}>Dispensa Sem Justa Causa</option>
                                    <option value="justa_causa" {{ old('subtipo', $ocorrencia->subtipo) === 'justa_causa' ? 'selected' : '' }}>Dispensa Por Justa Causa</option>
                                    <option value="termino_contrato" {{ old('subtipo', $ocorrencia->subtipo) === 'termino_contrato' ? 'selected' : '' }}>Término de Contrato</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Identificação & Descrição -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h2 class="text-lg font-black text-slate-800 mb-6 border-b border-slate-100 pb-4">Descrição</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Título / Resumo *</label>
                            <input type="text" name="titulo" 
                                   value="{{ old('titulo', $ocorrencia->titulo) }}" 
                                   required
                                   placeholder="Ex: Advertência verbal por atraso reiterado"
                                   class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm bg-slate-50/30 focus:bg-white transition-all">
                        </div>
                        <div>
                            <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Motivo / Contexto</label>
                            <textarea name="descricao" rows="4"
                                      placeholder="Descreva detalhadamente o motivo, contexto e circunstâncias da ocorrência..."
                                      class="w-full rounded-2xl border-slate-200 px-5 py-4 font-medium text-slate-700 shadow-sm bg-slate-50/30 focus:bg-white transition-all resize-none">{{ old('descricao', $ocorrencia->descricao) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Datas & Período -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h2 class="text-lg font-black text-slate-800 mb-6 border-b border-slate-100 pb-4">Data e Período</h2>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Data da Ocorrência *</label>
                                <input type="date" name="data_ocorrencia"
                                       value="{{ old('data_ocorrencia', $ocorrencia->data_ocorrencia?->format('Y-m-d')) }}"
                                       required
                                       max="{{ now()->format('Y-m-d') }}"
                                       class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                        </div>

                        <!-- Período (para suspensão) -->
                        <div x-show="tipo === 'suspensao'" class="pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Data de Início *</label>
                                <input type="date" name="data_inicio"
                                       value="{{ old('data_inicio', $ocorrencia->data_inicio?->format('Y-m-d')) }}"
                                       class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Data de Fim *</label>
                                <input type="date" name="data_fim"
                                       value="{{ old('data_fim', $ocorrencia->data_fim?->format('Y-m-d')) }}"
                                       class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                        </div>

                        <!-- Período (para atestado) -->
                        <div x-show="tipo === 'atestado'" class="pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Data de Início do Afastamento</label>
                                <input type="date" name="data_inicio"
                                       value="{{ old('data_inicio', $ocorrencia->data_inicio?->format('Y-m-d')) }}"
                                       class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Data de Término</label>
                                <input type="date" name="data_fim"
                                       value="{{ old('data_fim', $ocorrencia->data_fim?->format('Y-m-d')) }}"
                                       class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status & Referência -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                    <h2 class="text-lg font-black text-slate-800 mb-6 border-b border-slate-100 pb-4">Status & Documentação</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Status da Ocorrência</label>
                            <select name="status" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm bg-slate-50/30 focus:bg-white transition-all appearance-none cursor-pointer">
                                <option value="registrada" {{ old('status', $ocorrencia->status ?? 'registrada') === 'registrada' ? 'selected' : '' }}>Registrada</option>
                                <option value="em_analise" {{ old('status', $ocorrencia->status) === 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                                <option value="resolvida" {{ old('status', $ocorrencia->status) === 'resolvida' ? 'selected' : '' }}>Resolvida</option>
                                <option value="contestada" {{ old('status', $ocorrencia->status) === 'contestada' ? 'selected' : '' }}>Contestada</option>
                                <option value="arquivada" {{ old('status', $ocorrencia->status) === 'arquivada' ? 'selected' : '' }}>Arquivada</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Referência (Nº de Atestado, Protocolo, etc)</label>
                            <input type="text" name="referencia_documento"
                                   value="{{ old('referencia_documento', $ocorrencia->referencia_documento) }}"
                                   placeholder="Ex: AT-2026-001 ou PROTOCOLO-123456"
                                   class="w-full rounded-2xl border-slate-200 px-5 py-4 font-medium text-slate-700 shadow-sm bg-slate-50/30 focus:bg-white transition-all">
                        </div>
                    </div>
                </div>

                <!-- Governança de Desligamento -->
                <div x-show="tipo === 'desligamento'" class="bg-amber-50 border-2 border-amber-200 rounded-3xl shadow-sm p-8">
                    <h2 class="text-lg font-black text-amber-900 mb-6 border-b border-amber-200 pb-4">
                        <i class="fas fa-shield-alt mr-2"></i> Governança de Acesso
                    </h2>
                    
                    <div class="flex items-start gap-4 p-4 bg-white rounded-2xl border border-amber-200">
                        <input type="checkbox" id="revogar_acesso" name="revogar_acesso" value="1"
                               class="mt-1 w-5 h-5 rounded border-amber-300 cursor-pointer"
                               {{ old('revogar_acesso') ? 'checked' : '' }}>
                        <div class="flex-1">
                            <label for="revogar_acesso" class="font-bold text-amber-900 cursor-pointer">
                                Revogar acesso ao sistema
                            </label>
                            <p class="text-xs text-amber-700 mt-1">
                                Ao selecionar esta opção, o usuário relacionado será desativado e não poderá acessar o sistema. Histórico será preservado para auditoria.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex gap-3 pt-4">
                    <a href="{{ route('admin.system.equipe.ocorrencias.index', $funcionario->id) }}" 
                       class="btn rounded-xl px-6 py-3 font-bold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-all flex-1">
                        Cancelar
                    </a>
                    <button type="submit" class="btn rounded-xl px-6 py-3 font-bold bg-brand-primary text-white transition-all shadow-lg shadow-brand-primary/20 hover:shadow-xl flex-1">
                        <i class="fas fa-check mr-2"></i> {{ $ocorrencia->exists ? 'Atualizar Ocorrência' : 'Registrar Ocorrência' }}
                    </button>
                </div>

            </div>
        </div>
    </form>

    <script>
        function formOcorrencia(tipoInicial) {
            return {
                tipo: tipoInicial || null,
                showSubtipo: tipoInicial && ['advertencia', 'falta', 'desligamento'].includes(tipoInicial),
                
                tipoMudou(novoTipo) {
                    this.tipo = novoTipo;
                    this.showSubtipo = ['advertencia', 'falta', 'desligamento'].includes(novoTipo);
                },
                
                handleSubmit() {
                    // Validação adicional de cliente para suspensão
                    if (this.tipo === 'suspensao') {
                        const dataInicio = document.querySelector('input[name="data_inicio"]').value;
                        const dataFim = document.querySelector('input[name="data_fim"]').value;
                        
                        if (!dataInicio || !dataFim) {
                            alert('Para suspensão, informe data de início e fim.');
                            return false;
                        }
                    }
                    return true;
                }
            };
        }
    </script>
</x-layouts.app>
