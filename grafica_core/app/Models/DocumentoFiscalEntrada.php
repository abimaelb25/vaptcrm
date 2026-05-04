<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentoFiscalEntrada extends Model
{
    use HasTenancy;

    protected $table = 'documentos_fiscais_entrada';

    protected $fillable = [
        'loja_id',
        'fornecedor_id',
        'chave_nfe',
        'numero',
        'serie',
        'data_emissao',
        'valor_total',
        'xml_path',
        'status_importacao',
        'usuario_responsavel_id',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'valor_total' => 'float',
    ];

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(DocumentoFiscalEntradaItem::class, 'documento_id');
    }

    public function usuarioResponsavel(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_responsavel_id');
    }
}
