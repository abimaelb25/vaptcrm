<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-07 22:32 -03:00
*/

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pagamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagamentos';

    protected $fillable = [
        'pedido_id',
        'gateway',
        'metodo',
        'valor',
        'status',
        'tipo_cobranca',
        'codigo_pix',
        'qr_code',
        'transaction_id',
        'assinatura_gateway',
        'payload_original',
        // Campos Stripe
        'stripe_session_id',
        'stripe_payment_intent_id',
        'stripe_checkout_url',
        'stripe_expires_at',
    ];

    protected $casts = [
        'payload_original'  => 'array',
        'valor'             => 'decimal:2',
        'stripe_expires_at' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /** Verifica se a sessão Stripe ainda está válida */
    public function stripeSessionAtiva(): bool
    {
        return $this->stripe_session_id !== null
            && $this->status === 'pendente'
            && ($this->stripe_expires_at === null || $this->stripe_expires_at->isFuture());
    }
}

