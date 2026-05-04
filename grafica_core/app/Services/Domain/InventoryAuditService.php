<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\EstoqueMovimentacao;
use App\Services\AuditLogService;

class InventoryAuditService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
    ) {}

    public function logMovement(EstoqueMovimentacao $movement, array $oldValues = [], array $newValues = []): void
    {
        $this->auditLog->log(
            'estoque_movimentacoes',
            'movimentacao_' . $movement->tipo,
            $movement->id,
            $oldValues,
            $newValues
        );
    }

    public function logAdjustment(EstoqueMovimentacao $movement, array $oldValues = [], array $newValues = []): void
    {
        $this->auditLog->log(
            'estoque_movimentacoes',
            'ajuste_saldo',
            $movement->id,
            $oldValues,
            $newValues
        );
    }
}
