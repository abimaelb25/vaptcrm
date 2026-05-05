<?php

namespace App\Http\Controllers\SuperAdmin\Support;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
*/

use App\Http\Controllers\Controller;
use App\Models\AcademyTrack;
use App\Models\AcademyCourse;
use App\Models\HelpContent;
use App\Models\SaaS\Plano;
use App\Services\Support\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class HelpContentController extends Controller
{
    public function __construct(
        protected QuizService $quizService,
    ) {}

    public function index()
    {
        $contents = HelpContent::with('course.track')->orderBy('ordem')->get();
        return view('super-admin.support.help-contents.index', compact('contents'));
    }

    public function create()
    {
        $helpContent = new HelpContent();
        $tracks = AcademyTrack::query()->where('publicado', true)->orderBy('ordem')->orderBy('titulo')->get();
        $courses = AcademyCourse::query()->with('track')->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get();
        $plans = Plano::query()->operational()->commercialOrder()->get(['id', 'nome', 'slug']);
        $quizQuestionsDraft = [];

        return view('super-admin.support.help-contents.form', compact('helpContent', 'tracks', 'courses', 'plans', 'quizQuestionsDraft'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'track_id' => 'nullable|exists:academy_tracks,id',
            'course_id' => 'nullable|exists:academy_courses,id',
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|string|in:video,texto,pdf,imagem,quiz,treinamento,comunicado',
            'descricao' => 'nullable|string',
            'conteudo_texto' => 'nullable|string',
            'youtube_url' => 'nullable|url|required_if:tipo,video,treinamento',
            'thumbnail' => 'nullable|url',
            'material_apoio_titulo' => 'nullable|string|max:255',
            'material_apoio_url' => 'nullable|url',
            'quiz_payload' => 'nullable|array',
            'quiz_questions' => 'nullable|array',
            'quiz_questions.*.pergunta' => 'nullable|string',
            'quiz_questions.*.alternativas' => 'nullable|array',
            'quiz_questions.*.alternativas.*.texto' => 'nullable|string',
            'quiz_questions.*.alternativas.*.is_correct' => 'nullable|boolean',
            'ordem' => 'integer',
            'destaque' => 'boolean',
            'publicado' => 'boolean',
            'required_plan' => 'nullable|string|max:60',
            'visivel_para_planos' => 'nullable|array',
            'visivel_para_planos.*' => 'string|max:60',
            'obrigatoriedade' => 'nullable|string|in:livre,recomendado,obrigatorio',
        ]);

        $this->validarEstruturaAula($data);
        $this->validarOrdemDuplicada($data);

        $quizQuestions = $this->normalizarQuizQuestionsInput($data['quiz_questions'] ?? []);
        $data['quiz_payload'] = $this->normalizarQuizPayload($data['quiz_payload'] ?? null, $quizQuestions);
        $data['visivel_para_planos'] = $data['visivel_para_planos'] ?? null;
        $data['youtube_url'] = $this->normalizarYoutubeUrl($data);
        unset($data['track_id'], $data['quiz_questions']);

        $helpContent = HelpContent::create($data);
        if ($this->quizTablesDisponiveis()) {
            $this->quizService->sincronizarQuizAula($helpContent, $quizQuestions);
        }

        return redirect()->route('superadmin.support.central-de-ajuda.index')->with('success', 'Aula cadastrada com sucesso.');
    }

    public function edit(HelpContent $helpContent)
    {
        $tracks = AcademyTrack::query()->where('publicado', true)->orderBy('ordem')->orderBy('titulo')->get();
        $courses = AcademyCourse::query()->with('track')->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get();
        $plans = Plano::query()->operational()->commercialOrder()->get(['id', 'nome', 'slug']);
        $quizQuestionsDraft = $this->obterQuizQuestionsDraft($helpContent);

        return view('super-admin.support.help-contents.form', compact('helpContent', 'tracks', 'courses', 'plans', 'quizQuestionsDraft'));
    }

    public function update(Request $request, HelpContent $helpContent)
    {
        $data = $request->validate([
            'track_id' => 'nullable|exists:academy_tracks,id',
            'course_id' => 'nullable|exists:academy_courses,id',
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|string|in:video,texto,pdf,imagem,quiz,treinamento,comunicado',
            'descricao' => 'nullable|string',
            'conteudo_texto' => 'nullable|string',
            'youtube_url' => 'nullable|url|required_if:tipo,video,treinamento',
            'thumbnail' => 'nullable|url',
            'material_apoio_titulo' => 'nullable|string|max:255',
            'material_apoio_url' => 'nullable|url',
            'quiz_payload' => 'nullable|array',
            'quiz_questions' => 'nullable|array',
            'quiz_questions.*.pergunta' => 'nullable|string',
            'quiz_questions.*.alternativas' => 'nullable|array',
            'quiz_questions.*.alternativas.*.texto' => 'nullable|string',
            'quiz_questions.*.alternativas.*.is_correct' => 'nullable|boolean',
            'ordem' => 'integer',
            'destaque' => 'boolean',
            'publicado' => 'boolean',
            'required_plan' => 'nullable|string|max:60',
            'visivel_para_planos' => 'nullable|array',
            'visivel_para_planos.*' => 'string|max:60',
            'obrigatoriedade' => 'nullable|string|in:livre,recomendado,obrigatorio',
        ]);

        $this->validarEstruturaAula($data, $helpContent);
        $this->validarOrdemDuplicada($data, $helpContent);

        $quizQuestions = $this->normalizarQuizQuestionsInput($data['quiz_questions'] ?? []);
        $data['quiz_payload'] = $this->normalizarQuizPayload($data['quiz_payload'] ?? null, $quizQuestions);
        $data['visivel_para_planos'] = $data['visivel_para_planos'] ?? null;
        $data['youtube_url'] = $this->normalizarYoutubeUrl($data);
        unset($data['track_id'], $data['quiz_questions']);

        $helpContent->update($data);
        if ($this->quizTablesDisponiveis()) {
            $this->quizService->sincronizarQuizAula($helpContent, $quizQuestions);
        }

        return redirect()->route('superadmin.support.central-de-ajuda.index')->with('success', 'Aula atualizada com sucesso.');
    }

    public function destroy(HelpContent $helpContent)
    {
        $helpContent->delete();
        return redirect()->route('superadmin.support.central-de-ajuda.index')->with('success', 'Aula removida com sucesso.');
    }

    private function normalizarQuizPayload(?array $payload, array $quizQuestions = []): ?array
    {
        if (empty($payload['pergunta'])) {
            if (empty($quizQuestions)) {
                return null;
            }

            $primeiraQuestao = $quizQuestions[0];
            $opcoes = collect($primeiraQuestao['alternativas'] ?? [])
                ->pluck('texto')
                ->filter()
                ->values()
                ->all();

            $respostaCorreta = collect($primeiraQuestao['alternativas'] ?? [])
                ->firstWhere('is_correct', true)['texto'] ?? '';

            return [
                'pergunta' => trim((string) ($primeiraQuestao['pergunta'] ?? '')),
                'opcoes' => $opcoes,
                'resposta_correta' => (string) $respostaCorreta,
            ];
        }

        $opcoes = collect($payload['opcoes'] ?? [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        return [
            'pergunta' => trim((string) $payload['pergunta']),
            'opcoes' => $opcoes,
            'resposta_correta' => (string) ($payload['resposta_correta'] ?? ''),
        ];
    }

    private function normalizarQuizQuestionsInput(array $questions): array
    {
        return collect($questions)
            ->map(function ($question) {
                $alternativas = collect($question['alternativas'] ?? [])
                    ->map(fn ($alternativa) => [
                        'texto' => trim((string) ($alternativa['texto'] ?? '')),
                        'is_correct' => (bool) ($alternativa['is_correct'] ?? false),
                    ])
                    ->filter(fn (array $alternativa) => $alternativa['texto'] !== '')
                    ->values();

                return [
                    'pergunta' => trim((string) ($question['pergunta'] ?? '')),
                    'alternativas' => $alternativas->all(),
                ];
            })
            ->filter(fn (array $question) => $question['pergunta'] !== '')
            ->values()
            ->all();
    }

    private function obterQuizQuestionsDraft(HelpContent $helpContent): array
    {
        if ($this->quizTablesDisponiveis()) {
            $estruturado = $helpContent->quizQuestions()->with('answers')->orderBy('ordem')->orderBy('id')->get();
            if ($estruturado->isNotEmpty()) {
                return $estruturado->map(function ($question) {
                    return [
                        'pergunta' => $question->pergunta,
                        'alternativas' => $question->answers
                            ->sortBy('ordem')
                            ->values()
                            ->map(fn ($answer) => [
                                'texto' => $answer->texto,
                                'is_correct' => (bool) $answer->is_correct,
                            ])
                            ->all(),
                    ];
                })->all();
            }
        }

        if (! empty($helpContent->quiz_payload['pergunta'])) {
            $respostaCorreta = (string) ($helpContent->quiz_payload['resposta_correta'] ?? '');
            return [[
                'pergunta' => (string) $helpContent->quiz_payload['pergunta'],
                'alternativas' => collect($helpContent->quiz_payload['opcoes'] ?? [])
                    ->map(fn ($opcao) => [
                        'texto' => (string) $opcao,
                        'is_correct' => mb_strtolower(trim((string) $opcao)) === mb_strtolower(trim($respostaCorreta)),
                    ])
                    ->all(),
            ]];
        }

        return [];
    }

    private function quizTablesDisponiveis(): bool
    {
        return Schema::hasTable('quiz_questions') && Schema::hasTable('quiz_answers');
    }

    private function normalizarYoutubeUrl(array $data): ?string
    {
        return in_array($data['tipo'], [HelpContent::TIPO_VIDEO, HelpContent::TIPO_TREINAMENTO], true)
            ? ($data['youtube_url'] ?? null)
            : ($data['youtube_url'] ?? null);
    }

    private function validarEstruturaAula(array $data, ?HelpContent $helpContent = null): void
    {
        if (! empty($data['track_id']) && empty($data['course_id'])) {
            throw ValidationException::withMessages([
                'course_id' => 'Selecione uma etapa do treinamento depois de escolher a trilha.',
            ]);
        }

        if (! empty($data['track_id']) && ! empty($data['course_id'])) {
            $course = AcademyCourse::query()->find($data['course_id']);

            if (! $course || (int) $course->track_id !== (int) $data['track_id']) {
                throw ValidationException::withMessages([
                    'course_id' => 'A etapa escolhida não pertence à trilha selecionada.',
                ]);
            }
        }
    }

    private function validarOrdemDuplicada(array $data, ?HelpContent $helpContent = null): void
    {
        if (empty($data['course_id']) || ! isset($data['ordem'])) {
            return;
        }

        $query = HelpContent::query()
            ->where('course_id', $data['course_id'])
            ->where('ordem', (int) $data['ordem']);

        if ($helpContent) {
            $query->where('id', '!=', $helpContent->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'ordem' => 'Já existe outra aula nessa mesma etapa com essa ordem.',
            ]);
        }
    }
}
