<?php

declare(strict_types=1);

namespace App\Providers;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 20:11 -03:00
*/

use App\Services\Pix\AsaasService;
use App\Models\SiteConfiguracao;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\FinancialTitle;
use App\Models\Produto;
use App\Models\Usuario;
use App\Policies\ClientePolicy;
use App\Policies\FinancialTitlePolicy;
use App\Policies\PedidoPolicy;
use App\Policies\ProdutoPolicy;
use App\Policies\UsuarioPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\SaaS\TenantContext::class);

        $this->app->singleton(AsaasService::class, function (): AsaasService {
            return new AsaasService(
                config('asaas.url_base'),
                config('asaas.chave_api'),
                config('asaas.token_webhook')
            );
        });
    }

    public function boot(): void
    {
        Gate::policy(Usuario::class, UsuarioPolicy::class);
        Gate::policy(Pedido::class, PedidoPolicy::class);
        Gate::policy(Produto::class, ProdutoPolicy::class);
        Gate::policy(Cliente::class, ClientePolicy::class);
        Gate::policy(FinancialTitle::class, FinancialTitlePolicy::class);

        // Limpa cache ao alterar configurações
        SiteConfiguracao::saved(function ($config) {
            $lojaId = $config->loja_id ?? 'global';
            Cache::forget("site_configs_{$lojaId}");
            Cache::forget('site_configuracoes_todas');
        });
        SiteConfiguracao::deleted(function ($config) {
            $lojaId = $config->loja_id ?? 'global';
            Cache::forget("site_configs_{$lojaId}");
            Cache::forget('site_configuracoes_todas');
        });

        // Limpa cache de páginas legais ao alterar
        \App\Models\PaginaLegal::saved(function ($pagina) {
            $lojaId = $pagina->loja_id ?? 'global';
            Cache::forget("paginas_legais_{$lojaId}");
        });
        \App\Models\PaginaLegal::deleted(function ($pagina) {
            $lojaId = $pagina->loja_id ?? 'global';
            Cache::forget("paginas_legais_{$lojaId}");
        });

        // Limpa cache de branding da plataforma ao alterar
        \App\Models\ConfiguracaoSistema::saved(fn() => Cache::forget('branding_plataforma'));
        \App\Models\ConfiguracaoSistema::deleted(fn() => Cache::forget('branding_plataforma'));

        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('configuracoes_sistema')) {
                    $brandingService = app(\App\Services\Branding\BrandingService::class);
                    $lojaId = null;

                    if (Auth::check()) {
                        $lojaId = Auth::user()->loja_id;
                    } else {
                        // Resolução via Contexto Global (definido no TenantDiscoveryMiddleware)
                        $lojaId = app(\App\Services\SaaS\TenantContext::class)->getLojaId();
                    }

                    // Determina o contexto de branding
                    $path = request()->path();
                    $context = 'admin_panel';
                    if (str_starts_with($path, 'super-admin')) $context = 'master';
                    elseif (str_starts_with($path, 'catalogo') || str_starts_with($path, 'p/') || str_starts_with($path, 'checkout') || $path === '/') $context = 'catalog';

                    $branding = $brandingService->resolve($context, $lojaId);
                    $configPlataforma = $brandingService->getPlatformBranding();
                    $configSite = $lojaId ? $brandingService->getTenantBranding($lojaId) : [];

                    // Páginas Legais (Institucional - Rodapé - Sempre da Loja)
                    $tenantKey = $lojaId ?? 'global';
                    $paginasLegais = Cache::remember("paginas_legais_{$tenantKey}", 3600, function () use ($lojaId) {
                        return \App\Models\PaginaLegal::query()
                            ->where('ativa', true)
                            ->where('exibir_no_rodape', true)
                            ->when($lojaId, fn($q) => $q->where('loja_id', $lojaId))
                            ->orderBy('ordem_exibicao')
                            ->get();
                    });

                    $view->with([
                        'branding' => $branding,
                        'configPlataforma' => $configPlataforma,
                        'configSite' => $configSite,
                        'paginasLegais' => $paginasLegais,
                        'currentLojaId' => $lojaId
                    ]);
                }
            } catch (\Exception $e) {
            }
        });
    }
}
