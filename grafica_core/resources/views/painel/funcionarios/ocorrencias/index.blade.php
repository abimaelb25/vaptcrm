@php
/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 10:20
| Descrição: Lista de Ocorrências RH do colaborador (timeline estruturada)
*/
@endphp
<x-layouts.app titulo="Ocorrências RH - {{ $funcionario->nome_completo }}">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.system.equipe.show', $funcionario->id) }}" class="text-xs font-black text-slate-400 hover:text-brand-primary mb-2 inline-flex items-center gap-2 uppercase tracking-tighter transition-colors">
                <i class="fas fa-arrow-left"></i> Voltar para Ficha
            </a>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">Ocorrências RH</h1>
            <p class="text-slate-500 font-medium">Histórico estruturado e auditável de eventos funcionais.</p>
        </div>
        @if($permissoesOcorrencia['pode_criar'] ?? false)
            <a href="{{ route('admin.system.equipe.ocorrencias.create', $funcionario->id) }}" class="btn rounded-xl px-6 py-3 font-bold bg-brand-primary text-white transition-all flex items-center gap-2 shadow-lg shadow-brand-primary/20 hover:shadow-xl whitespace-nowrap">
                <i class="fas fa-plus"></i> Registrar Ocorrência
            </a>
        @endif
    </div>

    @if(session('sucesso'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl animate-in fade-in duration-300">
            <p class="text-emerald-700 font-bold flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('sucesso') }}
            </p>
        </div>
    @endif

    @if(session('erro'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl animate-in fade-in duration-300">
            <p class="text-red-700 font-bold flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('erro') }}
            </p>
        </div>
    @endif

    @if($tabelaNaoExiste ?? false)
        <div class="mb-6 p-6 bg-amber-50 border-2 border-amber-300 rounded-2xl animate-in fade-in duration-300">
            <div class="flex items-start gap-4">
                <div class="text-2xl text-amber-600 flex-shrink-0">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-black text-amber-900 text-lg mb-2">Módulo em Configuração</h3>
                    <p class="text-amber-800 text-sm mb-3">
                        O banco de dados ainda não foi preparado para gerenciar Ocorrências RH. Execute o comando de migração para ativar este módulo:
                    </p>
                    <div class="bg-amber-900 text-amber-50 px-4 py-3 rounded-xl font-mono text-sm overflow-x-auto mb-3">
                        php artisan migrate
                    </div>
                    <p class="text-amber-700 text-xs">
                        Após executar este comando, as Ocorrências RH estarão disponíveis nesta página.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Filtro por Tipo -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.system.equipe.ocorrencias.index', $funcionario->id) }}" 
           class="px-4 py-2 rounded-full text-sm font-bold transition-all border
           {{ !$tipoFiltro ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            <i class="fas fa-list mr-1"></i> Todas
        </a>
        <a href="{{ route('admin.system.equipe.ocorrencias.index', $funcionario->id) }}?tipo=advertencia"
           class="px-4 py-2 rounded-full text-sm font-bold transition-all border
           {{ $tipoFiltro === 'advertencia' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            <i class="fas fa-exclamation-triangle mr-1"></i> Advertências
        </a>
        <a href="{{ route('admin.system.equipe.ocorrencias.index', $funcionario->id) }}?tipo=suspensao"
           class="px-4 py-2 rounded-full text-sm font-bold transition-all border
           {{ $tipoFiltro === 'suspensao' ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            <i class="fas fa-ban mr-1"></i> Suspensões
        </a>
        <a href="{{ route('admin.system.equipe.ocorrencias.index', $funcionario->id) }}?tipo=falta"
           class="px-4 py-2 rounded-full text-sm font-bold transition-all border
           {{ $tipoFiltro === 'falta' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            <i class="fas fa-calendar-times mr-1"></i> Faltas
        </a>
        <a href="{{ route('admin.system.equipe.ocorrencias.index', $funcionario->id) }}?tipo=atestado"
           class="px-4 py-2 rounded-full text-sm font-bold transition-all border
           {{ $tipoFiltro === 'atestado' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            <i class="fas fa-file-medical mr-1"></i> Atestados
        </a>
        <a href="{{ route('admin.system.equipe.ocorrencias.index', $funcionario->id) }}?tipo=desligamento"
           class="px-4 py-2 rounded-full text-sm font-bold transition-all border
           {{ $tipoFiltro === 'desligamento' ? 'bg-slate-700 text-white shadow-lg shadow-slate-700/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            <i class="fas fa-sign-out-alt mr-1"></i> Desligamentos
        </a>
    </div>

    <!-- Timeline de Ocorrências -->
    @forelse($ocorrencias as $ocorrencia)
        <div class="mb-4 bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all overflow-hidden group">
            <div class="flex flex-col md:flex-row md:items-start gap-4 p-6">
                <!-- Ícone & Tipo -->
                <div class="flex items-start gap-4 flex-1">
                    <div class="flex-shrink-0">
                        @php
                            $tipoConfig = [
                                'advertencia' => ['icon' => 'exclamation-triangle', 'bg' => 'bg-orange-100', 'text' => 'text-orange-600'],
                                'suspensao' => ['icon' => 'ban', 'bg' => 'bg-red-100', 'text' => 'text-red-600'],
                                'falta' => ['icon' => 'calendar-times', 'bg' => 'bg-amber-100', 'text' => 'text-amber-600'],
                                'atestado' => ['icon' => 'file-medical', 'bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
                                'desligamento' => ['icon' => 'sign-out-alt', 'bg' => 'bg-slate-100', 'text' => 'text-slate-600'],
                            ];
                            $config = $tipoConfig[$ocorrencia->tipo] ?? $tipoConfig['falta'];
                        @endphp
                        <div class="h-12 w-12 rounded-lg {{ $config['bg'] }} {{ $config['text'] }} flex items-center justify-center font-black text-lg">
                            <i class="fas fa-{{ $config['icon'] }}"></i>
                        </div>
                    </div>

                    <div class="flex-1">
                        <!-- Tipo & Status -->
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-sm font-black text-slate-600 uppercase tracking-tight">
                                {{ $ocorrencia->getTipoLabel() }}
                                @if($ocorrencia->subtipo)
                                    <span class="text-xs font-medium text-slate-500 ml-1">({{ $ocorrencia->getSubtipoLabel() }})</span>
                                @endif
                            </span>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest
                                {{ match($ocorrencia->status) {
                                    'registrada' => 'bg-slate-100 text-slate-600',
                                    'em_analise' => 'bg-amber-100 text-amber-700',
                                    'resolvida' => 'bg-emerald-100 text-emerald-700',
                                    'contestada' => 'bg-red-100 text-red-700',
                                    'arquivada' => 'bg-gray-100 text-gray-600',
                                    default => 'bg-slate-50 text-slate-500',
                                } }}">
                                {{ $ocorrencia->getStatusLabel() }}
                            </span>
                        </div>

                        <!-- Título -->
                        <h3 class="text-lg font-black text-slate-800 mb-2">{{ $ocorrencia->titulo }}</h3>

                        <!-- Descrição (se houver) -->
                        @if($ocorrencia->descricao)
                            <p class="text-sm text-slate-600 mb-3 leading-relaxed">{{ $ocorrencia->descricao }}</p>
                        @endif

                        <!-- Datas & Info -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                            <div>
                                <span class="font-black text-slate-400 uppercase tracking-widest block mb-0.5">Data da Ocorrência</span>
                                <span class="font-bold text-slate-700">{{ $ocorrencia->data_ocorrencia->format('d/m/Y') }}</span>
                            </div>
                            @if($ocorrencia->data_inicio && $ocorrencia->data_fim)
                                <div>
                                    <span class="font-black text-slate-400 uppercase tracking-widest block mb-0.5">Período</span>
                                    <span class="font-bold text-slate-700">
                                        {{ $ocorrencia->data_inicio->format('d/m') }} a {{ $ocorrencia->data_fim->format('d/m/Y') }}
                                        <span class="text-slate-500">({{ $ocorrencia->getDuracao() }} dias)</span>
                                    </span>
                                </div>
                            @endif
                            <div>
                                <span class="font-black text-slate-400 uppercase tracking-widest block mb-0.5">Registrado por</span>
                                <span class="font-bold text-slate-700">{{ $ocorrencia->criador->nome ?? 'Sistema' }}</span>
                            </div>
                        </div>

                        <!-- Referência (se houver) -->
                        @if($ocorrencia->referencia_documento)
                            <div class="mt-3 p-2 bg-slate-50 rounded-lg border border-slate-200">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Referência</span>
                                <span class="block font-bold text-slate-700">{{ $ocorrencia->referencia_documento }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Ações -->
                <div class="flex items-center gap-2 md:border-l md:border-slate-200 md:pl-4 flex-shrink-0">
                    @php
                        $contadorAnexos = $ocorrencia->anexos()->whereNull('deleted_at')->count();
                    @endphp
                    @if($contadorAnexos > 0)
                        <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold whitespace-nowrap" 
                              title="Esta ocorrência tem anexos">
                            <i class="fas fa-paperclip mr-1"></i> {{ $contadorAnexos }}
                        </span>
                    @endif
                    @if($permissoesOcorrencia['pode_editar'] ?? false)
                        <a href="{{ route('admin.system.equipe.ocorrencias.edit', [$funcionario->id, $ocorrencia->id]) }}" 
                           class="h-9 w-9 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-all flex items-center justify-center font-bold" 
                           title="Editar ocorrência" aria-label="Editar ocorrência">
                            <i class="fas fa-edit text-sm"></i>
                        </a>
                    @endif
                    @if($permissoesOcorrencia['pode_excluir'] ?? false)
                        <form action="{{ route('admin.system.equipe.ocorrencias.destroy', [$funcionario->id, $ocorrencia->id]) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" 
                                    class="h-9 w-9 rounded-lg bg-white border border-slate-200 text-red-600 hover:bg-red-50 transition-all flex items-center justify-center font-bold"
                                    title="Remover ocorrência" aria-label="Remover ocorrência"
                                    onclick="return confirm('Remover esta ocorrência?')">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Footer: Auditoria -->
            <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 text-[10px] text-slate-500 font-medium">
                Criado em {{ $ocorrencia->created_at->format('d/m/Y às H:i') }}
                @if($ocorrencia->updated_by)
                    • Última alteração por {{ $ocorrencia->atualizador->nome ?? 'Sistema' }} em {{ $ocorrencia->updated_at->format('d/m/Y às H:i') }}
                @endif
            </div>
        </div>

    @empty
        <div class="bg-white rounded-3xl p-12 text-center border-2 border-dashed border-slate-200">
            <i class="fas fa-inbox text-4xl text-slate-200 mb-4"></i>
            <h2 class="text-xl font-black text-slate-400">Nenhuma Ocorrência Registrada</h2>
            <p class="text-slate-400 text-sm mt-2 mb-6">
                @if($tipoFiltro)
                    Não há ocorrências do tipo "{{ \App\Models\EmployeeOccurrence::find(1)?->getTipoLabel() }}" para este colaborador.
                @else
                    Este colaborador não possui ocorrências RH registradas ainda.
                @endif
            </p>
            @if($permissoesOcorrencia['pode_criar'] ?? false)
                <a href="{{ route('admin.system.equipe.ocorrencias.create', $funcionario->id) }}" class="btn rounded-xl px-6 py-2 font-bold bg-brand-primary text-white inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Registrar Ocorrência
                </a>
            @elseif($permissoesOcorrencia['is_funcionario'] ?? false)
                <span class="inline-flex items-center gap-2 rounded-xl px-5 py-2 bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wide">
                    <i class="fas fa-eye"></i> Você pode acompanhar seu histórico de ocorrências
                </span>
            @endif
        </div>
    @endforelse

    <!-- Paginação -->
    @if($ocorrencias->hasPages())
        <div class="mt-8">
            {{ $ocorrencias->links() }}
        </div>
    @endif
</x-layouts.app>
