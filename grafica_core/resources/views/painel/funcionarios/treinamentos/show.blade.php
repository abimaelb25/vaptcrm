<x-layouts.app titulo="Treinamentos do Colaborador">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <a href="{{ $podeMonitorarEquipe ? route('admin.system.equipe.treinamentos.index') : route('admin.system.equipe.show', $funcionario) }}" class="text-xs font-black uppercase tracking-widest text-slate-400 transition hover:text-brand-primary">{{ $podeMonitorarEquipe ? 'Voltar para equipe' : 'Voltar para ficha' }}</a>
            <h1 class="mt-2 text-3xl font-black text-brand-secondary">{{ $funcionario->nome_completo }}</h1>
            <p class="mt-1 text-sm text-slate-500">Acompanhe trilhas, módulos e aulas concluídas.</p>
        </div>
        <a href="{{ route('admin.system.equipe.show', $funcionario) }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Abrir ficha</a>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Aulas concluídas</div>
            <div class="mt-2 text-3xl font-black text-slate-900">{{ $summary['aulas_concluidas'] }}/{{ $summary['aulas_total'] }}</div>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Progresso geral</div>
            <div class="mt-2 text-3xl font-black text-brand-primary">{{ $summary['percentual_total'] }}%</div>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Perfil</div>
            <div class="mt-2 text-3xl font-black text-slate-900">{{ $funcionario->usuario->perfil ?? 'colaborador' }}</div>
        </div>
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Média em quiz</div>
            <div class="mt-2 text-3xl font-black {{ ($quizSummary['score_percent'] ?? null) !== null && $quizSummary['score_percent'] >= 70 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $quizSummary['score_percent'] ?? '--' }}@if(($quizSummary['score_percent'] ?? null) !== null)%@endif</div>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Aulas com quiz respondido</div>
            <div class="mt-2 text-3xl font-black text-slate-900">{{ $quizSummary['avaliacoes'] ?? 0 }}</div>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Segundas tentativas</div>
            <div class="mt-2 text-3xl font-black text-amber-600">{{ $quizSummary['refeitas'] ?? 0 }}</div>
        </div>
    </div>

    @if(($quizHistory ?? collect())->count() > 0)
        <section class="mb-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">Histórico de Quiz por Aula</h2>
            <p class="mt-1 text-sm text-slate-500">Compare a primeira e a segunda tentativa para validar evolução.</p>

            <div class="mt-4 space-y-3">
                @foreach($quizHistory as $item)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="text-sm font-black text-slate-800">{{ $item['aula']->titulo ?? 'Aula removida' }}</div>
                        <div class="mt-2 grid gap-3 md:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tentativa 1</div>
                                @if($item['tentativa_1'])
                                    <div class="mt-1 text-sm font-bold text-slate-700">Nota {{ $item['tentativa_1']->percentual_acerto }}%</div>
                                    <div class="text-xs text-slate-500">{{ $item['tentativa_1']->acertos }}/{{ $item['tentativa_1']->total_questoes }} acertos</div>
                                @else
                                    <div class="mt-1 text-xs font-semibold text-slate-400">Sem tentativa</div>
                                @endif
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tentativa 2</div>
                                @if($item['tentativa_2'])
                                    <div class="mt-1 text-sm font-bold text-slate-700">Nota {{ $item['tentativa_2']->percentual_acerto }}%</div>
                                    <div class="text-xs text-slate-500">{{ $item['tentativa_2']->acertos }}/{{ $item['tentativa_2']->total_questoes }} acertos</div>
                                @else
                                    <div class="mt-1 text-xs font-semibold text-slate-400">Não refez</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="space-y-6">
        @forelse($tracks as $trackData)
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">{{ $trackData['track']->titulo }}</h2>
                        <p class="text-sm text-slate-500">{{ $trackData['completed_lessons'] }}/{{ $trackData['total_lessons'] }} aulas concluídas</p>
                    </div>
                    <div class="w-full max-w-sm">
                        <div class="mb-1 flex items-center justify-between text-xs font-bold text-slate-600">
                            <span>Progresso da trilha</span>
                            <span>{{ $trackData['progress_percent'] }}%</span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-3 bg-brand-primary" style="width: {{ $trackData['progress_percent'] }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach($trackData['modules'] as $moduleData)
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="font-black text-slate-800">{{ $moduleData['module']->nome }}</h3>
                                    <p class="text-xs text-slate-500">{{ $moduleData['completed_lessons'] }}/{{ $moduleData['total_lessons'] }} aulas concluídas</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-brand-primary">{{ $moduleData['progress_percent'] }}%</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($moduleData['lessons'] as $lessonData)
                                    <div class="flex items-center justify-between rounded-xl bg-white px-4 py-3 text-sm">
                                        <div class="font-semibold text-slate-700">{{ $lessonData['lesson']->titulo }}</div>
                                        <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-wider {{ $lessonData['is_completed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $lessonData['progress_percent'] }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-16 text-center text-slate-400">
                Nenhuma trilha ativa encontrada para este colaborador.
            </div>
        @endforelse
    </div>
</x-layouts.app>
