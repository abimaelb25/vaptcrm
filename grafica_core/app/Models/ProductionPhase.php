<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-20
| Descrição: Fase de produção - agrupamento macro de etapas (ex: Pré-produção, Impressão, Acabamento).
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class ProductionPhase extends Model
{
    use SoftDeletes, HasTenancy;

    protected $fillable = [
        'loja_id',
        'nome',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'ativo' => 'boolean',
    ];

    /**
     * Loja proprietária da fase.
     */
    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class);
    }

    /**
     * Etapas vinculadas a esta fase.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ProductionStep::class)->orderBy('ordem');
    }

    /**
     * Alias para steps() - nomenclatura em português.
     */
    public function etapas(): HasMany
    {
        return $this->steps();
    }
}
