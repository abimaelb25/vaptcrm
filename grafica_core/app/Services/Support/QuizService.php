<?php

namespace App\Services\Support;

use App\Models\HelpContent;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\Usuario;
use App\Notifications\AcademyQuizManagerAlertNotification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class QuizService
{
    public function sincronizarQuizAula(HelpContent $helpContent, array $payload): void
    {
        if (! $this->quizTablesDisponiveis()) {
            return;
        }

        $questoes = collect($payload)
            ->map(function ($item, int $index) {
                $pergunta = trim((string) ($item['pergunta'] ?? ''));
                $answers = collect($item['alternativas'] ?? [])
                    ->map(function ($answer, int $answerIndex) {
                        return [
                            'texto' => trim((string) ($answer['texto'] ?? '')),
                            'is_correct' => (bool) ($answer['is_correct'] ?? false),
                            'ordem' => $answerIndex + 1,
                        ];
                    })
                    ->filter(fn (array $answer) => $answer['texto'] !== '')
                    ->values();

                return [
                    'pergunta' => $pergunta,
                    'ordem' => $index + 1,
                    'alternativas' => $answers,
                ];
            })
            ->filter(fn (array $item) => $item['pergunta'] !== '')
            ->values();

        if ($questoes->isEmpty()) {
            $helpContent->quizQuestions()->delete();
            return;
        }

        foreach ($questoes as $questao) {
            $corretas = collect($questao['alternativas'])->where('is_correct', true)->count();
            if ($corretas !== 1 || collect($questao['alternativas'])->count() < 2) {
                throw ValidationException::withMessages([
                    'quiz_questions' => 'Cada questão precisa ter ao menos 2 alternativas e exatamente 1 correta.',
                ]);
            }
        }

        DB::transaction(function () use ($helpContent, $questoes): void {
            $helpContent->quizQuestions()->delete();

            foreach ($questoes as $questaoData) {
                $question = QuizQuestion::create([
                    'help_content_id' => $helpContent->id,
                    'pergunta' => $questaoData['pergunta'],
                    'ordem' => $questaoData['ordem'],
                ]);

                foreach ($questaoData['alternativas'] as $alternativaData) {
                    QuizAnswer::create([
                        'question_id' => $question->id,
                        'texto' => $alternativaData['texto'],
                        'is_correct' => $alternativaData['is_correct'],
                        'ordem' => $alternativaData['ordem'],
                    ]);
                }
            }
        });
    }

    public function garantirQuizEstruturado(HelpContent $helpContent): void
    {
        if (! $this->quizTablesDisponiveis()) {
            return;
        }

        if ($helpContent->quizQuestions()->exists()) {
            return;
        }

        $payload = $helpContent->quiz_payload ?? [];
        if (empty($payload['pergunta'])) {
            return;
        }

        $opcoes = collect($payload['opcoes'] ?? [])->filter()->values();
        $respostaCorreta = trim((string) ($payload['resposta_correta'] ?? ''));

        if ($opcoes->count() < 2 || $respostaCorreta === '') {
            return;
        }

        $alternativas = $opcoes->map(function (string $texto, int $index) use ($respostaCorreta) {
            return [
                'texto' => $texto,
                'is_correct' => mb_strtolower(trim($texto)) === mb_strtolower($respostaCorreta),
                'ordem' => $index + 1,
            ];
        })->values();

        if ($alternativas->where('is_correct', true)->count() !== 1) {
            return;
        }

        $this->sincronizarQuizAula($helpContent, [[
            'pergunta' => (string) $payload['pergunta'],
            'alternativas' => $alternativas->all(),
        ]]);
    }

    public function obterQuestoes(HelpContent $helpContent): EloquentCollection
    {
        if (! $this->quizTablesDisponiveis()) {
            return new EloquentCollection();
        }

        $this->garantirQuizEstruturado($helpContent);

        return QuizQuestion::query()
            ->with(['answers' => fn ($query) => $query->orderBy('ordem')->orderBy('id')])
            ->where('help_content_id', $helpContent->id)
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();
    }

    public function aulaTemQuiz(HelpContent $helpContent): bool
    {
        if (! $this->quizTablesDisponiveis()) {
            return false;
        }

        $this->garantirQuizEstruturado($helpContent);

        return QuizQuestion::query()->where('help_content_id', $helpContent->id)->exists();
    }

    public function podeConcluirAula(Usuario $user, HelpContent $helpContent): bool
    {
        if (! $this->aulaTemQuiz($helpContent)) {
            return true;
        }

        return QuizAttempt::query()
            ->where('loja_id', $user->loja_id)
            ->where('user_id', $user->id)
            ->where('help_content_id', $helpContent->id)
            ->where('finalizada', true)
            ->exists();
    }

    public function obterEstadoAtual(Usuario $user, HelpContent $helpContent, bool $iniciarSeNecessario = true): array
    {
        $questoes = $this->obterQuestoes($helpContent);
        $hasQuiz = $questoes->isNotEmpty();

        if (! $hasQuiz) {
            return [
                'has_quiz' => false,
            ];
        }

        $tentativas = QuizAttempt::query()
            ->with('answers')
            ->where('loja_id', $user->loja_id)
            ->where('user_id', $user->id)
            ->where('help_content_id', $helpContent->id)
            ->orderBy('tentativa')
            ->get();

        $tentativaAtiva = $tentativas->first(fn (QuizAttempt $attempt) => ! $attempt->finalizada);
        $ultimaFinalizada = $tentativas->where('finalizada', true)->sortByDesc('tentativa')->first();

        if ($iniciarSeNecessario && ! $tentativaAtiva && $tentativas->isEmpty()) {
            $tentativaAtiva = $this->iniciarTentativa($user, $helpContent, 1);
            $tentativas = $tentativas->push($tentativaAtiva);
        }

        $podeRefazer = $tentativas->where('finalizada', true)->count() === 1 && ! $tentativas->contains(fn (QuizAttempt $attempt) => $attempt->tentativa === 2);

        $nextQuestion = $tentativaAtiva
            ? $this->proximaQuestaoNaoRespondida($questoes, $tentativaAtiva)
            : null;

        $respondidas = $tentativaAtiva ? $tentativaAtiva->answers->count() : 0;

        return [
            'has_quiz' => true,
            'questions_total' => $questoes->count(),
            'tentativa_ativa' => $tentativaAtiva,
            'ultima_finalizada' => $ultimaFinalizada,
            'pode_refazer' => $podeRefazer,
            'next_question' => $nextQuestion,
            'respondidas' => $respondidas,
            'percentual_progresso' => $questoes->count() > 0 ? (int) floor(($respondidas / $questoes->count()) * 100) : 0,
        ];
    }

    public function responderQuestao(Usuario $user, HelpContent $helpContent, int $questionId, int $answerId): array
    {
        $questoes = $this->obterQuestoes($helpContent);
        if ($questoes->isEmpty()) {
            throw ValidationException::withMessages(['quiz' => 'Esta aula não possui quiz configurado.']);
        }

        $estado = $this->obterEstadoAtual($user, $helpContent);
        /** @var QuizAttempt|null $tentativa */
        $tentativa = $estado['tentativa_ativa'];

        if (! $tentativa) {
            throw ValidationException::withMessages(['quiz' => 'Não existe tentativa ativa para esta aula.']);
        }

        $esperada = $this->proximaQuestaoNaoRespondida($questoes, $tentativa);
        if (! $esperada || (int) $esperada->id !== $questionId) {
            throw ValidationException::withMessages(['quiz' => 'Fluxo inválido. Responda as questões em sequência.']);
        }

        $answer = QuizAnswer::query()
            ->where('id', $answerId)
            ->where('question_id', $questionId)
            ->first();

        if (! $answer) {
            throw ValidationException::withMessages(['answer_id' => 'Alternativa inválida para a questão.']);
        }

        QuizAttemptAnswer::create([
            'loja_id' => $user->loja_id,
            'attempt_id' => $tentativa->id,
            'question_id' => $questionId,
            'answer_id' => $answerId,
            'correto' => $answer->is_correct,
            'respondido_em' => now(),
        ]);

        $tentativa->refresh();

        $proxima = $this->proximaQuestaoNaoRespondida($questoes, $tentativa);
        if ($proxima) {
            return [
                'finalizado' => false,
                'next_question_id' => $proxima->id,
            ];
        }

        $tentativaFinal = $this->finalizarTentativa($tentativa);

        return [
            'finalizado' => true,
            'attempt_id' => $tentativaFinal->id,
        ];
    }

    public function iniciarRefazer(Usuario $user, HelpContent $helpContent): QuizAttempt
    {
        $estado = $this->obterEstadoAtual($user, $helpContent);

        if (! ($estado['pode_refazer'] ?? false)) {
            throw ValidationException::withMessages([
                'quiz' => 'Você já utilizou sua tentativa de refazer o quiz.',
            ]);
        }

        $attempt = $this->iniciarTentativa($user, $helpContent, 2);

        $this->notificarGestores($user, $helpContent, [
            'titulo' => 'Colaborador iniciou refazer do quiz',
            'mensagem' => "{$user->nome} iniciou a segunda tentativa no quiz da aula {$helpContent->titulo}.",
            'tentativa' => 2,
        ]);

        return $attempt;
    }

    public function obterResultado(Usuario $user, HelpContent $helpContent, ?int $attemptId = null): array
    {
        $query = QuizAttempt::query()
            ->with(['answers.question', 'answers.answer'])
            ->where('loja_id', $user->loja_id)
            ->where('user_id', $user->id)
            ->where('help_content_id', $helpContent->id)
            ->where('finalizada', true)
            ->orderByDesc('tentativa');

        if ($attemptId) {
            $query->where('id', $attemptId);
        }

        $attempt = $query->first();

        if (! $attempt) {
            throw ValidationException::withMessages([
                'quiz' => 'Nenhuma tentativa finalizada encontrada.',
            ]);
        }

        $mostrarDetalhes = $attempt->tentativa >= 2;

        return [
            'attempt' => $attempt,
            'mostrar_detalhes' => $mostrarDetalhes,
            'detalhes' => $mostrarDetalhes
                ? $attempt->answers->map(function (QuizAttemptAnswer $answer): array {
                    return [
                        'question' => $answer->question,
                        'answer' => $answer->answer,
                        'correto' => $answer->correto,
                    ];
                })->values()
                : collect(),
        ];
    }

    public function historicoUsuario(Usuario $user, Collection $lessonIds): Collection
    {
        if (! $this->quizTablesDisponiveis()) {
            return collect();
        }

        if ($lessonIds->isEmpty()) {
            return collect();
        }

        return QuizAttempt::query()
            ->with('helpContent:id,titulo')
            ->where('loja_id', $user->loja_id)
            ->where('user_id', $user->id)
            ->whereIn('help_content_id', $lessonIds)
            ->where('finalizada', true)
            ->orderBy('help_content_id')
            ->orderBy('tentativa')
            ->get()
            ->groupBy('help_content_id')
            ->map(function (Collection $attempts): array {
                $primeira = $attempts->firstWhere('tentativa', 1);
                $segunda = $attempts->firstWhere('tentativa', 2);

                return [
                    'help_content_id' => $attempts->first()->help_content_id,
                    'aula' => $attempts->first()->helpContent,
                    'tentativa_1' => $primeira,
                    'tentativa_2' => $segunda,
                ];
            })
            ->values();
    }

    private function iniciarTentativa(Usuario $user, HelpContent $helpContent, int $tentativa): QuizAttempt
    {
        return QuizAttempt::create([
            'loja_id' => $user->loja_id,
            'user_id' => $user->id,
            'help_content_id' => $helpContent->id,
            'tentativa' => $tentativa,
            'finalizada' => false,
            'iniciada_em' => now(),
        ]);
    }

    private function proximaQuestaoNaoRespondida(EloquentCollection $questoes, QuizAttempt $attempt): ?QuizQuestion
    {
        $respondidas = $attempt->answers->pluck('question_id')->all();

        return $questoes->first(fn (QuizQuestion $question) => ! in_array($question->id, $respondidas, true));
    }

    private function finalizarTentativa(QuizAttempt $attempt): QuizAttempt
    {
        $attempt->load(['answers.answer', 'helpContent', 'user']);

        $total = $attempt->answers->count();
        $acertos = $attempt->answers->where('correto', true)->count();
        $erros = max(0, $total - $acertos);
        $percentualAcerto = $total > 0 ? (int) round(($acertos / $total) * 100) : 0;
        $percentualErro = max(0, 100 - $percentualAcerto);
        $duracao = $attempt->iniciada_em ? now()->diffInSeconds($attempt->iniciada_em) : null;

        $attempt->update([
            'nota' => $percentualAcerto,
            'percentual_acerto' => $percentualAcerto,
            'percentual_erro' => $percentualErro,
            'acertos' => $acertos,
            'erros' => $erros,
            'total_questoes' => $total,
            'finalizada' => true,
            'finalizada_em' => now(),
            'duracao_segundos' => $duracao,
        ]);

        if ($erros > 0) {
            $this->notificarGestores($attempt->user, $attempt->helpContent, [
                'titulo' => 'Colaborador errou questões no quiz',
                'mensagem' => "{$attempt->user->nome} concluiu o quiz da aula {$attempt->helpContent->titulo} com {$erros} erro(s).",
                'tentativa' => $attempt->tentativa,
                'percentual_acerto' => $percentualAcerto,
                'erros' => $erros,
            ]);
        }

        return $attempt->fresh(['answers.question', 'answers.answer']);
    }

    private function notificarGestores(Usuario $user, HelpContent $helpContent, array $extra): void
    {
        Usuario::query()
            ->where('loja_id', $user->loja_id)
            ->where('ativo', true)
            ->whereIn('perfil', ['administrador', 'gerente', 'rh'])
            ->get()
            ->each(function (Usuario $manager) use ($user, $helpContent, $extra): void {
                $manager->notify(new AcademyQuizManagerAlertNotification(array_merge([
                    'usuario_id' => $user->id,
                    'usuario_nome' => $user->nome,
                    'help_content_id' => $helpContent->id,
                    'help_content_titulo' => $helpContent->titulo,
                ], $extra)));
            });
    }

    private function quizTablesDisponiveis(): bool
    {
        return Schema::hasTable('quiz_questions')
            && Schema::hasTable('quiz_answers')
            && Schema::hasTable('quiz_attempts')
            && Schema::hasTable('quiz_attempt_answers');
    }
}
