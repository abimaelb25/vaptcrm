<?php

namespace App\Models;

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLessonProgress extends Model
{
    use HasTenancy;

    protected $table = 'user_lesson_progress';

    protected $fillable = [
        'loja_id',
        'user_id',
        'help_content_id',
        'percentual_concluido',
        'iniciado_em',
        'concluido_em',
    ];

    protected function casts(): array
    {
        return [
            'percentual_concluido' => 'integer',
            'iniciado_em' => 'datetime',
            'concluido_em' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function conteudo(): BelongsTo
    {
        return $this->belongsTo(HelpContent::class, 'help_content_id');
    }

    public function scopeConcluidos($query)
    {
        return $query->where('percentual_concluido', '>=', 100);
    }
}
