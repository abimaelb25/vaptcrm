<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:15
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class FinancialTitle extends Model
{
    use SoftDeletes, HasTenancy;

    protected $table = 'financial_titles';

    protected $fillable = [
        'loja_id',
        'tipo',
        'origem',
        'referencia_id',
        'descricao',
        'categoria_id',
        'valor_total',
        'valor_pago',
        'saldo_restante',
        'data_emissao',
        'data_vencimento',
        'data_pagamento',
        'status',
        'observacao',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'saldo_restante' => 'decimal:2',
        'data_emissao' => 'date',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class, 'categoria_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(FinancialPayment::class);
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'referencia_id')->where('origem', 'pedido');
    }

    /**
     * Atualiza os totais e status do título baseado nos pagamentos.
     */
    public function atualizarSaldos(): void
    {
        $pago = $this->pagamentos()->sum('valor');
        $this->valor_pago = $pago;
        $this->saldo_restante = $this->valor_total - $pago;

        if ($this->saldo_restante <= 0) {
            $this->status = 'pago';
            $this->data_pagamento = $this->pagamentos()->max('data_pagamento');
        } elseif ($this->valor_pago > 0) {
            $this->status = 'parcial';
        } else {
            $this->status = now()->isAfter($this->data_vencimento) ? 'vencido' : 'aberto';
        }

        $this->save();
        
        // Se for pedido, podemos avisar o pedido aqui ou via Service
    }
}
