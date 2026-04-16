<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:05
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenancy;

class EmployeeHistory extends Model
{
    use HasTenancy;

    protected $table = 'employee_history';

    protected $fillable = [
        'loja_id',
        'employee_id',
        'tipo_evento',
        'titulo',
        'descricao',
        'data_evento',
        'criado_por',
    ];

    protected $casts = [
        'data_evento' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'criado_por');
    }
}
