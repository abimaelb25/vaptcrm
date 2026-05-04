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
            // Evita recursão e erros em console/migrations
            if (app()->runningInConsole()) {
                return;
            }

            // SEGURANÇA: Usamos Auth::hasUser() para verificar se o usuário já foi carregado.
            // Se o usuário já estiver na memória, validamos se é Super Admin para liberar acesso total.
            if (Auth::hasUser() && Auth::user()->isSuperAdmin()) {
                return;
            }

            $lojaId = null;

            // 1. Contexto de Usuário Autenticado
            // Usamos Auth::id() que é mais leve e Auth::user() apenas se já estiver resolvido
            if (Auth::hasUser()) {
                $lojaId = Auth::user()->loja_id;
            } 
            // 2. Fallback para Tenant Context (Catálogo/Landing)
            else if (app()->bound(\App\Services\SaaS\TenantContext::class)) {
                $lojaId = app(\App\Services\SaaS\TenantContext::class)->getLojaId();
            }

            // Aplica o filtro apenas se houver um tenant identificado
            // Para a Landing Page da plataforma, lojaId será null e o filtro não é aplicado.
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
