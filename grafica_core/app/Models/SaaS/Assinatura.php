<?php

declare(strict_types=1);

namespace App\Models\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:20
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assinatura extends Model
{
    use \App\Traits\HasTenancy;

    public const STATUS_TRIAL = 'trial';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SUSPENSA = 'suspended';
    public const STATUS_CANCELADA = 'canceled';
    public const STATUS_SUSPENDED = 'suspended';

    public const BILLING_MONTHLY = 'monthly';
    public const BILLING_YEARLY = 'yearly';

    protected $table = 'saas_assinaturas';

    protected $fillable = [
        'loja_id',
        'plano_id',
        'plan_version',
        'plan_snapshot',
        'status',
        'billing_cycle',
        'stripe_subscription_id',
        'stripe_customer_id',
        'gateway_provider',
        'gateway_subscription_id',
        'gateway_customer_id',
        'gateway_status',
        'financial_status',
        'trial_ends_at',
        'grace_ends_at',
        'ends_at',
        'renews_at',
        'next_billing_at',
        'last_payment_at',
        'canceled_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'renews_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'canceled_at' => 'datetime',
        'plan_snapshot' => 'array',
    ];

    /**
     * Relacionamento com o plano atual desta assinatura.
     */
    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(PagamentoSaaS::class, 'assinatura_id');
    }

    /**
     * Verifica se o sistema está em período de testes.
     */
    public function emTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL
            && ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }

    public function emGracePeriod(): bool
    {
        if (! $this->grace_ends_at) {
            return false;
        }

        return $this->grace_ends_at->isFuture();
    }

    /**
     * Verifica se a assinatura está ativa (paga ou trial).
     */
    public function ativa(): bool
    {
        if ($this->emTrial()) {
            return true;
        }

        if ($this->emGracePeriod()) {
            return true;
        }

        if (in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PAST_DUE], true)) {
            if ($this->ends_at && $this->ends_at->isPast()) {
                return false;
            }

            return true;
        }

        return false;
    }
    
    /**
     * Verifica se a assinatura está vencida ou cancelada.
     */
    public function expirada(): bool
    {
        return ! $this->ativa();
    }

    public function suspensa(): bool
    {
        return in_array($this->status, [self::STATUS_SUSPENSA, self::STATUS_SUSPENDED], true);
    }

    public function isReadOnlyLocked(): bool
    {
        return in_array($this->status, [self::STATUS_PAST_DUE, self::STATUS_CANCELADA, self::STATUS_SUSPENSA, self::STATUS_SUSPENDED, self::STATUS_EXPIRED], true);
    }
}
