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

class Assinatura extends Model
{
    use \App\Traits\HasTenancy;

    protected $table = 'saas_assinaturas';

    protected $fillable = [
        'loja_id',
        'plano_id',
        'status',
        'stripe_subscription_id',
        'stripe_customer_id',
        'trial_ends_at',
        'ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Relacionamento com o plano atual desta assinatura.
     */
    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    /**
     * Verifica se o sistema está em período de testes.
     */
    public function emTrial(): bool
    {
        return $this->status === 'trial' && ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }

    /**
     * Verifica se a assinatura está ativa (paga ou trial).
     */
    public function ativa(): bool
    {
        if ($this->emTrial()) {
            return true;
        }

        return in_array($this->status, ['active', 'past_due'], true);
    }
    
    /**
     * Verifica se a assinatura está vencida ou cancelada.
     */
    public function expirada(): bool
    {
        return ! $this->ativa();
    }
}
