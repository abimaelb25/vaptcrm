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

    /**
     * Boot do modelo para validações.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Impedir alteração do codigo_loja se já existirem pedidos
        static::updating(function (Loja $loja): void {
            if ($loja->isDirty('codigo_loja') && $loja->getOriginal('codigo_loja') !== null) {
                // Verificar se existem pedidos com este código
                $temPedidos = Pedido::withoutGlobalScope('loja')
                    ->where('loja_id', $loja->id)
                    ->exists();

                if ($temPedidos) {
                    throw new \RuntimeException(
                        'Não é possível alterar o código da loja após existirem pedidos. ' .
                        'O código atual é: ' . $loja->getOriginal('codigo_loja')
                    );
                }
            }
        });
    }

    protected $fillable = [
        'nome_fantasia',
        'slug',
        'codigo_loja',
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
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_address',
        'smtp_from_name',
        'observacoes_internas',
        'bloqueada_em',
        'motivo_bloqueio',
        'dias_carencia',
        'limites_desbloqueados_ate',
        'ultima_notificacao_em',
    ];

    protected $hidden = [
        'smtp_password',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'assinatura_ativa_ate' => 'datetime',
        'storage_limit_mb' => 'integer',
        'storage_used_bytes' => 'integer',
        'limites_desbloqueados_ate' => 'datetime',
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
     * Verifica se a loja possui limites desbloqueados manualmente pelo suporte.
     */
    public function limitesDesbloqueados(): bool
    {
        return $this->limites_desbloqueados_ate !== null && $this->limites_desbloqueados_ate->isFuture();
    }

    /**
     * Bloqueia a loja com um motivo.
     * Invalida todos os caches relacionados para garantir consistência imediata.
     */
    public function bloquear(string $motivo = 'Inadimplência'): void
    {
        $this->update([
            'bloqueada_em' => now(),
            'motivo_bloqueio' => $motivo,
        ]);

        $this->invalidarTodosOsCaches();
    }

    /**
     * Desbloqueia a loja.
     * Invalida todos os caches relacionados para garantir consistência imediata.
     */
    public function desbloquear(): void
    {
        $this->update([
            'bloqueada_em' => null,
            'motivo_bloqueio' => null,
        ]);

        $this->invalidarTodosOsCaches();
    }

    /**
     * Invalida todos os caches relacionados a esta loja.
     * Deve ser chamado sempre que dados críticos forem alterados pelo Super-Admin.
     * 
     * Caches invalidados:
     * - saas_assinatura_ativa_loja_{id} → SaaSService
     * - tenant_id_host_{host} → TenantDiscoveryMiddleware (subdomínio e domínio personalizado)
     * 
     * Autoria: Abimael Borges | https://abimaelborges.adv.br | 2026-04-18
     */
    public function invalidarTodosOsCaches(): void
    {
        $cache = \Illuminate\Support\Facades\Cache::getFacadeRoot();

        // 1. Cache de assinatura SaaS
        $cache->forget("saas_assinatura_ativa_loja_{$this->id}");

        // 2. Cache de tenant por subdomínio
        if ($this->subdominio) {
            $baseHost = parse_url(config('app.url'), PHP_URL_HOST);
            $host = "{$this->subdominio}.{$baseHost}";
            $cache->forget("tenant_id_host_{$host}");
        }

        // 3. Cache de tenant por domínio personalizado
        if ($this->dominio_personalizado) {
            $cache->forget("tenant_id_host_{$this->dominio_personalizado}");
        }

        // 4. Cache de branding/configurações
        $cache->forget("site_configs_{$this->id}");
        $cache->forget("paginas_legais_{$this->id}");
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

    /**
     * Verifica se a loja possui configuração SMTP própria válida.
     */
    public function possuiSmtpProprio(): bool
    {
        return !empty($this->smtp_host)
            && !empty($this->smtp_port)
            && !empty($this->smtp_from_address);
    }

    /**
     * Retorna array de configuração SMTP para uso com mailer dinâmico.
     */
    public function getSmtpConfig(): array
    {
        return [
            'transport'  => 'smtp',
            'host'       => $this->smtp_host,
            'port'       => $this->smtp_port,
            'username'   => $this->smtp_username,
            'password'   => $this->smtp_password,
            'encryption' => $this->smtp_encryption ?? 'tls',
        ];
    }

    /**
     * Retorna o e-mail remetente da loja (SMTP próprio ou responsável).
     */
    public function getFromAddress(): string
    {
        return $this->smtp_from_address
            ?? $this->responsavel_email
            ?? config('mail.from.address');
    }

    /**
     * Retorna o nome remetente da loja.
     */
    public function getFromName(): string
    {
        return $this->smtp_from_name
            ?? $this->nome_fantasia
            ?? config('mail.from.name');
    }
}
