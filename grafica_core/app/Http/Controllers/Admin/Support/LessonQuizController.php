<?php

namespace App\Http\Controllers\Admin\Support;

use App\Http\Controllers\Controller;
use App\Models\HelpContent;
use App\Services\Support\QuizService;
use App\Services\Support\VaptAcademyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LessonQuizController extends Controller
{
    public function __construct(
        protected QuizService $quizService,
        protected VaptAcademyService $academyService,
    ) {}

    public function show(Request $request, HelpContent $helpContent): View|RedirectResponse
    {
        $user = auth()->user();
        $this->autorizarAula($user, $helpContent);

        $estado = $this->quizService->obterEstadoAtual($user, $helpContent);

        if (! ($estado['has_quiz'] ?? false)) {
            return redirect()
                ->route('admin.support.help.show', $helpContent)
                ->with('erro', 'Esta aula não possui quiz configurado.');
        }

        if (! $estado['tentativa_ativa']) {
            return redirect()->route('admin.support.help.quiz.result', $helpContent);
        }

        return view('painel.support.help-center.quiz', [
            'helpContent' => $helpContent,
            'estadoQuiz' => $estado,
            'question' => $estado['next_question'],
        ]);
    }

    public function answer(Request $request, HelpContent $helpContent): RedirectResponse
    {
        $user = auth()->user();
        $this->autorizarAula($user, $helpContent);

        $data = $request->validate([
            'question_id' => 'required|integer',
            'answer_id' => 'required|integer',
        ]);

        try {
            $resultado = $this->quizService->responderQuestao($user, $helpContent, (int) $data['question_id'], (int) $data['answer_id']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        if ($resultado['finalizado'] ?? false) {
            return redirect()->route('admin.support.help.quiz.result', $helpContent, ['attempt' => $resultado['attempt_id']]);
        }

        return redirect()->route('admin.support.help.quiz.show', $helpContent);
    }

    public function result(Request $request, HelpContent $helpContent): View|RedirectResponse
    {
        $user = auth()->user();
        $this->autorizarAula($user, $helpContent);

        try {
            $resultado = $this->quizService->obterResultado($user, $helpContent, $request->integer('attempt'));
            $estado = $this->quizService->obterEstadoAtual($user, $helpContent);
        } catch (ValidationException $exception) {
            return redirect()->route('admin.support.help.quiz.show', $helpContent)->withErrors($exception->errors());
        }

        return view('painel.support.help-center.quiz-result', [
            'helpContent' => $helpContent,
            'resultadoQuiz' => $resultado,
            'estadoQuiz' => $estado,
        ]);
    }

    public function retry(HelpContent $helpContent): RedirectResponse
    {
        $user = auth()->user();
        $this->autorizarAula($user, $helpContent);

        try {
            $this->quizService->iniciarRefazer($user, $helpContent);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.support.help.quiz.show', $helpContent)
            ->with('success', 'Segunda tentativa iniciada.');
    }

    private function autorizarAula($user, HelpContent $helpContent): void
    {
        $detalhe = $this->academyService->obterDetalhe($user, $helpContent);
        unset($detalhe);
    }
}
