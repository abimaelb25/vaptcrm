<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:02
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenancy;

class EmployeeDocument extends Model
{
    use HasTenancy;

    protected $fillable = [
        'loja_id',
        'employee_id',
        'tipo_documento',
        'titulo',
        'arquivo_path',
        'mime_type',
        'tamanho_bytes',
        'observacao',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
