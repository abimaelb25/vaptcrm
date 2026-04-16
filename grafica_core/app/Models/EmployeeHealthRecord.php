<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:04
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenancy;

class EmployeeHealthRecord extends Model
{
    use HasTenancy;

    protected $fillable = [
        'loja_id',
        'employee_id',
        'tipo_registro',
        'data_registro',
        'validade_ate',
        'observacao',
        'arquivo_path',
    ];

    protected $casts = [
        'data_registro' => 'date',
        'validade_ate' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
