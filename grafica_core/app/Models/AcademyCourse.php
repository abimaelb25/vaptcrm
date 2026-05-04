<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademyCourse extends Model
{
    protected $fillable = [
        'track_id',
        'nome',
        'slug',
        'descricao',
        'ordem',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'track_id' => 'integer',
            'ordem' => 'integer',
            'ativo' => 'boolean',
        ];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(AcademyTrack::class, 'track_id');
    }

    public function conteudos(): HasMany
    {
        return $this->hasMany(HelpContent::class, 'course_id')->orderBy('ordem');
    }
}
