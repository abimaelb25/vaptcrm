<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:05
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class FinancialCategory extends Model
{
    use SoftDeletes, HasTenancy;

    protected $table = 'financial_categories';

    protected $fillable = [
        'loja_id',
        'nome',
        'tipo',
        'cor',
    ];

    public function titulos(): HasMany
    {
        return $this->hasMany(FinancialTitle::class, 'categoria_id');
    }
}
