<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 03:50 (adição: HasTenancy)
*/

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\HasTenancy;

class MovimentacaoFinanceira extends Model
{
    use HasFactory, SoftDeletes, HasTenancy;

    protected $table = 'movimentacoes_financeiras';

    const TIPO_ENTRADA = 'entrada';
    const TIPO_SAIDA = 'saida';

    const STATUS_PAGO = 'pago';
    const STATUS_PENDENTE = 'pendente';
    const STATUS_CANCELADO = 'cancelado';

    protected $fillable = [
        'loja_id',
        'uuid',
        'tipo',
        'categoria',
        'valor',
        'data_movimentacao',
        'forma_pagamento',
        'status',
        'pedido_id',
        'pagamento_id',
        'caixa_id',
        'usuario_id',
        'descricao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_movimentacao' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (MovimentacaoFinanceira $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /* 
    | RELACIONAMENTOS 
    */

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function pagamento(): BelongsTo
    {
        return $this->belongsTo(Pagamento::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /* 
    | SCOPES 
    */

    public function scopeEntradas($query)
    {
        return $query->where('tipo', self::TIPO_ENTRADA);
    }

    public function scopeSaidas($query)
    {
        return $query->where('tipo', self::TIPO_SAIDA);
    }

    public function scopePagos($query)
    {
        return $query->where('status', self::STATUS_PAGO);
    }

    public function scopeNoPeriodo($query, $inicio, $fim)
    {
        return $query->whereBetween('data_movimentacao', [$inicio, $fim]);
    }
}
