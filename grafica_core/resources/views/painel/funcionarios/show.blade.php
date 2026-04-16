@php
/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:40
| Descrição: Perfil Profissional do Colaborador (Evolução RH).
*/
@endphp
<x-layouts.app titulo="Ficha do Colaborador - Vapt RH">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-5">
            <div class="h-20 w-20 rounded-3xl bg-brand-secondary text-white flex items-center justify-center font-black text-3xl shadow-xl shadow-brand-secondary/20 relative group border-4 border-white">
                @if($funcionario->usuario && $funcionario->usuario->avatar)
                    <img src="{{ asset('storage/' . $funcionario->usuario->avatar) }}" class="h-full w-full object-cover rounded-2xl">
                @else
                    {{ substr($funcionario->nome_completo, 0, 1) }}
                @endif
                <div class="absolute -bottom-2 -right-2 h-8 w-8 rounded-full bg-white flex items-center justify-center border-2 border-slate-100 shadow-sm text-emerald-500 text-xs">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-black text-brand-secondary tracking-tight">{{ $funcionario->nome_completo }}</h1>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-slate-200">{{ $funcionario->status_funcional }}</span>
                        @if($funcionario->user_id)
                            <span class="px-3 py-1 bg-brand-primary/10 text-brand-primary rounded-full text-[10px] font-black uppercase tracking-widest border border-brand-primary/20">
                                <i class="fas fa-shield-alt mr-1"></i> {{ $funcionario->usuario->perfil }}
                            </span>
                        @endif
                    </div>
                </div>
                <p class="text-slate-500 font-medium flex items-center gap-2">
                    <i class="fas fa-id-card-alt text-brand-primary"></i> {{ $funcionario->cargo_interno ?: ($funcionario->cargo_formal ?: 'Sem Função') }} &bull; <span class="font-bold text-slate-700">{{ $funcionario->setor ?: 'Geral' }}</span>
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.system.equipe.edit', $funcionario->id) }}" class="btn rounded-xl px-6 py-3 font-bold bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-edit"></i> Editar Ficha
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8" x-data="{ tab: 'perfil' }">
        
        <!-- SIDEBAR INFO & NAV -->
        <div class="lg:col-span-1 space-y-6">
            <nav class="flex flex-col gap-1">
                <button @click="tab = 'perfil'" :class="tab === 'perfil' ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20 scale-[1.02]' : 'bg-white text-slate-600 hover:bg-slate-50'" class="flex items-center gap-3 px-5 py-4 rounded-2xl font-black text-sm transition-all border border-slate-200/50">
                    <i class="fas fa-address-card w-5"></i> <span>Dossiê Profissional</span>
                </button>
                <button @click="tab = 'tarefas'" :class="tab === 'tarefas' ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20 scale-[1.02]' : 'bg-white text-slate-600 hover:bg-slate-50'" class="flex items-center justify-between px-5 py-4 rounded-2xl font-black text-sm transition-all border border-slate-200/50">
                    <div class="flex items-center gap-3"><i class="fas fa-tasks w-5"></i> <span>Operacional</span></div>
                    <span class="bg-slate-100 text-slate-400 text-[10px] px-2 py-0.5 rounded-full">{{ $funcionario->user_id ? 'Quadro' : '-' }}</span>
                </button>
                <button @click="tab = 'rh'" :class="tab === 'rh' ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20 scale-[1.02]' : 'bg-white text-slate-600 hover:bg-slate-50'" class="flex items-center gap-3 px-5 py-4 rounded-2xl font-black text-sm transition-all border border-slate-200/50">
                    <i class="fas fa-file-invoice-dollar w-5"></i> <span>Financeiro & RH</span>
                </button>
                <button @click="tab = 'documentos'" :class="tab === 'documentos' ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20 scale-[1.02]' : 'bg-white text-slate-600 hover:bg-slate-50'" class="flex items-center gap-3 px-5 py-4 rounded-2xl font-black text-sm transition-all border border-slate-200/50">
                    <i class="fas fa-folder-open w-5"></i> <span>Cofre Digital</span>
                </button>
                <button @click="tab = 'saude'" :class="tab === 'saude' ? 'bg-brand-primary text-white shadow-lg shadow-brand-primary/20 scale-[1.02]' : 'bg-white text-slate-600 hover:bg-slate-50'" class="flex items-center gap-3 px-5 py-4 rounded-2xl font-black text-sm transition-all border border-slate-200/50">
                    <i class="fas fa-heartbeat w-5"></i> <span>Saúde & Histórico</span>
                </button>
            </nav>

            <!-- Quick Contacts -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Contatos Rápidos</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fab fa-whatsapp"></i></div>
                        <span class="text-sm font-bold text-slate-700">{{ $funcionario->whatsapp ?: 'N/A' }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-envelope"></i></div>
                        <span class="text-xs font-bold text-slate-600 truncate flex-1">{{ $funcionario->email_pessoal ?? ($funcionario->usuario->email ?? 'N/A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT AREA -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- TAB: PERFIL / DOSSIÊ -->
            <div x-show="tab === 'perfil'" class="animate-in fade-in slide-in-from-right-4 duration-300">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-5">
                            <h2 class="text-xl font-black text-brand-secondary uppercase tracking-tight">Dossiê de Identificação</h2>
                            <div class="text-xs font-black text-slate-300">REF: {{ $funcionario->matricula ?? '#'.$funcionario->id }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-8 gap-x-12">
                            <div>
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nome de Registro</span>
                                <span class="font-bold text-slate-800">{{ $funcionario->nome_completo }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">E-mail Pessoal</span>
                                <span class="font-bold text-slate-800">{{ $funcionario->email_pessoal ?: '---' }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">CPF</span>
                                <span class="font-bold text-slate-800">{{ $funcionario->cpf ?: '---' }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Data de Nascimento</span>
                                <span class="font-bold text-slate-800">{{ $funcionario->data_nascimento ? $funcionario->data_nascimento->format('d/m/Y') : '---' }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Telefone / Whats</span>
                                <span class="font-bold text-slate-800">{{ $funcionario->whatsapp ?: ($funcionario->telefone ?: '---') }}</span>
                            </div>
                        </div>

                        <div class="mt-12 pt-8 border-t border-slate-100">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 border-l-4 border-slate-200 pl-3">Endereço Residencial</h3>
                            @if($funcionario->endereco)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <span class="block text-xs font-bold text-slate-700 tracking-tight">{{ $funcionario->endereco }}, {{ $funcionario->numero }}</span>
                                    <span class="block text-xs text-slate-500 font-medium">{{ $funcionario->bairro }} &bull; {{ $funcionario->complemento }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-slate-700 tracking-tight">{{ $funcionario->cidade }} - {{ $funcionario->uf }}</span>
                                    <span class="block text-xs text-slate-500 font-medium">CEP: {{ $funcionario->cep }}</span>
                                </div>
                            </div>
                            @else
                                <p class="text-sm text-slate-400 italic">Nenhum endereço cadastrado na ficha do colaborador.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Resumo de Acesso Sistema -->
                <div class="bg-slate-950 rounded-3xl p-8 text-white shadow-2xl relative overflow-hidden">
                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-5 italic opacity-80">
                            <i class="fas fa-fingerprint text-4xl text-brand-primary"></i>
                            <div>
                                <h3 class="font-black text-lg tracking-tight">Status de Acesso</h3>
                                <p class="text-xs text-white/50">Gerencie como {{ explode(' ', $funcionario->nome_completo)[0] }} entra no CRM.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-8">
                            @if($funcionario->user_id)
                                <div class="text-center">
                                    <span class="block text-[10px] font-black text-white/30 uppercase tracking-widest mb-1">Perfil Login</span>
                                    <span class="bg-brand-primary/20 text-brand-primary px-3 py-1 rounded-full text-xs font-black uppercase tracking-tighter">{{ $funcionario->usuario->perfil }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[10px] font-black text-white/30 uppercase tracking-widest mb-1">E-mail Sistema</span>
                                    <span class="font-black text-sm">{{ $funcionario->usuario->email }}</span>
                                </div>
                            @else
                                <p class="text-xs font-bold text-white/40 uppercase">Este colaborador não possui acesso ao sistema.</p>
                            @endif
                        </div>
                    </div>
                    <!-- Decor -->
                    <div class="absolute -top-10 -right-10 h-40 w-40 bg-brand-primary/20 rounded-full blur-3xl"></div>
                </div>
            </div>

            <!-- TAB: TAREFAS (OPERACIONAL) -->
            <div x-show="tab === 'tarefas'" class="animate-in fade-in slide-in-from-right-4 duration-300">
                @if(!$funcionario->user_id)
                    <div class="bg-white rounded-3xl p-12 text-center border-2 border-dashed border-slate-200">
                        <i class="fas fa-lock text-4xl text-slate-200 mb-4"></i>
                        <h2 class="text-xl font-black text-slate-400">Sem Vínculo Operacional</h2>
                        <p class="text-slate-400 text-sm mt-2">Para designar tarefas, o colaborador precisa de um usuário ativo no sistema.</p>
                    </div>
                @else
                    <!-- Kanban Header -->
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="text-2xl font-black text-brand-secondary">Quadro Kanban</h2>
                        <button onclick="document.getElementById('modalNovaTarefa').classList.remove('hidden')" class="btn bg-brand-primary text-white rounded-xl px-5 py-2 font-bold shadow-lg shadow-brand-primary/20 flex items-center gap-2">
                            <i class="fas fa-plus"></i> Designar Projeto
                        </button>
                    </div>

                    @php
                        $statusMap = [
                            'backlog' => ['label' => 'Backlog', 'color' => 'bg-slate-50', 'text' => 'text-slate-500'],
                            'a_fazer' => ['label' => 'A Fazer', 'color' => 'bg-blue-50/50', 'text' => 'text-blue-600'],
                            'em_andamento' => ['label' => 'Execução', 'color' => 'bg-amber-50/50', 'text' => 'text-amber-600'],
                            'bloqueada' => ['label' => 'Impedido', 'color' => 'bg-red-50/50', 'text' => 'text-red-600'],
                            'concluida' => ['label' => 'Entregue', 'color' => 'bg-emerald-50/50', 'text' => 'text-emerald-600'],
                        ];
                    @endphp

                    <div class="flex overflow-x-auto gap-4 pb-4 -mx-4 px-4 min-h-[500px] scrollbar-hide">
                        @foreach($statusMap as $sk => $scfg)
                        <div class="flex-shrink-0 w-72 flex flex-col h-full rounded-2xl border border-slate-100 bg-white">
                            <div class="p-4 border-b border-slate-50 flex items-center justify-between">
                                <span class="font-black uppercase text-[10px] {{ $scfg['text'] }} tracking-widest">{{ $scfg['label'] }}</span>
                                <span class="bg-slate-100 text-slate-400 text-[10px] font-black px-2 py-0.5 rounded-full">{{ $tarefas[$sk]->count() }}</span>
                            </div>
                            <div class="flex-1 p-3 space-y-3 overflow-y-auto">
                                @forelse($tarefas[$sk] as $t)
                                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:translate-x-1 transition-transform cursor-pointer group">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-[9px] font-black uppercase tracking-tighter px-2 py-0.5 rounded-md
                                            {{ $t->prioridade === 'urgente' ? 'bg-red-600 text-white' : ($t->prioridade === 'alta' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-500') }}">
                                            {{ $t->prioridade }}
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-xs leading-tight">{{ $t->titulo }}</h4>
                                    <div class="mt-4 flex items-center justify-between">
                                        <div class="text-[9px] font-black text-slate-300 uppercase italic">#{{ $t->id }}</div>
                                        @if($t->prazo)
                                            <span class="text-[9px] font-black {{ $t->prazo->isPast() ? 'text-rose-500' : 'text-slate-400' }}">{{ $t->prazo->format('d/m') }}</span>
                                        @endif
                                    </div>
                                </div>
                                @empty
                                    <div class="py-10 text-center opacity-20">
                                        <i class="fas fa-layer-group text-2xl"></i>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- TAB: RH & FINANCEIRO -->
            <div x-show="tab === 'rh'" class="animate-in fade-in slide-in-from-right-4 duration-300">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                        <h3 class="font-black text-slate-800 text-lg mb-6 flex items-center gap-2">
                            <i class="fas fa-coins text-brand-primary"></i> Remuneração & Vínculo
                        </h3>
                        <div class="space-y-6">
                            <div class="flex justify-between items-center border-b border-slate-50 pb-4">
                                <span class="text-xs font-black text-slate-400 uppercase">Vínculo Contratual</span>
                                <span class="font-black text-slate-700 uppercase">{{ $funcionario->tipo_vinculo ?: '---' }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-slate-50 pb-4">
                                <span class="text-xs font-black text-slate-400 uppercase">Salário Base</span>
                                <span class="font-black text-brand-secondary text-xl">R$ {{ number_format((float) $funcionario->salario_base, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-slate-50 pb-4">
                                <span class="text-xs font-black text-slate-400 uppercase">Comissão Comercial</span>
                                <span class="font-black text-slate-700">{{ (float) $funcionario->comissao_percentual }}%</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-black text-slate-400 uppercase">Carga Horária</span>
                                <span class="font-black text-slate-700">{{ $funcionario->carga_horaria_semanal ?: '---' }}h / Semana</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                        <h3 class="font-black text-slate-800 text-lg mb-6 flex items-center gap-2">
                            <i class="fas fa-umbrella-beach text-brand-primary"></i> Ciclo de Férias
                        </h3>
                        @if($funcionario->ferias->isNotEmpty())
                            @foreach($funcionario->ferias as $f)
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 mb-4 transition-all hover:border-brand-primary/30">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-black uppercase text-slate-400">P. Aquisitivo</span>
                                    <span class="text-xs font-black bg-brand-primary/10 text-brand-primary px-2 py-0.5 rounded uppercase">{{ $f->status }}</span>
                                </div>
                                <p class="font-black text-slate-700 text-sm italic">{{ $f->periodo_aquisitivo_inicio->format('d/m/y') }} &rarr; {{ $f->periodo_aquisitivo_fim->format('d/m/y') }}</p>
                                <div class="mt-3 flex items-center gap-4 text-xs font-bold text-slate-500">
                                    <span>Saldo: <strong>{{ $f->saldo_dias }} dias</strong></span>
                                    <span>Gozados: <strong>{{ $f->dias_gozados }} dias</strong></span>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="h-40 flex flex-col items-center justify-center border-2 border-dashed border-slate-100 rounded-3xl opacity-30">
                                <i class="fas fa-calendar-alt text-3xl mb-3"></i>
                                <span class="text-sm font-black uppercase">Nenhum perídio registrado</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-orange-50/50 rounded-3xl border-2 border-dashed border-brand-primary/20 p-8 text-center">
                    <h3 class="font-black text-brand-primary uppercase text-sm mb-2">Relato Funcional / Obs. RH</h3>
                    <p class="text-slate-600 font-medium italic">"{{ $funcionario->observacoes_gerais ?: 'Sem anotações internas registradas para este colaborador.' }}"</p>
                </div>
            </div>

            <!-- TAB: DOCUMENTOS -->
            <div x-show="tab === 'documentos'" class="animate-in fade-in slide-in-from-right-4 duration-300">
                <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-xl font-black text-brand-secondary">Pastas & Arquivos</h2>
                        <button class="text-xs font-black text-brand-primary uppercase tracking-widest hover:underline">+ Novo Documento</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @forelse($funcionario->documentos as $doc)
                        <div class="group p-5 rounded-2xl border border-slate-100 bg-slate-50/30 hover:bg-white hover:shadow-xl hover:-translate-y-1 transition-all h-full flex flex-col">
                            <div class="flex items-center justify-between mb-4">
                                <div class="h-10 w-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <span class="text-[9px] font-black bg-slate-200 text-slate-500 px-2 py-0.5 rounded uppercase tracking-tighter">{{ $doc->tipo_documento }}</span>
                            </div>
                            <h4 class="font-black text-slate-800 text-sm mb-1 truncate">{{ $doc->titulo }}</h4>
                            <p class="text-[10px] text-slate-400 font-bold uppercase">{{ number_format($doc->tamanho_bytes / 1024, 0) }} KB &bull; Enviado em {{ $doc->created_at->format('d/m/y') }}</p>
                            <div class="mt-6 pt-4 border-t border-slate-100 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ asset('storage/' . $doc->arquivo_path) }}" target="_blank" class="w-full block bg-slate-800 text-white text-center py-2 rounded-xl text-xs font-black transition-all hover:bg-black uppercase">Ver Documento</a>
                            </div>
                        </div>
                        @empty
                            <div class="col-span-full py-20 text-center opacity-30">
                                <i class="fas fa-folder-minus text-5xl mb-4"></i>
                                <h3 class="font-black text-slate-400 uppercase">Nenhum anexo digitalizado</h3>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB: SAÚDE & HISTÓRICO -->
            <div x-show="tab === 'saude'" class="animate-in fade-in slide-in-from-right-4 duration-300 space-y-6">
                <!-- Timeline do Colaborador -->
                <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                    <h2 class="text-xl font-black text-brand-secondary mb-8">Linha do Tempo Funcional</h2>
                    
                    <div class="relative space-y-8 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
                        @forelse($funcionario->historico as $event)
                        <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                             <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-200 text-slate-500 shadow-sm shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">
                                <i class="fas fa-clock text-xs"></i>
                            </div>
                            <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-3xl border border-slate-100 bg-slate-50/50 shadow-sm transition hover:shadow-lg">
                                <div class="flex items-center justify-between space-x-2 mb-2">
                                    <div class="font-black text-slate-800 text-sm uppercase tracking-tight">{{ $event->titulo }}</div>
                                    <time class="font-black text-brand-primary text-[10px]">{{ $event->data_evento->format('d/m/Y') }}</time>
                                </div>
                                <div class="text-xs text-slate-500 font-medium leading-relaxed">{{ $event->descricao }}</div>
                                <div class="mt-3 text-[9px] font-black text-slate-300 uppercase tracking-widest italic">Registrado por: {{ $event->autor->nome ?? 'Sistema' }}</div>
                            </div>
                        </div>
                        @empty
                            <p class="text-center text-slate-400 font-bold py-10 uppercase text-xs">Início do histórico...</p>
                        @endforelse
                    </div>
                </div>

                <!-- Painel ASO / Saúde -->
                <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-xl font-black text-blue-600 flex items-center gap-2">
                            <i class="fas fa-notes-medical"></i> Controle de Saúde Ocupacional
                        </h2>
                         <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">LGPD SENSÍVEL</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($funcionario->registrosSaude as $aso)
                        <div class="flex items-center gap-4 p-5 rounded-2xl bg-blue-50/50 border border-blue-100 hover:bg-blue-50 transition-all">
                            <div class="h-12 w-12 rounded-xl bg-white shadow-sm flex items-center justify-center text-blue-500 text-xl">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black text-slate-800 text-xs uppercase">{{ $aso->tipo_registro }}</h4>
                                <p class="text-[10px] font-bold text-slate-500 mt-1">Realizado: {{ $aso->data_registro->format('d/m/Y') }} @if($aso->validade_ate) &bull; Vant: <span class="text-rose-500">{{ $aso->validade_ate->format('d/m/Y') }}</span> @endif</p>
                            </div>
                            @if($aso->arquivo_path)
                                <a href="{{ asset('storage/'.$aso->arquivo_path) }}" target="_blank" class="p-2 rounded-xl bg-white text-blue-400 hover:text-blue-600 shadow-sm"><i class="fas fa-eye"></i></a>
                            @endif
                        </div>
                        @empty
                            <p class="text-sm text-slate-400 italic col-span-2">Sem exames ou atestados médicos vinculados.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nova Tarefa (Reutilizado do CRM) -->
    <div id="modalNovaTarefa" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="document.getElementById('modalNovaTarefa').classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-3xl shadow-2xl p-8 transform transition-all">
            <h2 class="text-2xl font-black text-brand-secondary mb-6 flex items-center gap-3">
                <i class="fas fa-plus-circle text-brand-primary"></i> Designar Projeto
            </h2>
            <form action="{{ route('admin.ops.tasks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="responsavel_id" value="{{ $funcionario->user_id }}">
                <div class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Título da Tarefa *</label>
                        <input type="text" name="titulo" required placeholder="Ex: Criar arte Cartão de Visita #123" class="w-full rounded-xl border-slate-200 focus:border-brand-primary focus:ring-brand-primary font-bold text-slate-800 px-4 py-3 bg-slate-50/50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Instruções / Briefing</label>
                        <textarea name="descricao" rows="3" class="w-full rounded-2xl border-slate-200 focus:border-brand-primary focus:ring-brand-primary font-medium text-slate-700 px-4 py-3 bg-slate-50/50" placeholder="Descreva os detalhes importantes aqui..."></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Prioridade</label>
                            <select name="prioridade" class="w-full rounded-xl border-slate-200 font-bold text-slate-800 px-4 py-3 bg-slate-50/50">
                                <option value="baixa">Baixa</option>
                                <option value="media" selected>Média</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">⚠️ Urgente</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status de Início</label>
                            <select name="status" class="w-full rounded-xl border-slate-200 font-bold text-slate-800 px-4 py-3 bg-slate-50/50">
                                <option value="backlog">Backlog</option>
                                <option value="a_fazer" selected>A Fazer</option>
                            </select>
                        </div>
                         <div class="col-span-2">
                             <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Prazo de Entrega</label>
                             <input type="date" name="prazo" class="w-full rounded-xl border-slate-200 font-bold text-slate-800 px-4 py-3 bg-slate-50/50">
                         </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modalNovaTarefa').classList.add('hidden')" class="px-6 py-3 font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Cancelar</button>
                    <button type="submit" class="px-10 py-3 font-black text-white bg-brand-primary hover:bg-orange-500 shadow-xl shadow-brand-primary/20 rounded-2xl transition-all">
                        Designar Agora
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
