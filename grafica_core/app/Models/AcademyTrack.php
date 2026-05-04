<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademyTrack extends Model
{
    protected $fillable = [
        'titulo',
        'slug',
        'descricao',
        'ordem',
        'destaque',
        'publicado',
    ];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
            'destaque' => 'boolean',
            'publicado' => 'boolean',
        ];
    }

    public function modulos(): HasMany
    {
        return $this->hasMany(AcademyCourse::class, 'track_id')
            ->orderBy('ordem')
            ->orderBy('nome');
    }
}
