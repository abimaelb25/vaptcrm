<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class AuditarGovernancaSaaSCommand extends Command
{
    protected $signature = 'saas:audit-governance';

    protected $description = 'Audita proteção de rotas SaaS e riscos de isolamento multi-tenant em jobs/console.';

    public function handle(): int
    {
        $findings = [];

        foreach (Route::getRoutes() as $route) {
            $uri = ltrim($route->uri(), '/');
            $middlewares = $route->gatherMiddleware();

            if (str_starts_with($uri, 'painel')) {
                if (! in_array('auth', $middlewares, true)) {
                    $findings[] = "[rota] {$uri} sem middleware auth.";
                }

                if (! in_array('assinatura', $middlewares, true) && ! in_array('super_admin', $middlewares, true)) {
                    $findings[] = "[rota] {$uri} sem middleware assinatura/super_admin.";
                }
            }

            if (str_starts_with($uri, 'api/production')) {
                if (! in_array('auth', $middlewares, true)) {
                    $findings[] = "[api] {$uri} sem auth.";
                }

                if (! in_array('assinatura', $middlewares, true)) {
                    $findings[] = "[api] {$uri} sem assinatura.";
                }

                if (! in_array('check_plan_feature:modulo_api', $middlewares, true)) {
                    $findings[] = "[api] {$uri} sem check_plan_feature:modulo_api.";
                }
            }
        }

        $findings = array_merge($findings, $this->auditFiles(app_path('Jobs'), 'job'));
        $findings = array_merge($findings, $this->auditFiles(app_path('Console/Commands'), 'console'));

        if ($findings === []) {
            $this->info('Governança SaaS: auditoria concluída sem achados críticos.');
            return self::SUCCESS;
        }

        $this->warn('Governança SaaS: foram encontrados pontos de risco.');
        foreach ($findings as $finding) {
            $this->line('- ' . $finding);
        }

        return self::FAILURE;
    }

    private function auditFiles(string $directory, string $type): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $risks = [];

        foreach (File::allFiles($directory) as $file) {
            $path = $file->getPathname();
            $content = File::get($path);

            if ($file->getFilename() === 'AuditarGovernancaSaaSCommand.php') {
                continue;
            }

            $hasTenantSignal = str_contains($content, 'loja_id')
                || str_contains($content, 'TenantContext')
                || str_contains($content, 'HasTenancy')
                || str_contains($content, '->loja');

            if (! $hasTenantSignal) {
                $risks[] = "[{$type}] {$file->getRelativePathname()} sem referência explícita a tenant/loja.";
            }

            if (str_contains($content, "withoutGlobalScope('loja')")
                && ! str_contains($content, "where('loja_id'")
                && ! str_contains($content, '->where("loja_id"')
            ) {
                $risks[] = "[{$type}] {$file->getRelativePathname()} usa withoutGlobalScope('loja') sem filtro explícito por loja_id.";
            }
        }

        return $risks;
    }
}
