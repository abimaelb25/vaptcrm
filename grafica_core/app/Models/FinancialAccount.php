<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:10
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class FinancialAccount extends Model
{
    use SoftDeletes, HasTenancy;

    protected $table = 'financial_accounts';

    protected $fillable = [
        'loja_id',
        'nome',
        'tipo',
        'saldo_inicial',
        'ativo',
    ];

    protected $casts = [
        'saldo_inicial' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function pagamentos(): HasMany
    {
        return $this->hasMany(FinancialPayment::class);
    }
}
