<?php

declare(strict_types=1);

namespace App\Models\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:20
| Descrição: Snapshot mensal de consumo de recursos por loja.
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Loja;

class ConsumoMetrica extends Model
{
    protected $table = 'saas_consumo_metricas';

    protected $fillable = [
        'loja_id',
        'mes',
        'ano',
        'total_produtos',
        'total_usuarios',
        'total_pedidos',
        'storage_bytes',
        'limite_produtos',
        'limite_usuarios',
        'limite_storage_bytes',
    ];

    /**
     * Relacionamento com a Loja
     */
    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    /**
     * Scope: filtrar métricas do mês e ano específicos
     */
    public function scopeDoMes($query, int $mes, int $ano)
    {
        return $query->where('mes', $mes)->where('ano', $ano);
    }

    /**
     * Scope: filtrar métricas por ano
     */
    public function scopeDoAno($query, int $ano)
    {
        return $query->where('ano', $ano);
    }

    /**
     * Retorna a porcentagem de uso de produtos em relação ao limite (0 a 100).
     */
    public function porcentagemProdutos(): float
    {
        if (!$this->limite_produtos) {
            return 0.0;
        }

        return min(100, round(($this->total_produtos / $this->limite_produtos) * 100, 2));
    }

    /**
     * Retorna a porcentagem de uso de usuários em relação ao limite (0 a 100).
     */
    public function porcentagemUsuarios(): float
    {
        if (!$this->limite_usuarios) {
            return 0.0;
        }

        return min(100, round(($this->total_usuarios / $this->limite_usuarios) * 100, 2));
    }
}
