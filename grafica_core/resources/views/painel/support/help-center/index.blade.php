{{-- Autoria: Abimael Borges | https://abimaelborges.adv.br | Data: 2026-04-16 --}}
<x-layouts.app>
    @if($continuarTreinamento)
        <div class="mb-6 rounded-3xl border border-brand-primary/20 bg-gradient-to-r from-brand-primary/10 via-white to-amber-50 p-5 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-[0.25em] text-brand-primary">Continuar de onde parou</div>
                    <h2 class="mt-2 text-xl font-black text-slate-900">{{ $continuarTreinamento['lesson']->titulo }}</h2>
                    <p class="mt-1 text-sm text-slate-600">Seu avanço atual nesta aula está em {{ $continuarTreinamento['progress_percent'] }}%.</p>
                </div>
                <a href="{{ route('admin.support.help.show', $continuarTreinamento['lesson']) }}" class="rounded-2xl bg-brand-primary px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-primary/20 transition hover:-translate-y-0.5">Continuar treinamento</a>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if(($alertasObrigatorios ?? collect())->count() > 0)
        <div class="mb-6 p-5 bg-amber-50 border border-amber-200 rounded-xl">
            <h3 class="text-sm font-black uppercase tracking-wider text-amber-800 mb-2">
                Treinamentos Recomendados
            </h3>
            <p class="text-sm text-amber-700">
                Você tem {{ $alertasObrigatorios->count() }} treinamento(s) ainda não concluído(s). Isso não bloqueia seu uso do sistema.
            </p>
        </div>
    @endif

    <div class="mb-8 text-center py-6 bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 rounded-2xl shadow-lg relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
        <div class="relative z-10 px-6">
            <h1 class="text-3xl md:text-4xl font-black text-white mb-3">Seu espaço de treinamentos</h1>
            <p class="text-blue-200 text-lg max-w-2xl mx-auto">Aprenda no seu ritmo e avance pelas próximas etapas do sistema com mais segurança.</p>
            <div class="mt-6 inline-flex items-center gap-4 rounded-full border border-white/20 bg-white/10 px-5 py-3 text-sm font-bold text-white/90 backdrop-blur">
                <span>{{ $resumoBiblioteca['aulas_concluidas'] }} de {{ $resumoBiblioteca['aulas_total'] }} aulas concluídas</span>
                <span class="text-amber-300">{{ $resumoBiblioteca['percentual_total'] }}% de avanço</span>
            </div>
            
            <div class="mt-8 max-w-xl mx-auto flex">
                <input type="text" placeholder="Buscar por assunto, ex: 'Configurar PDV'" class="w-full px-5 py-4 rounded-l-xl border-0 focus:ring-4 focus:ring-blue-400/50 text-gray-800 font-medium">
                <button class="bg-amber-500 hover:bg-amber-600 px-6 py-4 rounded-r-xl text-white font-bold transition-colors">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>

    @if($destaques->count() > 0)
    <div class="mb-12">
        <div class="flex items-center gap-2 mb-6 border-b pb-2">
            <i class="fas fa-fire text-amber-500 text-xl"></i>
            <h2 class="text-2xl font-bold text-slate-800">Treinamentos em destaque</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($destaques as $video)
                <a href="{{ route('admin.support.help.show', $video) }}" class="group block bg-white rounded-xl shadow-sm border hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="relative aspect-video bg-slate-900 overflow-hidden">
                        @if($video->thumbnail)
                            <img src="{{ $video->thumbnail }}" alt="{{ $video->titulo }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80 group-hover:opacity-100">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-700">
                                <i class="fab fa-youtube text-4xl"></i>
                            </div>
                        @endif
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-black/30 backdrop-blur-sm">
                            <div class="w-16 h-16 rounded-full bg-brand-primary/90 flex items-center justify-center text-white scale-75 group-hover:scale-100 transition-transform duration-300">
                                <i class="fas fa-play text-xl ml-1"></i>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 relative">
                        <div class="absolute -top-3 right-4 bg-amber-500 text-white text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded shadow">Destaque</div>
                        <h3 class="font-bold text-lg text-slate-800 group-hover:text-brand-primary transition-colors line-clamp-2">{{ $video->titulo }}</h3>
                        <p class="text-sm text-slate-500 mt-2 line-clamp-2">{{ $video->descricao }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <div>
        <div class="flex items-center gap-2 mb-6 border-b pb-2 mt-10">
            <i class="fas fa-play-circle text-blue-500 text-xl"></i>
            <h2 class="text-2xl font-bold text-slate-800">Treinamentos em andamento</h2>
        </div>

        @if(($bibliotecaTracks ?? collect())->count() > 0)
            <div class="space-y-8 mb-12">
                @foreach($bibliotecaTracks as $trackData)
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 px-6 py-6 text-white">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                                <div>
                                    <div class="mb-2 inline-flex rounded-full bg-white/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.25em] text-white/80">Treinamento</div>
                                    <h3 class="text-2xl font-black">{{ $trackData['track']->titulo }}</h3>
                                    <p class="mt-2 max-w-2xl text-sm text-white/70">{{ $trackData['track']->descricao ?: 'Trilha sem descrição adicional.' }}</p>
                                </div>
                                <div class="w-full max-w-sm rounded-2xl bg-white/10 p-4 backdrop-blur">
                                    <div class="mb-1 flex items-center justify-between text-xs font-black uppercase tracking-widest text-white/80">
                                        <span>Avanço da trilha</span>
                                        <span>{{ $trackData['progress_percent'] }}%</span>
                                    </div>
                                    <div class="h-3 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-3 bg-amber-400" style="width: {{ $trackData['progress_percent'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-5 p-6 lg:grid-cols-2">
                            @foreach($trackData['modules'] as $moduleData)
                                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <div class="mb-4 flex items-start justify-between gap-4">
                                        <div>
                                            <h4 class="text-lg font-black text-slate-800">{{ $moduleData['module']->nome }}</h4>
                                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $moduleData['completed_lessons'] }}/{{ $moduleData['total_lessons'] }} aulas</p>
                                        </div>
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-brand-primary shadow-sm">{{ $moduleData['progress_percent'] }}%</span>
                                    </div>
                                    <div class="space-y-3">
                                        @foreach($moduleData['lessons'] as $lessonData)
                                            <a href="{{ route('admin.support.help.show', $lessonData['lesson']) }}" class="flex items-center gap-4 rounded-2xl bg-white px-4 py-3 transition hover:-translate-y-0.5 hover:shadow-md">
                                                <div class="h-12 w-16 overflow-hidden rounded-xl bg-slate-100">
                                                    @if($lessonData['lesson']->thumbnail_resolved)
                                                        <img src="{{ $lessonData['lesson']->thumbnail_resolved }}" alt="{{ $lessonData['lesson']->titulo }}" class="h-full w-full object-cover">
                                                    @endif
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2 text-sm font-bold text-slate-800 line-clamp-2">
                                                        <span>{{ $lessonData['lesson']->titulo }}</span>
                                                        @if($lessonData['is_next'])
                                                            <span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-amber-700">Próxima</span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $lessonData['lesson']->tipo_label }}</div>
                                                </div>
                                                <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-widest {{ $lessonData['is_completed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $lessonData['progress_percent'] }}%</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif

        @if(($conteudosAvulsos ?? collect())->count() > 0)
            <div class="mb-4 flex items-center gap-2 border-b pb-2">
                <i class="fas fa-layer-group text-slate-500 text-xl"></i>
                <h2 class="text-2xl font-bold text-slate-800">Conteúdos extras</h2>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($conteudosAvulsos as $lessonData)
                    <a href="{{ route('admin.support.help.show', $lessonData['lesson']) }}" class="group block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="relative aspect-video bg-slate-100">
                            @if($lessonData['lesson']->thumbnail_resolved)
                                <img src="{{ $lessonData['lesson']->thumbnail_resolved }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @endif
                            <div class="absolute bottom-2 right-2 rounded-full bg-black/70 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-white">{{ $lessonData['lesson']->tipo_label }}</div>
                            <div class="absolute bottom-2 left-2 rounded-full bg-brand-primary/90 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-white">{{ $lessonData['progress_percent'] }}%</div>
                        </div>
                        <div class="p-4">
                            <h3 class="text-sm font-bold leading-tight text-slate-800 line-clamp-2 group-hover:text-brand-primary">{{ $lessonData['lesson']->titulo }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
        
        @if(($bibliotecaTracks ?? collect())->count() === 0 && ($conteudosAvulsos ?? collect())->count() === 0 && $destaques->count() === 0)
            <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-dashed border-slate-300">
                <i class="fas fa-video-slash text-5xl text-slate-300 mb-4"></i>
                <h3 class="text-lg font-bold text-slate-600">Nenhum treinamento disponível</h3>
                <p class="text-slate-400">Em breve sua biblioteca de treinamentos ficará disponível aqui.</p>
            </div>
        @endif
    </div>

    <div class="mt-16 bg-blue-50 rounded-2xl p-8 border border-blue-100 flex flex-col md:flex-row items-center justify-between gap-6 shadow-inner">
        <div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Ainda precisa de ajuda especializada?</h3>
            <p class="text-slate-600">Nossa equipe de especialistas está pronta para resolver o seu problema técnico.</p>
        </div>
        <a href="{{ route('admin.support.meus-tickets.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-md transition-transform hover:scale-105 shrink-0">
            <i class="fas fa-life-ring mr-2"></i> Abrir Ticket de Suporte
        </a>
    </div>
</x-layouts.app>
