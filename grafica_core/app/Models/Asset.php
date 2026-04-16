<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:40
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;
use Carbon\Carbon;

class Asset extends Model
{
    use SoftDeletes, HasTenancy;

    protected $fillable = [
        'loja_id',
        'nome',
        'tipo',
        'marca',
        'modelo',
        'numero_serie',
        'setor',
        'data_aquisicao',
        'valor_aquisicao',
        'vida_util_meses',
        'valor_residual',
        'status',
        'observacao',
    ];

    protected $casts = [
        'data_aquisicao' => 'date',
        'valor_aquisicao' => 'float',
        'valor_residual' => 'float',
        'vida_util_meses' => 'integer',
    ];

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    /**
     * Calcula a depreciação acumulada (Linear).
     */
    public function getDepreciacaoAcumuladaAttribute(): float
    {
        $mesesUso = $this->data_aquisicao->diffInMonths(now());
        if ($mesesUso <= 0) return 0;
        
        $mesesParaCalcular = min($mesesUso, $this->vida_util_meses);
        $depreciacaoMensal = ($this->valor_aquisicao - $this->valor_residual) / $this->vida_util_meses;
        
        return round($mesesParaCalcular * $depreciacaoMensal, 2);
    }

    /**
     * Calcula o valor contábil atual do ativo.
     */
    public function getValorAtualAttribute(): float
    {
        return round($this->valor_aquisicao - $this->depreciacao_acumulada, 2);
    }
}
