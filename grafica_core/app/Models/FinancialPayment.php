<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:20
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenancy;

class FinancialPayment extends Model
{
    use SoftDeletes, HasTenancy;

    protected $table = 'financial_payments';

    protected $fillable = [
        'loja_id',
        'financial_title_id',
        'financial_account_id',
        'valor',
        'forma_pagamento',
        'data_pagamento',
        'comprovante_path',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_pagamento' => 'date',
    ];

    public function titulo(): BelongsTo
    {
        return $this->belongsTo(FinancialTitle::class, 'financial_title_id');
    }

    public function conta(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }

    protected static function booted(): void
    {
        static::saved(function (FinancialPayment $payment) {
            $payment->titulo->atualizarSaldos();
        });

        static::deleted(function (FinancialPayment $payment) {
            $payment->titulo->atualizarSaldos();
        });
    }
}
