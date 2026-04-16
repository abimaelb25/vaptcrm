<?php

declare(strict_types=1);

namespace App\Services\Branding;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16 00:00
*/

use App\Models\ConfiguracaoSistema;
use App\Models\SiteConfiguracao;
use Illuminate\Support\Facades\Cache;

class BrandingService
{
    /**
     * Resolve o branding da plataforma (SaaS)
     */
    public function getPlatformBranding(): array
    {
        return Cache::remember('branding_plataforma', 86400, function () {
            return ConfiguracaoSistema::pluck('valor', 'chave')->toArray();
        });
    }

    /**
     * Resolve o branding de uma loja específica (Tenant)
     */
    public function getTenantBranding(int|string|null $lojaId): array
    {
        if (!$lojaId) {
            return [];
        }

        return Cache::remember("site_configs_{$lojaId}", 3600, function () use ($lojaId) {
            return SiteConfiguracao::where('loja_id', $lojaId)
                ->pluck('valor', 'chave')
                ->toArray();
        });
    }

    /**
     * Retorna a marca correta baseada no contexto
     */
    public function resolve(string $context, $lojaId = null): array
    {
        $platform = $this->getPlatformBranding();
        $tenant = $lojaId ? $this->getTenantBranding($lojaId) : [];

        switch ($context) {
            case 'catalog':
            case 'public_page':
            case 'checkout':
                return [
                    'logo' => !empty($tenant['aparencia_logo']) ? $tenant['aparencia_logo'] : $platform['plataforma_logo'],
                    'favicon' => !empty($tenant['aparencia_favicon']) ? $tenant['aparencia_favicon'] : $platform['plataforma_favicon'],
                    'name' => $tenant['empresa_nome'] ?? $tenant['loja_nome'] ?? $platform['plataforma_nome'],
                    'primary_color' => $tenant['aparencia_cor_primaria'] ?? $platform['plataforma_cor_primaria'],
                    'secondary_color' => $tenant['aparencia_cor_secundaria'] ?? $platform['plataforma_cor_secundaria'],
                    'support_whatsapp' => $tenant['empresa_whatsapp'] ?? $platform['plataforma_whatsapp_suporte'],
                    'is_tenant' => true
                ];

            case 'master':
                return [
                    'logo' => $platform['plataforma_logo'],
                    'favicon' => $platform['plataforma_favicon'],
                    'name' => $platform['plataforma_nome'],
                    'primary_color' => $platform['plataforma_cor_primaria'],
                    'secondary_color' => $platform['plataforma_cor_secundaria'],
                    'is_tenant' => false
                ];

            case 'admin_panel':
            default:
                // No painel administrativo da loja, usamos a marca da plataforma no chrome
                // mas podemos disponibilizar dados da loja para contextuais
                return [
                    'logo' => $platform['plataforma_logo'],
                    'favicon' => $platform['plataforma_favicon'],
                    'name' => $platform['plataforma_nome'],
                    'tenant_name' => $tenant['empresa_nome'] ?? $tenant['loja_nome'] ?? '',
                    'primary_color' => $platform['plataforma_cor_primaria'],
                    'secondary_color' => $platform['plataforma_cor_secundaria'],
                    'is_tenant' => false
                ];
        }
    }
}
