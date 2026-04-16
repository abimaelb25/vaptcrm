<?php

declare(strict_types=1);

namespace App\Models\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:20
| Descrição: Rastreamento de notificações de inadimplência enviadas.
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Loja;

class NotificacaoInadimplencia extends Model
{
    protected $table = 'saas_notificacoes_inadimplencia';

    protected $fillable = [
        'loja_id',
        'assinatura_id',
        'tipo',
        'canal',
        'mensagem',
        'enviado_em',
        'entregue',
    ];

    protected $casts = [
        'enviado_em' => 'datetime',
        'entregue'   => 'boolean',
    ];

    /**
     * Relacionamento com a Loja
     */
    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    /**
     * Relacionamento com a Assinatura (opcional)
     */
    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(Assinatura::class);
    }

    /**
     * Scope: ordenar pelas notificações mais recentes
     */
    public function scopeRecentes($query)
    {
        return $query->orderBy('enviado_em', 'desc');
    }
}
