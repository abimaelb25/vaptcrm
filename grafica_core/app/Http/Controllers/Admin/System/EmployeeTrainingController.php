<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HelpContent;
use App\Models\Usuario;
use App\Services\Support\TrainingProgressService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;

class EmployeeTrainingController extends Controller
{
    public function __construct(
        protected TrainingProgressService $trainingProgressService,
    ) {}

    public function index(): View
    {
        $this->autorizarVisaoEquipe(auth()->user());

        $employees = Employee::query()
            ->with('usuario')
            ->whereNotNull('user_id')
            ->orderBy('nome_completo')
            ->get();

        $lessons = $this->carregarAulas();
        $rows = $this->trainingProgressService->resumoEquipe($employees, $lessons);
        $alerts = $this->trainingProgressService->montarAlertasEquipe($rows);

        return view('painel.funcionarios.treinamentos.index', [
            'rows' => $rows,
            'alerts' => $alerts,
            'summary' => [
                'colaboradores' => $rows->count(),
                'percentual_medio' => $rows->count() > 0 ? (int) round($rows->avg('progress_percent')) : 0,
                'trilhas_concluidas' => (int) $rows->sum('tracks_completed'),
            ],
        ]);
    }

    public function show(Employee $equipe): View
    {
        $usuario = auth()->user();
        $podeMonitorarEquipe = $this->podeMonitorarEquipe($usuario);
        $this->autorizarVisaoColaborador($usuario, $equipe);

        abort_if(! $equipe->usuario, 404);

        $lessons = $this->carregarAulas();
        $detail = $this->trainingProgressService->detalharColaborador($equipe->usuario, $lessons);

        return view('painel.funcionarios.treinamentos.show', [
            'funcionario' => $equipe->load('usuario'),
            'tracks' => $detail['tracks'],
            'summary' => $detail['summary'],
            'quizSummary' => $detail['quiz_summary'],
            'quizHistory' => $detail['quiz_history'],
            'podeMonitorarEquipe' => $podeMonitorarEquipe,
            'continuar' => $detail['continuar'],
            'status' => $detail['status'],
        ]);
    }

    private function autorizarVisaoEquipe(Usuario $usuario): void
    {
        if (! $this->podeMonitorarEquipe($usuario)) {
            throw new AuthorizationException('Somente gestor, dono da loja ou RH pode acompanhar treinamentos da equipe.');
        }
    }

    private function autorizarVisaoColaborador(Usuario $usuario, Employee $employee): void
    {
        if ($this->podeMonitorarEquipe($usuario)) {
            return;
        }

        if ($employee->user_id !== $usuario->id) {
            throw new AuthorizationException('Você só pode visualizar o próprio progresso de treinamento.');
        }
    }

    private function podeMonitorarEquipe(Usuario $usuario): bool
    {
        $perfil = strtolower((string) $usuario->perfil);
        if (in_array($perfil, ['administrador', 'gerente', 'rh'], true)) {
            return true;
        }

        foreach (['rh_ocorrencias_visualizar_todas', 'rh_ocorrencias_gerenciar', 'gerenciar_rh'] as $permission) {
            if ($usuario->temPermissao($permission)) {
                return true;
            }
        }

        return false;
    }

    private function carregarAulas()
    {
        $planSlug = auth()->user()?->loja?->plano?->slug;

        return HelpContent::query()
            ->with([
                'course.track',
                'course.conteudos' => fn ($query) => $query->where('publicado', true)->orderBy('ordem')->orderBy('id'),
                'course.track.modulos' => fn ($query) => $query->where('ativo', true)->orderBy('ordem')->orderBy('nome'),
                'course.track.modulos.conteudos' => fn ($query) => $query->where('publicado', true)->orderBy('ordem')->orderBy('id'),
            ])
            ->where('publicado', true)
            ->orderBy('ordem')
            ->orderBy('id')
            ->get()
            ->filter(fn (HelpContent $lesson) => $lesson->isDisponivelParaPlano($planSlug))
            ->values();
    }
}
