<x-layouts.app>
    <div class="mx-auto max-w-4xl">
        <div class="mb-6">
            <a href="{{ route('admin.support.help.show', $helpContent) }}" class="text-xs font-black uppercase tracking-widest text-slate-400 transition hover:text-brand-primary">Voltar para aula</a>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Quiz: {{ $helpContent->titulo }}</h1>
            <p class="mt-2 text-sm text-slate-500">Responda uma pergunta por vez. Não é possível pular questões.</p>
        </div>

        @if($errors->any())
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-2 flex items-center justify-between text-xs font-black uppercase tracking-widest text-slate-400">
                <span>Progresso do quiz</span>
                <span>{{ $estadoQuiz['respondidas'] }}/{{ $estadoQuiz['questions_total'] }}</span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                <div class="h-3 bg-brand-primary" style="width: {{ $estadoQuiz['percentual_progresso'] }}%"></div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 inline-flex rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-500">
                Tentativa {{ $estadoQuiz['tentativa_ativa']->tentativa }}
            </div>

            <h2 class="text-xl font-black text-slate-900">{{ $question->pergunta }}</h2>

            <form action="{{ route('admin.support.help.quiz.answer', $helpContent) }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">

                @foreach($question->answers as $answer)
                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-brand-primary/40 hover:bg-white">
                        <input type="radio" name="answer_id" value="{{ $answer->id }}" required class="h-4 w-4 border-slate-300 text-brand-primary focus:ring-brand-primary">
                        <span class="text-sm font-semibold text-slate-700">{{ $answer->texto }}</span>
                    </label>
                @endforeach

                <button type="submit" class="mt-2 inline-flex items-center gap-2 rounded-xl bg-brand-primary px-5 py-3 text-sm font-black text-white shadow">
                    Próxima
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
