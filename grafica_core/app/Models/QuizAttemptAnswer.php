<?php

namespace App\Models;

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttemptAnswer extends Model
{
    use HasTenancy;

    protected $fillable = [
        'loja_id',
        'attempt_id',
        'question_id',
        'answer_id',
        'correto',
        'respondido_em',
    ];

    protected function casts(): array
    {
        return [
            'correto' => 'boolean',
            'respondido_em' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(QuizAnswer::class, 'answer_id');
    }
}
