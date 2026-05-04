<?php

declare(strict_types=1);

namespace App\Services\SaaS;

class UsageTrackerService
{
    public function __construct(
        private readonly PlanService $planService,
    ) {}

    public function trackOrderCreated(int $lojaId, array $metadata = []): void
    {
        $this->safeRecord('pedido_criado', [
            'limit_key' => 'max_pedidos_mes',
            'delta' => 1,
            'used_total' => $this->planService->currentUsage('max_pedidos_mes', $lojaId),
            'metadata' => $metadata,
        ], $lojaId);
    }

    public function trackProductCreated(int $lojaId, array $metadata = []): void
    {
        $this->safeRecord('produto_criado', [
            'limit_key' => 'max_produtos',
            'delta' => 1,
            'used_total' => $this->planService->currentUsage('max_produtos', $lojaId),
            'metadata' => $metadata,
        ], $lojaId);
    }

    public function trackUserCreated(int $lojaId, array $metadata = []): void
    {
        $this->safeRecord('usuario_criado', [
            'limit_key' => 'max_usuarios',
            'delta' => 1,
            'used_total' => $this->planService->currentUsage('max_usuarios', $lojaId),
            'metadata' => $metadata,
        ], $lojaId);
    }

    public function trackProductionOrderCreated(int $lojaId, array $metadata = []): void
    {
        $this->safeRecord('op_gerada', [
            'limit_key' => 'max_ops_simultaneas',
            'delta' => 1,
            'used_total' => $this->planService->currentUsage('max_ops_simultaneas', $lojaId),
            'metadata' => $metadata,
        ], $lojaId);
    }

    public function trackUpload(int $lojaId, int $bytes, array $metadata = []): void
    {
        $mbDelta = (int) ceil(max(0, $bytes) / 1024 / 1024);

        $this->safeRecord('upload_realizado', [
            'limit_key' => 'max_storage_mb',
            'delta' => max(1, $mbDelta),
            'used_total' => $this->planService->currentUsage('max_storage_mb', $lojaId),
            'metadata' => array_merge($metadata, ['bytes' => max(0, $bytes)]),
        ], $lojaId);
    }

    public function trackApiConsumption(int $lojaId, string $endpoint, array $metadata = []): void
    {
        $this->safeRecord('api_consumo', [
            'feature_key' => 'modulo_api',
            'delta' => 1,
            'metadata' => array_merge($metadata, ['endpoint' => $endpoint]),
        ], $lojaId);
    }

    private function safeRecord(string $eventType, array $payload, int $lojaId): void
    {
        try {
            $this->planService->recordUsage($eventType, $payload, $lojaId);
        } catch (\Throwable) {
            // Tracking nao deve derrubar operacoes de negocio.
        }
    }
}
