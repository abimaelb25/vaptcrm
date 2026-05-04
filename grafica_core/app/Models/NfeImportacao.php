<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NfeImportacao extends Model
{
    use HasTenancy;

    protected $table = 'nfe_importacoes';

    protected $fillable = [
        'loja_id',
        'usuario_id',
        'fornecedor_id',
        'documento_fiscal_entrada_id',
        'chave_nfe',
        'numero',
        'serie',
        'data_emissao',
        'valor_total',
        'xml_path',
        'status',
        'payload_json',
        'alertas_json',
        'confirmada_em',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'valor_total' => 'float',
        'payload_json' => 'array',
        'alertas_json' => 'array',
        'confirmada_em' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function documentoFiscalEntrada(): BelongsTo
    {
        return $this->belongsTo(DocumentoFiscalEntrada::class, 'documento_fiscal_entrada_id');
    }
}
