<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenancy;

class ProdutoEtapaProducao extends Model
{
    use HasTenancy;

    protected $table = 'produto_etapas_producao';

    protected $fillable = [
        'loja_id',
        'produto_id',
        'production_step_id',
        'ordem',
        'tempo_estimado_minutos',
        'obrigatorio',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'tempo_estimado_minutos' => 'integer',
        'obrigatorio' => 'boolean',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function etapa(): BelongsTo
    {
        return $this->belongsTo(ProductionStep::class, 'production_step_id');
    }
}
