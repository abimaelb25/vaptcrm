<?php

declare(strict_types=1);

namespace App\Models\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:20
| Descrição: Model de Histórico de cobranças SaaS por assinatura.
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Loja;

class PagamentoSaaS extends Model
{
    protected $table = 'saas_pagamentos';

    protected $fillable = [
        'loja_id',
        'assinatura_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'valor',
        'moeda',
        'status',
        'periodo_inicio',
        'periodo_fim',
        'pago_em',
        'vencimento_em',
        'motivo_falha',
        'tentativas',
    ];

    protected $casts = [
        'valor'          => 'decimal:2',
        'periodo_inicio' => 'date',
        'periodo_fim'    => 'date',
        'pago_em'        => 'datetime',
        'vencimento_em'  => 'datetime',
    ];

    /**
     * Relacionamento com a Loja
     */
    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    /**
     * Relacionamento com a Assinatura correspondente
     */
    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(Assinatura::class);
    }

    /**
     * Scope: apenas pagamentos concluidos (pago)
     */
    public function scopePagos($query)
    {
        return $query->where('status', 'pago');
    }

    /**
     * Scope: apenas pagamentos que falharam
     */
    public function scopeFalhados($query)
    {
        return $query->where('status', 'falhou');
    }

    /**
     * Scope: filtrar pagamentos no periodo (por vencimento)
     */
    public function scopeNoPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('vencimento_em', [$dataInicio, $dataFim]);
    }
}
