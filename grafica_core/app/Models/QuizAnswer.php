<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    protected $fillable = [
        'question_id',
        'texto',
        'is_correct',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'ordem' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
