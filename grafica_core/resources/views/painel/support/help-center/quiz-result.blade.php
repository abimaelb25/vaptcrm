<x-layouts.app>
    <div class="mx-auto max-w-4xl">
        <div class="mb-6">
            <a href="{{ route('admin.support.help.show', $helpContent) }}" class="text-xs font-black uppercase tracking-widest text-slate-400 transition hover:text-brand-primary">Voltar para aula</a>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Resultado do Quiz</h1>
            <p class="mt-2 text-sm text-slate-500">Aula: {{ $helpContent->titulo }}</p>
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nota</div>
                <div class="mt-2 text-3xl font-black text-slate-900">{{ $resultadoQuiz['attempt']->nota }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">% de acerto</div>
                <div class="mt-2 text-3xl font-black text-emerald-600">{{ $resultadoQuiz['attempt']->percentual_acerto }}%</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">% de erro</div>
                <div class="mt-2 text-3xl font-black text-rose-600">{{ $resultadoQuiz['attempt']->percentual_erro }}%</div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-black text-slate-700">Tentativa {{ $resultadoQuiz['attempt']->tentativa }}</div>
            @if(! $resultadoQuiz['mostrar_detalhes'])
                <p class="mt-2 text-sm text-slate-500">Nesta primeira tentativa mostramos apenas seu desempenho geral.</p>
            @else
                <p class="mt-2 text-sm text-slate-500">Resultado final registrado. Esta nota fica fixa.</p>

                <div class="mt-4 space-y-3">
                    @foreach($resultadoQuiz['detalhes'] as $item)
                        <div class="rounded-xl border px-4 py-3 {{ $item['correto'] ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' }}">
                            <div class="text-sm font-bold {{ $item['correto'] ? 'text-emerald-700' : 'text-rose-700' }}">{{ $item['question']->pergunta }}</div>
                            <div class="mt-1 text-xs font-semibold text-slate-600">Sua resposta: {{ $item['answer']->texto }}</div>
                            @if(! $item['correto'])
                                <div class="mt-2 text-xs font-black uppercase tracking-wider text-rose-700">Recomendamos revisar esta aula</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('admin.support.help.show', $helpContent) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700">Voltar para aula</a>

                @if(($estadoQuiz['pode_refazer'] ?? false))
                    <form action="{{ route('admin.support.help.quiz.retry', $helpContent) }}" method="POST" onsubmit="return confirm('Você só pode refazer uma única vez. Deseja continuar?');">
                        @csrf
                        <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-black text-white">Refazer quiz (1 única vez)</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
