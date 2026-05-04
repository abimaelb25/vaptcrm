<?php

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HelpContent extends Model
{
    public const TIPO_VIDEO = 'video';
    public const TIPO_TEXTO = 'texto';
    public const TIPO_PDF = 'pdf';
    public const TIPO_IMAGEM = 'imagem';
    public const TIPO_QUIZ = 'quiz';
    public const TIPO_TREINAMENTO = 'treinamento';
    public const TIPO_COMUNICADO = 'comunicado';

    protected $fillable = [
        'course_id',
        'titulo',
        'tipo',
        'descricao',
        'youtube_url',
        'thumbnail',
        'conteudo_texto',
        'material_apoio_titulo',
        'material_apoio_url',
        'quiz_payload',
        'ordem',
        'destaque',
        'publicado',
        'required_plan',
        'visivel_para_planos',
        'obrigatoriedade',
    ];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
            'destaque' => 'boolean',
            'publicado' => 'boolean',
            'quiz_payload' => 'array',
            'visivel_para_planos' => 'array',
        ];
    }

    public function scopePublicados($query)
    {
        return $query->where('publicado', true);
    }

    public function scopeDaBiblioteca($query)
    {
        return $query->whereIn('tipo', [
            self::TIPO_VIDEO,
            self::TIPO_TEXTO,
            self::TIPO_PDF,
            self::TIPO_IMAGEM,
            self::TIPO_QUIZ,
            self::TIPO_TREINAMENTO,
        ]);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(AcademyCourse::class, 'course_id');
    }

    public function progressos(): HasMany
    {
        return $this->hasMany(UserLessonProgress::class, 'help_content_id');
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'help_content_id')->orderBy('ordem')->orderBy('id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'help_content_id')->orderByDesc('id');
    }

    public function getYoutubeVideoIdAttribute(): ?string
    {
        $url = trim((string) $this->youtube_url);
        if ($url === '') {
            return null;
        }

        if (str_contains($url, 'youtu.be/')) {
            return Str::before(Str::afterLast($url, '/'), '?');
        }

        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            parse_str($query, $params);

            return Arr::get($params, 'v');
        }

        return null;
    }

    public function getThumbnailResolvedAttribute(): ?string
    {
        if (! empty($this->thumbnail)) {
            return $this->thumbnail;
        }

        return $this->youtube_video_id
            ? 'https://img.youtube.com/vi/' . $this->youtube_video_id . '/hqdefault.jpg'
            : null;
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            self::TIPO_VIDEO => 'Vídeo',
            self::TIPO_TEXTO => 'Texto',
            self::TIPO_PDF => 'PDF / Apostila',
            self::TIPO_IMAGEM => 'Imagem',
            self::TIPO_QUIZ => 'Quiz',
            self::TIPO_TREINAMENTO => 'Treinamento',
            self::TIPO_COMUNICADO => 'Comunicado',
            default => Str::headline((string) $this->tipo),
        };
    }

    public function isObrigatorio(): bool
    {
        return $this->obrigatoriedade === 'obrigatorio'
            || $this->tipo === self::TIPO_TREINAMENTO
            || ! empty($this->required_plan);
    }

    public function isDisponivelParaPlano(?string $planSlug): bool
    {
        $normalizedPlan = strtolower(trim((string) $planSlug));
        $allowedPlans = collect($this->visivel_para_planos ?? [])
            ->map(fn ($item) => strtolower((string) $item))
            ->filter()
            ->values();

        if ($allowedPlans->contains(fn (string $item) => in_array($item, ['todos', 'all', '*'], true))) {
            return true;
        }

        if ($allowedPlans->isNotEmpty()) {
            return $normalizedPlan !== '' && $allowedPlans->contains($normalizedPlan);
        }

        if (! empty($this->required_plan)) {
            $legacyPlans = collect(explode(',', (string) $this->required_plan))
                ->map(fn ($item) => strtolower(trim((string) $item)))
                ->filter();

            if ($legacyPlans->contains(fn (string $item) => in_array($item, ['todos', 'all', '*'], true))) {
                return true;
            }

            return $normalizedPlan !== '' && $legacyPlans->contains($normalizedPlan);
        }

        return true;
    }
}
