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
use App\Traits\HasTenancy;

class IntegracaoPagamento extends Model
{
    use HasFactory, HasTenancy;

    public const GATEWAY_MERCADO_PAGO = 'mercado_pago';
    public const GATEWAY_STRIPE = 'stripe';
    public const GATEWAY_PAYPAL = 'paypal';
    public const GATEWAY_ASAAS = 'asaas';
    public const GATEWAY_PIX_MANUAL = 'pix_manual';
    public const GATEWAY_OUTRO = 'outro';

    protected $table = 'integracoes_pagamento';

    protected $fillable = [
        'loja_id',
        'user_id',
        'gateway',
        'ativo',
        'ambiente',
        'credenciais',
        'config_json',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'credenciais' => 'encrypted:array',
        'config_json' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
