<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:20
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lojas';

    protected $fillable = [
        'nome_fantasia',
        'slug',
        'responsavel_nome',
        'responsavel_email',
        'responsavel_whatsapp',
        'status',
        'plano_id',
        'trial_ends_at',
        'assinatura_ativa_ate',
        'storage_limit_mb',
        'storage_used_bytes',
        'subdominio',
        'dominio_personalizado',
        'observacoes_internas',
        'bloqueada_em',
        'motivo_bloqueio',
        'dias_carencia',
        'ultima_notificacao_em',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'assinatura_ativa_ate' => 'datetime',
        'storage_limit_mb' => 'integer',
        'storage_used_bytes' => 'integer',
        'bloqueada_em' => 'datetime',
        'ultima_notificacao_em' => 'datetime',
    ];

    /**
     * Relacionamento com o plano SaaS.
     */
    public function plano(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SaaS\Plano::class, 'plano_id');
    }

    /**
     * Usuários que pertencem a esta loja.
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'loja_id');
    }

    /**
     * Produtos da loja.
     */
    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class, 'loja_id');
    }

    /**
     * Pedidos da loja.
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'loja_id');
    }

    /**
     * Assinaturas da loja.
     */
    public function assinaturas(): HasMany
    {
        return $this->hasMany(\App\Models\SaaS\Assinatura::class, 'loja_id');
    }

    /**
     * Histórico de pagamentos SaaS.
     */
    public function pagamentosSaaS(): HasMany
    {
        return $this->hasMany(\App\Models\SaaS\PagamentoSaaS::class, 'loja_id');
    }

    /**
     * Snapshots de consumo.
     */
    public function consumoMetricas(): HasMany
    {
        return $this->hasMany(\App\Models\SaaS\ConsumoMetrica::class, 'loja_id');
    }

    /**
     * Notificações de inadimplência recebidas.
     */
    public function notificacoesInadimplencia(): HasMany
    {
        return $this->hasMany(\App\Models\SaaS\NotificacaoInadimplencia::class, 'loja_id');
    }

    /**
     * Verifica se a loja está bloqueada.
     */
    public function estaBloqueada(): bool
    {
        return $this->bloqueada_em !== null;
    }

    /**
     * Bloqueia a loja com um motivo.
     */
    public function bloquear(string $motivo = 'Inadimplência'): void
    {
        $this->update([
            'bloqueada_em' => now(),
            'motivo_bloqueio' => $motivo,
        ]);
    }

    /**
     * Desbloqueia a loja.
     */
    public function desbloquear(): void
    {
        $this->update([
            'bloqueada_em' => null,
            'motivo_bloqueio' => null,
        ]);
    }

    /**
     * Retorna a assinatura ativa atual da loja.
     * Autoria: Abimael Borges | https://abimaelborges.adv.br | 2026-04-15 14:38 BRT
     */
    public function assinaturaAtiva(): ?\App\Models\SaaS\Assinatura
    {
        return $this->assinaturas()
            ->whereIn('status', ['active', 'past_due', 'trial'])
            ->latest()
            ->first();
    }
}
