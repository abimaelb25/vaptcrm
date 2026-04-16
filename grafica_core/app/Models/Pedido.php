<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-05 00:16 -03:00
*/

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Authorable, \App\Traits\HasTenancy;

    protected $table = 'pedidos';

    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_AGUARDANDO = 'aguardando_aprovacao';
    public const STATUS_APROVADO = 'aprovado';
    public const STATUS_EM_PRODUCAO = 'em_producao';
    public const STATUS_PRONTO = 'pronto';
    public const STATUS_EM_TRANSPORTE = 'em_transporte';
    public const STATUS_ENTREGUE = 'entregue';
    public const STATUS_CANCELADO = 'cancelado';
    public const STATUS_AGUARDANDO_PAGAMENTO = 'aguardando_pagamento';
    
    public const ORIGEM_PDV = 'pdv';

    protected $fillable = [
        'loja_id',
        'numero',
        'numero_acompanhamento',
        'origem',
        'tipo_atendimento',
        'cliente_id',
        'status',
        'subtotal',
        'total',
        'valor_recebido',
        'troco',
        'tipo_total',
        'tipo_entrega',
        'valor_frete',
        'taxas_adicionais',
        'desconto',
        'forma_pagamento',
        'gateway_pagamento',
        'prazo_entrega',
        'responsavel_id',
        'atendente_id',
        'cupom_id',
        'valor_desconto_cupom',
        'observacoes',
        'observacoes_internas',
        'observacoes_cliente',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'subtotal'             => 'decimal:2',
        'total'                => 'decimal:2',
        'valor_frete'          => 'decimal:2',
        'taxas_adicionais'     => 'decimal:2',
        'desconto'             => 'decimal:2',
        'valor_desconto_cupom' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemPedido::class, 'pedido_id');
    }

    public function historico(): HasMany
    {
        return $this->hasMany(HistoricoPedido::class, 'pedido_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class, 'pedido_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsavel_id');
    }

    public function atendente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'atendente_id');
    }

    public function cupom(): BelongsTo
    {
        return $this->belongsTo(Cupom::class, 'cupom_id');
    }
}
