<?php

namespace App\Services\Support;

use App\Models\HelpContent;
use App\Models\UserLessonProgress;
use App\Models\Usuario;
use Illuminate\Support\Collection;

class VaptAcademyService
{
    public function __construct(
        protected TrainingProgressService $trainingProgressService,
        protected QuizService $quizService,
    ) {}

    public function montarBiblioteca(Usuario $user): array
    {
        $queryBase = HelpContent::query()
            ->with(['course.track'])
            ->publicados()
            ->daBiblioteca()
            ->orderBy('ordem')
            ->orderBy('id');

        $lessons = (clone $queryBase)
            ->get();

        $lessons = $this->filtrarPorPlano($lessons, $user);

        $destaques = $lessons
            ->filter(fn (HelpContent $lesson) => $lesson->destaque)
            ->values();

        $estrutura = $this->trainingProgressService->montarResumoBiblioteca($user, $lessons);
        $alertasObrigatorios = $this->montarAlertasObrigatorios($lessons, $estrutura['progressMap']);

        return [
            'destaques' => $destaques,
            'bibliotecaTracks' => $estrutura['tracks'],
            'conteudosAvulsos' => $estrutura['avulsos'],
            'progressMap' => $estrutura['progressMap'],
            'resumoBiblioteca' => $estrutura['resumo'],
            'continuarTreinamento' => $estrutura['continuar'],
            'alertasObrigatorios' => $alertasObrigatorios,
        ];
    }

    public function obterDetalhe(Usuario $user, HelpContent $helpContent): array
    {
        if (!$helpContent->publicado || !in_array($helpContent->tipo, [
            HelpContent::TIPO_VIDEO,
            HelpContent::TIPO_TEXTO,
            HelpContent::TIPO_PDF,
            HelpContent::TIPO_IMAGEM,
            HelpContent::TIPO_QUIZ,
            HelpContent::TIPO_TREINAMENTO,
        ], true)) {
            abort(404);
        }

        $helpContent->load([
            'course.track.modulos.conteudos' => fn ($query) => $query->publicados()->daBiblioteca()->orderBy('ordem')->orderBy('id'),
            'course.conteudos' => fn ($query) => $query->publicados()->daBiblioteca()->orderBy('ordem')->orderBy('id'),
        ]);

        if (!$this->conteudoDisponivelParaUsuario($helpContent, $user)) {
            abort(404);
        }

        $allLessons = $this->filtrarPorPlano(
            HelpContent::query()
                ->with(['course.track', 'course.conteudos'])
                ->publicados()
                ->daBiblioteca()
                ->orderBy('ordem')
                ->orderBy('id')
                ->get(),
            $user
        );

        $detalhe = $this->trainingProgressService->montarDetalheUsuario($user, $allLessons, $helpContent);

        $sugeridos = $allLessons
            ->where('id', '!=', $helpContent->id)
            ->when($helpContent->course_id, fn (Collection $collection) => $collection->where('course_id', $helpContent->course_id))
            ->take(4)
            ->values();

        $progresso = UserLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('help_content_id', $helpContent->id)
            ->first();

        return [
            'progresso' => $progresso,
            'sugeridos' => $sugeridos,
            'sameModuleLessons' => $detalhe['sameModuleLessons'],
            'sameTrackModules' => $detalhe['sameTrackModules'],
            'trackProgressPercent' => $detalhe['trackProgressPercent'],
            'moduleProgressPercent' => $detalhe['moduleProgressPercent'],
            'resumoBiblioteca' => $detalhe['resumo'],
            'quizStatus' => $this->quizService->obterEstadoAtual($user, $helpContent, false),
        ];
    }

    public function registrarConclusao(Usuario $user, HelpContent $helpContent, int $percentual = 100): UserLessonProgress
    {
        $percentual = max(0, min(100, $percentual));

        return UserLessonProgress::updateOrCreate(
            [
                'loja_id' => $user->loja_id,
                'user_id' => $user->id,
                'help_content_id' => $helpContent->id,
            ],
            [
                'percentual_concluido' => $percentual,
                'iniciado_em' => now(),
                'concluido_em' => $percentual >= 100 ? now() : null,
            ]
        );
    }

    public function podeConcluirAula(Usuario $user, HelpContent $helpContent): bool
    {
        return $this->quizService->podeConcluirAula($user, $helpContent);
    }

    private function montarAlertasObrigatorios(Collection $lessons, Collection $progressMap): Collection
    {
        return $lessons
            ->filter(fn (HelpContent $lesson) => $lesson->isObrigatorio())
            ->filter(fn (HelpContent $lesson) => (int) ($progressMap[$lesson->id] ?? 0) < 100)
            ->values();
    }

    private function filtrarPorPlano(Collection $lessons, Usuario $user): Collection
    {
        return $lessons
            ->filter(fn (HelpContent $lesson) => $this->conteudoDisponivelParaUsuario($lesson, $user))
            ->values();
    }

    private function conteudoDisponivelParaUsuario(HelpContent $lesson, Usuario $user): bool
    {
        return $lesson->isDisponivelParaPlano($user->loja?->plano?->slug);
    }
}
