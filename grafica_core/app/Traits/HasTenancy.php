<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 00:10
| Descrição: Trait para automação de multitenancy em modelos Eloquent.
*/

namespace App\Traits;

use App\Models\Loja;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasTenancy
{
    /**
     * Inicializa a trait, aplicando escopos globais e mutações automáticas.
     */
    public static function bootHasTenancy(): void
    {
        // Define loja_id automaticamente ao criar registros
        static::creating(function ($model) {
            if (empty($model->loja_id)) {
                if (Auth::check()) {
                    $model->loja_id = Auth::user()->loja_id;
                } else {
                    $model->loja_id = app(\App\Services\SaaS\TenantContext::class)->getLojaId();
                }
            }
        });

        // Aplica filtro global de loja em todas as consultas
        static::addGlobalScope('loja', function (Builder $builder) {
            // Evita recursão e erros em console/migrations ou se for super_admin
            if (app()->runningInConsole()) {
                return;
            }

            // SEGURANÇA: Super Admins podem ver TUDO para suporte e gestão da plataforma.
            // Usamos Auth::check() para forçar a resolução do usuário durante o Route Model Binding.
            if (Auth::check() && Auth::user()->isSuperAdmin()) {
                return;
            }

            $lojaId = null;

            // 1. Contexto de Usuário Autenticado (Painel Admin)
            if (Auth::check() && Auth::user()->loja_id) {
                $lojaId = Auth::user()->loja_id;
            } 
            // 2. Contexto de Tenant Descoberto (Catálogo Público)
            else if (app()->bound(\App\Services\SaaS\TenantContext::class)) {
                $lojaId = app(\App\Services\SaaS\TenantContext::class)->getLojaId();
            }

            if ($lojaId) {
                $builder->where($builder->getModel()->getTable() . '.loja_id', $lojaId);
            }
        });
    }

    /**
     * Relacionamento com a Loja proprietária do registro.
     */
    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }
}
