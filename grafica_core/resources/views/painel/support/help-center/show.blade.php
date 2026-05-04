<x-layouts.app>
    <div class="mb-6">
        <a href="{{ route('admin.support.help.index') }}" class="text-xs font-black text-slate-400 hover:text-brand-primary mb-2 inline-flex items-center gap-2 uppercase tracking-tighter transition-colors">
            <i class="fas fa-arrow-left"></i> Voltar para Treinamentos
        </a>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">{{ $helpContent->titulo }}</h1>
        <p class="text-slate-500 font-medium mt-1">{{ $helpContent->descricao ?: 'Treinamento da VaptAcademy' }}</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('erro'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 font-semibold">
            {{ session('erro') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                @if($helpContent->youtube_video_id)
                    <div class="aspect-video">
                        <iframe
                            class="w-full h-full"
                            src="https://www.youtube.com/embed/{{ $helpContent->youtube_video_id }}"
                            title="{{ $helpContent->titulo }}"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                @elseif($helpContent->thumbnail_resolved)
                    <img src="{{ $helpContent->thumbnail_resolved }}" alt="{{ $helpContent->titulo }}" class="w-full object-cover">
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="px-2 py-1 text-[10px] rounded bg-slate-100 text-slate-600 font-bold uppercase">{{ $helpContent->tipo_label }}</span>
                    @if($helpContent->course?->track)
                        <span class="px-2 py-1 text-[10px] rounded bg-amber-100 text-amber-700 font-bold uppercase">{{ $helpContent->course->track->titulo }}</span>
                    @endif
                    @if($helpContent->course)
                        <span class="px-2 py-1 text-[10px] rounded bg-indigo-100 text-indigo-700 font-bold uppercase">{{ $helpContent->course->nome }}</span>
                    @endif
                    <span class="px-2 py-1 text-[10px] rounded bg-brand-primary/10 text-brand-primary font-bold uppercase">
                        {{ (int) ($progresso->percentual_concluido ?? 0) }}% concluído
                    </span>
                    @if($helpContent->isObrigatorio())
                        <span class="px-2 py-1 text-[10px] rounded bg-rose-100 text-rose-700 font-bold uppercase">Obrigatório</span>
                    @endif
                </div>

                <div class="grid gap-4 md:grid-cols-2 mb-5">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Progresso do módulo</div>
                        <div class="text-3xl font-black text-slate-900">{{ $moduleProgressPercent }}%</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Progresso da trilha</div>
                        <div class="text-3xl font-black text-brand-primary">{{ $trackProgressPercent }}%</div>
                    </div>
                </div>

                <p class="text-sm text-slate-600 leading-relaxed">
                    {{ $helpContent->descricao ?: 'Sem descrição adicional para este treinamento.' }}
                </p>

                @if($helpContent->conteudo_texto)
                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm leading-relaxed text-slate-700 whitespace-pre-line">{{ $helpContent->conteudo_texto }}</div>
                @endif

                @if($helpContent->material_apoio_url || ($quizStatus['has_quiz'] ?? false) || !empty($helpContent->quiz_payload))
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @if($helpContent->material_apoio_url)
                            <a href="{{ $helpContent->material_apoio_url }}" target="_blank" class="rounded-2xl border border-blue-100 bg-blue-50 p-4 transition hover:bg-blue-100">
                                <div class="text-xs font-black uppercase tracking-widest text-blue-500">Material complementar</div>
                                <div class="mt-2 text-sm font-bold text-blue-900">{{ $helpContent->material_apoio_titulo ?: 'Abrir material de apoio' }}</div>
                            </a>
                        @endif
                        @if(($quizStatus['has_quiz'] ?? false))
                            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                                <div class="text-xs font-black uppercase tracking-widest text-amber-600">Quiz</div>
                                <div class="mt-2 text-sm font-bold text-amber-900">{{ $quizStatus['questions_total'] ?? 0 }} questões nesta aula</div>
                                <p class="mt-2 text-xs font-semibold text-amber-700">Fluxo sequencial: 1 questão por vez e sem pular.</p>
                                <a href="{{ route('admin.support.help.quiz.show', $helpContent) }}" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-amber-500 px-3 py-2 text-xs font-black uppercase tracking-wider text-white">
                                    {{ ($quizStatus['tentativa_ativa'] ?? null) ? 'Continuar quiz' : 'Iniciar quiz' }}
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                <form action="{{ route('admin.support.help.complete', $helpContent) }}" method="POST" class="mt-5">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold shadow transition-all {{ ($quizStatus['has_quiz'] ?? false) && !($quizStatus['ultima_finalizada'] ?? null) ? 'bg-slate-300 text-slate-600 cursor-not-allowed' : 'bg-brand-primary text-white hover:shadow-lg' }}" {{ (($quizStatus['has_quiz'] ?? false) && !($quizStatus['ultima_finalizada'] ?? null)) ? 'disabled' : '' }}>
                        <i class="fas fa-check-circle"></i>
                        Marcar como concluído
                    </button>
                    @if(($quizStatus['has_quiz'] ?? false) && !($quizStatus['ultima_finalizada'] ?? null))
                        <p class="mt-2 text-xs font-semibold text-slate-500">Para concluir esta aula, finalize o quiz primeiro.</p>
                    @endif
                </form>
            </div>
        </div>

        <aside class="space-y-4">
            @if(($sameModuleLessons ?? collect())->count() > 0)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-500 mb-3">Próximas aulas desta etapa</h3>

                    <div class="space-y-3">
                        @foreach($sameModuleLessons as $lessonData)
                            <a href="{{ route('admin.support.help.show', $lessonData['lesson']) }}" class="block rounded-xl border border-slate-200 p-3 hover:bg-slate-50 transition-colors {{ $lessonData['lesson']->id === $helpContent->id ? 'ring-2 ring-brand-primary/20 border-brand-primary/30' : '' }}">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-bold text-slate-700 line-clamp-2">
                                        {{ $lessonData['lesson']->titulo }}
                                        @if($lessonData['is_next'] && $lessonData['lesson']->id !== $helpContent->id)
                                            <span class="ml-2 rounded-full bg-amber-100 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-amber-700">Próxima</span>
                                        @endif
                                    </div>
                                    <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-widest {{ $lessonData['is_completed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $lessonData['progress_percent'] }}%</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-500 mb-3">Sugeridos</h3>

                <div class="space-y-3">
                    @forelse($sugeridos as $sug)
                        <a href="{{ route('admin.support.help.show', $sug) }}" class="block rounded-xl border border-slate-200 p-3 hover:bg-slate-50 transition-colors">
                            <p class="text-sm font-bold text-slate-700 line-clamp-2">{{ $sug->titulo }}</p>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400">Sem sugestões no momento.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-blue-50 rounded-2xl border border-blue-100 p-5">
                <h3 class="text-sm font-black uppercase tracking-wider text-blue-700 mb-2">Lembrete rápido</h3>
                <p class="text-sm text-blue-700">
                    Avançar nos treinamentos melhora o uso do sistema no dia a dia e reduz retrabalho da operação.
                </p>
            </div>

            @if(($sameTrackModules ?? collect())->count() > 0)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-500 mb-3">Na mesma trilha</h3>
                    <div class="space-y-3">
                        @foreach($sameTrackModules as $moduleData)
                            <div class="rounded-xl border border-slate-200 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-bold text-slate-700">{{ $moduleData['module']->nome }}</div>
                                    <span class="text-xs font-black text-brand-primary">{{ $moduleData['progress_percent'] }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
</x-layouts.app>
