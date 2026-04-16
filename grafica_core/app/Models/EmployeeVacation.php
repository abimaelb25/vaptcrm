<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:03
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenancy;

class EmployeeVacation extends Model
{
    use HasTenancy;

    protected $fillable = [
        'loja_id',
        'employee_id',
        'periodo_aquisitivo_inicio',
        'periodo_aquisitivo_fim',
        'periodo_concessivo_fim',
        'dias_direito',
        'dias_gozados',
        'dias_vendidos',
        'saldo_dias',
        'inicio_gozo',
        'fim_gozo',
        'status',
        'observacao',
    ];

    protected $casts = [
        'periodo_aquisitivo_inicio' => 'date',
        'periodo_aquisitivo_fim' => 'date',
        'periodo_concessivo_fim' => 'date',
        'inicio_gozo' => 'date',
        'fim_gozo' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
