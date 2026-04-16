<?php

declare(strict_types=1);

namespace App\Services\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16 09:30
| Descrição: Singleton para gerenciar o contexto da Loja (Tenant) atual na requisição.
*/

use App\Models\Loja;

class TenantContext
{
    private ?Loja $loja = null;

    /**
     * Define a loja atual no contexto.
     */
    public function setLoja(Loja $loja): void
    {
        $this->loja = $loja;
    }

    /**
     * Retorna o objeto da loja atual.
     */
    public function getLoja(): ?Loja
    {
        return $this->loja;
    }

    /**
     * Retorna apenas o ID da loja atual.
     */
    public function getLojaId(): ?int
    {
        return $this->loja?->id;
    }

    /**
     * Verifica se existe uma loja no contexto.
     */
    public function hasTenant(): bool
    {
        return !is_null($this->loja);
    }
}
