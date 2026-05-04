<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FornecedorProdutoMapeamento extends Model
{
    use HasTenancy;

    protected $table = 'fornecedor_produto_mapeamentos';

    protected $fillable = [
        'loja_id',
        'fornecedor_id',
        'codigo_fornecedor',
        'descricao_fornecedor',
        'insumo_id',
        'confianca',
    ];

    protected $casts = [
        'confianca' => 'integer',
    ];

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }
}
