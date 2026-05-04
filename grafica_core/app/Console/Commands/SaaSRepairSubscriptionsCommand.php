<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Loja;
use App\Models\SaaS\Assinatura;
use App\Services\SaaS\SubscriptionSyncService;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Comando de reparo: sincronizar assinaturas SaaS para lojas órfãs.
 * 
 * Uso: php artisan saas:repair-subscriptions
 * 
 * Funciona:
 * - Localiza lojas com plano_id mas sem assinatura
 * - Cria assinatura automaticamente para cada uma
 * - Mostra relatório detalhado no terminal
 * - Pode rodar múltiplas vezes com segurança (idempotente)
 */
final class SaaSRepairSubscriptionsCommand extends Command
{
    protected $signature = 'saas:repair-subscriptions
        {--dry-run : Simula a execução sem fazer alterações}
        {--loja-id= : Reparar apenas uma loja específica}
        {--repair-billing : Corrige billing_cycle/next_billing_at inválidos}
        {--force : Executa sem confirmação interativa}';

    protected $description = 'Sincronizar/criar assinaturas SaaS faltantes para lojas órfãs';

    public function __construct(
        private SubscriptionSyncService $syncService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔍 Procurando lojas sem assinatura SaaS...');

        // Encontrar lojas órfãs
        $query = Loja::whereNotNull('plano_id')
            ->whereNotIn('id', function ($q) {
                $q->select('loja_id')->from('saas_assinaturas');
            });

        // Se --loja-id especificado, filtrar
        if ($this->option('loja-id')) {
            $query->where('id', $this->option('loja-id'));
        }

        $lojas = $query->get();

        if ($lojas->isEmpty()) {
            $this->info('✅ Nenhuma loja órfã encontrada.');

            if ($this->option('repair-billing')) {
                if ($this->option('dry-run')) {
                    $this->line(sprintf('Billing anomalies detectadas: %d', $this->countBillingAnomalies()));

                    return self::SUCCESS;
                }

                $fixed = $this->repairBillingAnomalies();
                $remaining = $this->countBillingAnomalies();
                $this->line(sprintf('Billing anomalies corrigidas: %d', $fixed));

                return $remaining === 0 ? self::SUCCESS : self::FAILURE;
            }

            $this->info('Sistema está íntegro!');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn(sprintf('⚠️  Encontradas %d loja(s) sem assinatura:', $lojas->count()));
        $this->newLine();

        // Mostrar detalhes das lojas órfãs
        foreach ($lojas as $loja) {
            $this->line(sprintf(
                '  • Loja ID %d: %s (Plano: %d, Status: %s)',
                $loja->id,
                $loja->nome_fantasia,
                $loja->plano_id,
                $loja->status
            ));
        }

        $this->newLine();

        // Se dry-run, informar e sair
        if ($this->option('dry-run')) {
            $this->info('Modo DRY-RUN: Nenhuma alteração foi feita.');

            if ($this->option('repair-billing')) {
                $billingAnomalies = $this->countBillingAnomalies();
                $this->line(sprintf('Billing anomalies detectadas: %d', $billingAnomalies));
            }

            return self::SUCCESS;
        }

        // Confirmar ação
        if (! $this->option('force') && ! $this->confirm(sprintf('Deseja criar assinatura(s) para estas %d loja(s)?', $lojas->count()))) {
            $this->info('Operação cancelada.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('🔧 Sincronizando assinaturas...');
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;

        // Processar cada loja
        foreach ($lojas as $loja) {
            try {
                $assinatura = $this->syncService->syncSubscriptionForStore($loja);
                
                $this->line(sprintf(
                    '  ✅ Loja %d: Assinatura criada (ID: %d, Plano: %s)',
                    $loja->id,
                    $assinatura->id,
                    $assinatura->plano->nome
                ));
                
                $successCount++;
            } catch (RuntimeException $e) {
                $this->error(sprintf(
                    '  ❌ Loja %d: %s',
                    $loja->id,
                    $e->getMessage()
                ));
                $errorCount++;
            } catch (\Exception $e) {
                $this->error(sprintf(
                    '  ❌ Loja %d: Erro inesperado: %s',
                    $loja->id,
                    $e->getMessage()
                ));
                $errorCount++;
            }
        }

        $this->newLine();

        // Resumo
        $this->info('📊 Relatório Final:');
        $this->line(sprintf('  ✅ Sucesso: %d', $successCount));
        $this->line(sprintf('  ❌ Erros: %d', $errorCount));

        if ($this->option('repair-billing')) {
            $this->newLine();
            $this->info('🔧 Corrigindo anomalias de billing...');
            $billingFixed = $this->repairBillingAnomalies();
            $this->line(sprintf('  ✅ Assinaturas ajustadas em billing: %d', $billingFixed));
        }

        // Verificação final
        $this->newLine();
        $this->info('🔍 Executando verificação final...');

        $orfas = $this->syncService->findOrphanStores();
        $billingAnomalies = $this->option('repair-billing') ? $this->countBillingAnomalies() : 0;

        if ($orfas->isEmpty() && $billingAnomalies === 0) {
            $this->info('✅ Sistema está íntegro! Nenhuma loja órfã restante.');
            return self::SUCCESS;
        } else {
            $this->warn(sprintf('⚠️  Ainda existem %d loja(s) sem assinatura', $orfas->count()));

            if ($this->option('repair-billing') && $billingAnomalies > 0) {
                $this->warn(sprintf('⚠️  Ainda existem %d inconsistência(s) de billing', $billingAnomalies));
            }

            return self::FAILURE;
        }
    }

    private function repairBillingAnomalies(): int
    {
        $updated = 0;

        Assinatura::query()->chunkById(200, function ($assinaturas) use (&$updated): void {
            foreach ($assinaturas as $assinatura) {
                $dirty = [];

                if (! in_array($assinatura->billing_cycle, [Assinatura::BILLING_MONTHLY, Assinatura::BILLING_YEARLY], true)) {
                    $dirty['billing_cycle'] = Assinatura::BILLING_MONTHLY;
                }

                $statusNeedsNextBilling = in_array($assinatura->status, [
                    Assinatura::STATUS_ACTIVE,
                    Assinatura::STATUS_PAST_DUE,
                ], true);

                if ($statusNeedsNextBilling && ! $assinatura->next_billing_at) {
                    $cycle = $dirty['billing_cycle'] ?? $assinatura->billing_cycle ?? Assinatura::BILLING_MONTHLY;
                    $dirty['next_billing_at'] = $cycle === Assinatura::BILLING_YEARLY ? now()->addYear() : now()->addMonth();
                }

                if ($assinatura->status === Assinatura::STATUS_TRIAL && ! $assinatura->next_billing_at && ! $assinatura->trial_ends_at) {
                    $trialEnd = now()->addDays((int) ($assinatura->plano?->trial_days ?? config('saas.trial_default_days', 14)));
                    $dirty['trial_ends_at'] = $trialEnd;
                    $dirty['next_billing_at'] = $trialEnd;
                }

                if ($dirty !== []) {
                    $assinatura->update($dirty);
                    $updated++;
                }
            }
        });

        return $updated;
    }

    private function countBillingAnomalies(): int
    {
        return (int) Assinatura::query()
            ->where(function ($query): void {
                $query->whereNotNull('billing_cycle')
                    ->whereNotIn('billing_cycle', [Assinatura::BILLING_MONTHLY, Assinatura::BILLING_YEARLY]);
            })
            ->orWhere(function ($query): void {
                $query->whereIn('status', [Assinatura::STATUS_ACTIVE, Assinatura::STATUS_PAST_DUE])
                    ->whereNull('next_billing_at');
            })
            ->orWhere(function ($query): void {
                $query->where('status', Assinatura::STATUS_TRIAL)
                    ->whereNull('next_billing_at')
                    ->whereNull('trial_ends_at');
            })
            ->count();
    }
}
