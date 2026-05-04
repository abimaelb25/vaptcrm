<?php

namespace App\Models;

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    use HasTenancy;

    protected $fillable = [
        'loja_id',
        'user_id',
        'help_content_id',
        'tentativa',
        'nota',
        'percentual_acerto',
        'percentual_erro',
        'acertos',
        'erros',
        'total_questoes',
        'finalizada',
        'iniciada_em',
        'finalizada_em',
        'duracao_segundos',
    ];

    protected function casts(): array
    {
        return [
            'tentativa' => 'integer',
            'nota' => 'integer',
            'percentual_acerto' => 'integer',
            'percentual_erro' => 'integer',
            'acertos' => 'integer',
            'erros' => 'integer',
            'total_questoes' => 'integer',
            'finalizada' => 'boolean',
            'iniciada_em' => 'datetime',
            'finalizada_em' => 'datetime',
            'duracao_segundos' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function helpContent(): BelongsTo
    {
        return $this->belongsTo(HelpContent::class, 'help_content_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class, 'attempt_id');
    }
}
