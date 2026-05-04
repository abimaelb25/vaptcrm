<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Model de log de envio de e-mails para auditoria multi-tenant.
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\HasTenancy;

class EmailLog extends Model
{
    use HasTenancy;

    protected $fillable = [
        'loja_id',
        'tipo',
        'destinatario_email',
        'destinatario_nome',
        'assunto',
        'status',
        'referencia_type',
        'referencia_id',
        'erro',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function referencia(): MorphTo
    {
        return $this->morphTo('referencia');
    }
}
