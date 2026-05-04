<?php

namespace App\Services\Support;

use App\Models\Employee;
use App\Models\HelpContent;
use App\Models\QuizAttempt;
use App\Models\UserLessonProgress;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TrainingProgressService
{
    public function classificarStatus(int $percentual, $lastActivityAt = null): array
    {
        if ($percentual >= 100) {
            return [
                'label' => 'Concluído',
                'tone' => 'emerald',
            ];
        }

        if ($percentual > 70) {
            return [
                'label' => 'Avançado',
                'tone' => 'blue',
            ];
        }

        if ($percentual > 0) {
            return [
                'label' => 'Em andamento',
                'tone' => 'amber',
            ];
        }

        return [
            'label' => 'Não iniciado',
            'tone' => 'slate',
        ];
    }

    public function mapaProgressoUsuario(Usuario $user): Collection
    {
        return UserLessonProgress::query()
            ->where('user_id', $user->id)
            ->pluck('percentual_concluido', 'help_content_id');
    }

    public function montarResumoBiblioteca(Usuario $user, EloquentCollection $lessons): array
    {
        $progressMap = $this->mapaProgressoUsuario($user);
        $tracks = $this->agruparPorTrilha($lessons, $progressMap);
        $avulsos = $this->agruparAvulsos($lessons, $progressMap);
        $resumo = $this->resumoDoMapa($lessons, $progressMap);
        $continuar = $this->resolverContinuacao($lessons, $progressMap);

        return [
            'tracks' => $tracks,
            'avulsos' => $avulsos,
            'progressMap' => $progressMap,
            'resumo' => $resumo,
            'continuar' => $continuar,
        ];
    }

    public function montarDetalheUsuario(Usuario $user, EloquentCollection $allLessons, HelpContent $currentLesson): array
    {
        $progressMap = $this->mapaProgressoUsuario($user);
        $track = $currentLesson->course?->track;
        $sameModuleLessons = $currentLesson->course
            ? $currentLesson->course->conteudos->sortBy('ordem')->values()
            : collect();

        $sameTrackModules = $track
            ? $track->modulos->map(function ($module) use ($progressMap) {
                $lessons = $module->conteudos->sortBy('ordem')->values();
                return [
                    'module' => $module,
                    'lessons' => $this->enriquecerAulas($lessons, $progressMap),
                    'progress_percent' => $this->calcularMedia($lessons, $progressMap),
                ];
            })->values()
            : collect();

        return [
            'progressMap' => $progressMap,
            'sameModuleLessons' => $this->enriquecerAulas($sameModuleLessons, $progressMap),
            'sameTrackModules' => $sameTrackModules,
            'trackProgressPercent' => $track ? $this->calcularMedia($track->modulos->flatMap->conteudos, $progressMap) : 0,
            'moduleProgressPercent' => $this->calcularMedia($sameModuleLessons, $progressMap),
            'resumo' => $this->resumoDoMapa($allLessons, $progressMap),
        ];
    }

    public function resumoEquipe(EloquentCollection $employees, EloquentCollection $lessons): Collection
    {
        $userIds = $employees->pluck('user_id')->filter()->values();
        if ($userIds->isEmpty()) {
            return collect();
        }

        $progressRows = UserLessonProgress::query()
            ->whereIn('user_id', $userIds)
            ->get(['user_id', 'help_content_id', 'percentual_concluido', 'updated_at'])
            ->groupBy('user_id');

        $quizRows = collect();

        if ($this->quizTablesDisponiveis()) {
            $quizRows = QuizAttempt::query()
                ->whereIn('user_id', $userIds)
                ->where('finalizada', true)
                ->get(['user_id', 'help_content_id', 'tentativa', 'percentual_acerto', 'acertos', 'erros', 'finalizada_em'])
                ->groupBy('user_id');
        }

        return $employees
            ->filter(fn (Employee $employee) => $employee->user_id !== null && $employee->usuario)
            ->map(function (Employee $employee) use ($progressRows, $quizRows, $lessons) {
                $rowsByUser = $progressRows->get($employee->user_id, collect());
                $quizByUser = $quizRows->get($employee->user_id, collect());
                $progressMap = $rowsByUser->pluck('percentual_concluido', 'help_content_id');
                $quizStats = $this->calcularDesempenhoQuiz($quizByUser);

                $resumo = $this->resumoDoMapa($lessons, $progressMap);
                $lastActivity = $rowsByUser->sortByDesc('updated_at')->first();
                $status = $this->classificarStatus($resumo['percentual_total'], $lastActivity?->updated_at);

                return [
                    'employee' => $employee,
                    'user' => $employee->usuario,
                    'progress_percent' => $resumo['percentual_total'],
                    'tracks_completed' => $this->totalTrilhasConcluidas($lessons, $progressMap),
                    'completed_lessons' => $resumo['aulas_concluidas'],
                    'total_lessons' => $resumo['aulas_total'],
                    'last_activity_at' => $lastActivity?->updated_at,
                    'status_label' => $status['label'],
                    'status_tone' => $status['tone'],
                    'quiz_score_percent' => $quizStats['score_percent'],
                    'quiz_total_avaliacoes' => $quizStats['avaliacoes'],
                    'quiz_low_count' => $quizStats['baixo_desempenho'],
                    'quiz_retry_count' => $quizStats['refeitas'],
                ];
            })
            ->sortByDesc('progress_percent')
            ->values();
    }

    public function montarAlertasEquipe(Collection $rows): array
    {
        $naoIniciaram = $rows->filter(fn (array $row) => $row['progress_percent'] === 0)->values();
        $baixoProgresso = $rows->filter(fn (array $row) => $row['progress_percent'] > 0 && $row['progress_percent'] < 30)->values();
        $parados = $rows->filter(function (array $row) {
            if (! $row['last_activity_at']) {
                return false;
            }

            return now()->diffInDays($row['last_activity_at']) >= 7 && $row['progress_percent'] < 100;
        })->values();
        $quizBaixo = $rows->filter(fn (array $row) => $row['quiz_score_percent'] !== null && $row['quiz_score_percent'] < 70)->values();
        $quizErrosAltos = $rows->filter(fn (array $row) => ($row['quiz_low_count'] ?? 0) > 0)->values();
        $quizRefizeram = $rows->filter(fn (array $row) => ($row['quiz_retry_count'] ?? 0) > 0)->values();

        return [
            [
                'title' => 'Pessoas sem início',
                'count' => $naoIniciaram->count(),
                'tone' => 'slate',
                'message' => $naoIniciaram->count() > 0
                    ? 'Há colaboradores que ainda não começaram nenhum treinamento.'
                    : 'Todos já começaram pelo menos uma etapa.',
                'action' => $naoIniciaram->count() > 0 ? 'Vale cobrar o início ainda esta semana.' : 'Nenhuma ação urgente aqui.',
                'employees' => $naoIniciaram->take(4),
            ],
            [
                'title' => 'Progresso baixo',
                'count' => $baixoProgresso->count(),
                'tone' => 'amber',
                'message' => $baixoProgresso->count() > 0
                    ? 'Parte do time avançou menos de 30%.'
                    : 'Não há ninguém travado no início do treinamento.',
                'action' => $baixoProgresso->count() > 0 ? 'Sugestão: alinhar prioridade e reservar um bloco curto de estudo.' : 'A equipe passou da fase inicial.',
                'employees' => $baixoProgresso->take(4),
            ],
            [
                'title' => 'Sem atividade recente',
                'count' => $parados->count(),
                'tone' => 'rose',
                'message' => $parados->count() > 0
                    ? 'Alguns colaboradores pararam de avançar nos últimos dias.'
                    : 'Não há sinais de estagnação recente.',
                'action' => $parados->count() > 0 ? 'Sugestão: retomar com a próxima etapa já definida.' : 'Fluxo saudável no momento.',
                'employees' => $parados->take(4),
            ],
            [
                'title' => 'Nota baixa no quiz',
                'count' => $quizBaixo->count(),
                'tone' => 'amber',
                'message' => $quizBaixo->count() > 0
                    ? 'Existem colaboradores com desempenho abaixo de 70% em quiz.'
                    : 'Não há nota baixa registrada em quiz.',
                'action' => $quizBaixo->count() > 0 ? 'Sugestão: revisar conteúdos críticos com esse grupo.' : 'Indicador está saudável.',
                'employees' => $quizBaixo->take(4),
            ],
            [
                'title' => 'Muitos erros em questões',
                'count' => $quizErrosAltos->count(),
                'tone' => 'rose',
                'message' => $quizErrosAltos->count() > 0
                    ? 'Parte do time errou várias questões no quiz finalizado.'
                    : 'Não há sinais de erro alto em quizzes finalizados.',
                'action' => $quizErrosAltos->count() > 0 ? 'Sugestão: reforçar as aulas com maior taxa de erro.' : 'Sem ação urgente no momento.',
                'employees' => $quizErrosAltos->take(4),
            ],
            [
                'title' => 'Colaboradores que refizeram',
                'count' => $quizRefizeram->count(),
                'tone' => 'slate',
                'message' => $quizRefizeram->count() > 0
                    ? 'Há colaboradores que precisaram da segunda tentativa.'
                    : 'Nenhum colaborador precisou refazer quiz.',
                'action' => $quizRefizeram->count() > 0 ? 'Sugestão: observar os temas mais sensíveis e orientar revisão.' : 'Fluxo de primeira tentativa está estável.',
                'employees' => $quizRefizeram->take(4),
            ],
        ];
    }

    public function detalharColaborador(Usuario $user, EloquentCollection $lessons): array
    {
        $progressMap = $this->mapaProgressoUsuario($user);
        $tracks = $this->agruparPorTrilha($lessons, $progressMap);
        $continuar = $this->resolverContinuacao($lessons, $progressMap);
        $status = $this->classificarStatus($this->calcularMedia($lessons, $progressMap));
        $lessonIds = $lessons->pluck('id')->values();
        $quizAttempts = collect();

        if ($this->quizTablesDisponiveis()) {
            $quizAttempts = QuizAttempt::query()
                ->with('helpContent:id,titulo')
                ->where('loja_id', $user->loja_id)
                ->where('user_id', $user->id)
                ->whereIn('help_content_id', $lessonIds)
                ->where('finalizada', true)
                ->orderBy('help_content_id')
                ->orderBy('tentativa')
                ->get();
        }
        $quizResumo = $this->calcularDesempenhoQuiz($quizAttempts);

        return [
            'tracks' => $tracks,
            'summary' => $this->resumoDoMapa($lessons, $progressMap),
            'progressMap' => $progressMap,
            'continuar' => $continuar,
            'status' => $status,
            'quiz_summary' => $quizResumo,
            'quiz_history' => $quizAttempts
                ->groupBy('help_content_id')
                ->map(function (Collection $attempts): array {
                    return [
                        'help_content_id' => $attempts->first()->help_content_id,
                        'aula' => $attempts->first()->helpContent,
                        'tentativa_1' => $attempts->firstWhere('tentativa', 1),
                        'tentativa_2' => $attempts->firstWhere('tentativa', 2),
                    ];
                })
                ->values(),
        ];
    }

    private function agruparPorTrilha(EloquentCollection $lessons, Collection $progressMap): Collection
    {
        return $lessons
            ->filter(fn (HelpContent $lesson) => $lesson->course?->track)
            ->groupBy(fn (HelpContent $lesson) => $lesson->course->track->id)
            ->map(function (Collection $trackLessons) use ($progressMap) {
                $track = $trackLessons->first()->course->track;
                $modules = $trackLessons
                    ->groupBy(fn (HelpContent $lesson) => $lesson->course?->id)
                    ->map(function (Collection $moduleLessons) use ($progressMap) {
                        $module = $moduleLessons->first()->course;
                        $nextLesson = $moduleLessons
                            ->sortBy('ordem')
                            ->first(fn (HelpContent $lesson) => (int) ($progressMap[$lesson->id] ?? 0) < 100);

                        return [
                            'module' => $module,
                            'lessons' => $this->enriquecerAulas($moduleLessons->sortBy('ordem')->values(), $progressMap),
                            'progress_percent' => $this->calcularMedia($moduleLessons, $progressMap),
                            'completed_lessons' => $this->totalConcluidas($moduleLessons, $progressMap),
                            'total_lessons' => $moduleLessons->count(),
                            'next_lesson' => $nextLesson,
                        ];
                    })
                    ->sortBy(fn (array $item) => $item['module']->ordem)
                    ->values();

                return [
                    'track' => $track,
                    'modules' => $modules,
                    'progress_percent' => $this->calcularMedia($trackLessons, $progressMap),
                    'completed_lessons' => $this->totalConcluidas($trackLessons, $progressMap),
                    'total_lessons' => $trackLessons->count(),
                ];
            })
            ->sortBy(fn (array $item) => $item['track']->ordem)
            ->values();
    }

    private function agruparAvulsos(EloquentCollection $lessons, Collection $progressMap): Collection
    {
        return $this->enriquecerAulas(
            $lessons->filter(fn (HelpContent $lesson) => ! $lesson->course?->track)->values(),
            $progressMap
        );
    }

    private function enriquecerAulas(Collection $lessons, Collection $progressMap): Collection
    {
        $nextLessonId = $lessons
            ->first(fn (HelpContent $lesson) => (int) ($progressMap[$lesson->id] ?? 0) < 100)
            ?->id;

        return $lessons->map(function (HelpContent $lesson) use ($progressMap, $nextLessonId) {
            return [
                'lesson' => $lesson,
                'progress_percent' => (int) ($progressMap[$lesson->id] ?? 0),
                'is_completed' => (int) ($progressMap[$lesson->id] ?? 0) >= 100,
                'is_next' => $nextLessonId === $lesson->id,
            ];
        })->values();
    }

    private function resolverContinuacao(iterable $lessons, Collection $progressMap): ?array
    {
        $lessonCollection = $lessons instanceof Collection ? $lessons : collect($lessons);
        $candidate = $lessonCollection
            ->first(fn (HelpContent $lesson) => (int) ($progressMap[$lesson->id] ?? 0) > 0 && (int) ($progressMap[$lesson->id] ?? 0) < 100)
            ?? $lessonCollection->first(fn (HelpContent $lesson) => (int) ($progressMap[$lesson->id] ?? 0) < 100);

        if (! $candidate) {
            return null;
        }

        return [
            'lesson' => $candidate,
            'progress_percent' => (int) ($progressMap[$candidate->id] ?? 0),
        ];
    }

    private function resumoDoMapa(iterable $lessons, Collection $progressMap): array
    {
        $lessonCollection = $lessons instanceof Collection ? $lessons : collect($lessons);
        $total = $lessonCollection->count();
        $completed = $this->totalConcluidas($lessonCollection, $progressMap);

        return [
            'aulas_total' => $total,
            'aulas_concluidas' => $completed,
            'percentual_total' => $this->calcularMedia($lessonCollection, $progressMap),
        ];
    }

    private function totalConcluidas(iterable $lessons, Collection $progressMap): int
    {
        return collect($lessons)
            ->filter(fn (HelpContent $lesson) => (int) ($progressMap[$lesson->id] ?? 0) >= 100)
            ->count();
    }

    private function totalTrilhasConcluidas(EloquentCollection $lessons, Collection $progressMap): int
    {
        return $lessons
            ->filter(fn (HelpContent $lesson) => $lesson->course?->track)
            ->groupBy(fn (HelpContent $lesson) => $lesson->course->track->id)
            ->filter(fn (Collection $trackLessons) => $this->calcularMedia($trackLessons, $progressMap) >= 100)
            ->count();
    }

    private function calcularMedia(iterable $lessons, Collection $progressMap): int
    {
        $lessonCollection = $lessons instanceof Collection ? $lessons : collect($lessons);
        $total = $lessonCollection->count();
        if ($total === 0) {
            return 0;
        }

        $sum = $lessonCollection->sum(fn (HelpContent $lesson) => (int) ($progressMap[$lesson->id] ?? 0));

        return (int) round($sum / $total);
    }

    private function calcularDesempenhoQuiz(Collection $attempts): array
    {
        if ($attempts->isEmpty()) {
            return [
                'score_percent' => null,
                'avaliacoes' => 0,
                'baixo_desempenho' => 0,
                'refeitas' => 0,
            ];
        }

        $latestPerLesson = $attempts
            ->sortByDesc('tentativa')
            ->groupBy('help_content_id')
            ->map(fn (Collection $rows) => $rows->sortByDesc('tentativa')->first())
            ->values();

        $score = (int) round($latestPerLesson->avg('percentual_acerto'));

        return [
            'score_percent' => $score,
            'avaliacoes' => $latestPerLesson->count(),
            'baixo_desempenho' => $latestPerLesson->where('percentual_acerto', '<', 70)->count(),
            'refeitas' => $attempts->where('tentativa', 2)->count(),
        ];
    }

    private function quizTablesDisponiveis(): bool
    {
        return Schema::hasTable('quiz_attempts');
    }
}
