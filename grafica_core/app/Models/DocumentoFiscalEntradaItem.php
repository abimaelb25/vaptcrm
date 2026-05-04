<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoFiscalEntradaItem extends Model
{
    use HasTenancy;

    protected $table = 'documentos_fiscais_entrada_itens';

    protected $fillable = [
        'loja_id',
        'documento_id',
        'insumo_id',
        'codigo_fornecedor',
        'descricao',
        'ncm',
        'cfop',
        'unidade',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'impostos_json',
        'acao_definida',
        'tipo_item_operacional',
        'tratamento_financeiro',
        'valor_financeiro_alocado',
        'confirmacao_desconsideracao',
    ];

    protected $casts = [
        'quantidade' => 'float',
        'valor_unitario' => 'float',
        'valor_total' => 'float',
        'valor_financeiro_alocado' => 'float',
        'confirmacao_desconsideracao' => 'boolean',
        'impostos_json' => 'array',
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(DocumentoFiscalEntrada::class, 'documento_id');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}
