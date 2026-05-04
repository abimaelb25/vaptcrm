<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SaaS\IntegrityCheckService;
use Illuminate\Console\Command;

class CheckSaaSIntegrityCommand extends Command
{
    protected $signature = 'saas:check-integrity';

    protected $description = 'Valida integridade entre schema e dados canônicos do módulo SaaS.';

    public function handle(IntegrityCheckService $integrityCheckService): int
    {
        $report = $integrityCheckService->fullReport();

        $this->info('=== SaaS Integrity Check ===');
        $this->newLine();

        $this->line('Tabelas críticas:');
        foreach ($integrityCheckService->criticalTables() as $table) {
            $status = in_array($table, $report['missing_tables'], true) ? 'FALTANDO' : 'OK';
            $this->line(sprintf('- %s: %s', $table, $status));
        }

        if ($report['missing_tables'] !== []) {
            $this->newLine();
            $this->error('Banco desatualizado. Execute php artisan migrate');

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('Planos: ' . $report['plans_total']);

        if ($report['plans_total'] === 0) {
            $this->error('Nenhum plano SaaS cadastrado. Execute o seeder de planos.');

            return self::FAILURE;
        }

        if ($report['plans_without_features'] !== []) {
            $this->warn('Planos sem features:');
            foreach ($report['plans_without_features'] as $plan) {
                $this->line('- ' . $plan);
            }
        } else {
            $this->info('Features por plano: OK');
        }

        if ($report['plans_without_limits'] !== []) {
            $this->warn('Planos sem limits:');
            foreach ($report['plans_without_limits'] as $plan) {
                $this->line('- ' . $plan);
            }
        } else {
            $this->info('Limits por plano: OK');
        }

        if ($report['orphan_feature_rows'] > 0) {
            $this->warn('Features orfas: ' . $report['orphan_feature_rows']);
        } else {
            $this->info('Features orfas: 0');
        }

        if ($report['orphan_limit_rows'] > 0) {
            $this->warn('Limits orfaos: ' . $report['orphan_limit_rows']);
        } else {
            $this->info('Limits orfaos: 0');
        }

        if ($report['stores_without_subscription'] > 0) {
            $this->warn('Lojas sem assinatura: ' . $report['stores_without_subscription']);
        } else {
            $this->info('Lojas sem assinatura: 0');
        }

        if ($report['stores_without_plan'] > 0) {
            $this->warn('Lojas sem plano vinculado: ' . $report['stores_without_plan']);
        } else {
            $this->info('Lojas sem plano vinculado: 0');
        }

        if ($report['subscriptions_with_missing_plan'] > 0) {
            $this->warn('Assinaturas com plano inexistente: ' . $report['subscriptions_with_missing_plan']);
        } else {
            $this->info('Assinaturas com plano inexistente: 0');
        }

        if ($report['subscriptions_with_inactive_plan'] > 0) {
            $this->warn('Assinaturas com plano inativo: ' . $report['subscriptions_with_inactive_plan']);
        } else {
            $this->info('Assinaturas com plano inativo: 0');
        }

        if ($report['invalid_usage_rows'] > 0) {
            $this->warn('Registros de uso invalidos: ' . $report['invalid_usage_rows']);
        } else {
            $this->info('Registros de uso invalidos: 0');
        }

        if ($report['subscriptions_with_invalid_billing_cycle'] > 0) {
            $this->warn('Assinaturas com billing_cycle inválido: ' . $report['subscriptions_with_invalid_billing_cycle']);
        } else {
            $this->info('Assinaturas com billing_cycle inválido: 0');
        }

        if ($report['subscriptions_with_invalid_next_billing_at'] > 0) {
            $this->warn('Assinaturas com next_billing_at inválido: ' . $report['subscriptions_with_invalid_next_billing_at']);
        } else {
            $this->info('Assinaturas com next_billing_at inválido: 0');
        }

        if ($report['subscriptions_with_expired_status_inconsistency'] > 0) {
            $this->warn('Assinaturas com expiração inconsistente: ' . $report['subscriptions_with_expired_status_inconsistency']);
        } else {
            $this->info('Assinaturas com expiração inconsistente: 0');
        }

        if (! $report['ok']) {
            $this->newLine();
            $this->error('Integridade SaaS reprovada. Corrija as inconsistências antes do deploy.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Integridade SaaS validada com sucesso.');

        return self::SUCCESS;
    }
}