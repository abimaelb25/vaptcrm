<?php

namespace App\Http\Controllers\Admin\Support;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
*/

use App\Http\Controllers\Controller;
use App\Models\HelpContent;
use App\Services\Support\VaptAcademyService;
use Illuminate\Http\RedirectResponse;

class HelpCenterController extends Controller
{
    public function __construct(
        protected VaptAcademyService $academyService,
    ) {}

    public function index()
    {
        $dados = $this->academyService->montarBiblioteca(auth()->user());

        return view('painel.support.help-center.index', $dados);
    }

    public function show(HelpContent $helpContent)
    {
        $dados = $this->academyService->obterDetalhe(auth()->user(), $helpContent);

        return view('painel.support.help-center.show', [
            'helpContent' => $helpContent,
            'sugeridos' => $dados['sugeridos'],
            'progresso' => $dados['progresso'],
            'quizStatus' => $dados['quizStatus'],
            'sameModuleLessons' => $dados['sameModuleLessons'],
            'sameTrackModules' => $dados['sameTrackModules'],
            'trackProgressPercent' => $dados['trackProgressPercent'],
            'moduleProgressPercent' => $dados['moduleProgressPercent'],
            'resumoBiblioteca' => $dados['resumoBiblioteca'],
        ]);
    }

    public function concluir(HelpContent $helpContent): RedirectResponse
    {
        if (! $this->academyService->podeConcluirAula(auth()->user(), $helpContent)) {
            return back()->with('erro', 'Finalize o quiz desta aula antes de marcar a conclusão.');
        }

        $this->academyService->registrarConclusao(auth()->user(), $helpContent, 100);

        return back()->with('success', 'Treinamento marcado como concluído.');
    }
}
