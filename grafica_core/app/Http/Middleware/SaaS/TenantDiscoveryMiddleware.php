<?php

declare(strict_types=1);

namespace App\Http\Middleware\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16 09:40
| Descrição: Middleware para descoberta da Loja (Tenant) atual via domínio/subdomínio.
*/

use App\Models\Loja;
use App\Services\SaaS\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class TenantDiscoveryMiddleware
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $baseHost = parse_url(config('app.url'), PHP_URL_HOST);

        // Se estivermos no domínio principal (plataforma), não carregamos tenant específico por padrão.
        if ($host === $baseHost || $host === 'localhost' || $host === '127.0.0.1') {
            
            // CONVENIÊNCIA PARA DESENVOLVIMENTO LOCAL (127.0.0.1 / localhost)
            // Agora que o middleware roda após o StartSession, podemos usar a sessão.
            if (config('app.env') === 'local') {
                $loja = null;

                // 1. Prioridade absoluta: Parâmetro explicitamente na URL (?loja=slug)
                if ($request->has('loja')) {
                    $loja = Loja::where('slug', $request->query('loja'))->first();
                    if ($loja) {
                        $request->session()->put('tenant_test_loja_id', $loja->id);
                    }
                }

                // 2. Fallback local: Recupera da sessão (persistência durante navegação em dev)
                if (!$loja && $request->session()->has('tenant_test_loja_id')) {
                    $lojaId = $request->session()->get('tenant_test_loja_id');
                    $loja = Loja::find($lojaId);
                }

                // 3. Fallback Logado: Se o usuário estiver autenticado, assume a loja dele
                if (!$loja && auth()->check()) {
                    $loja = auth()->user()->loja;
                }

                if ($loja) {
                    $this->tenantContext->setLoja($loja);
                    View::share('lojaAtual', $loja);
                }
            }

            return $next($request);
        }

        // Tenta resolver a loja pelo Host (Cache de 1 hora para performance)
        $loja = Cache::remember("tenant_id_host_{$host}", 3600, function () use ($host, $baseHost) {
            // 1. Busca por Domínio Personalizado
            $l = Loja::where('dominio_personalizado', $host)->first();
            if ($l) return $l;

            // 2. Busca por Subdomínio
            // Exemplo: 'fantasia.vaptcrm.com.br' -> 'fantasia'
            $subdominio = explode('.', $host)[0];
            
            // Proteção: não deixar subdomínio 'www' ou vazio resolver loja
            if ($subdominio === 'www' || empty($subdominio)) {
                return null;
            }

            return Loja::where('subdominio', $subdominio)->first();
        });

        if ($loja) {
            // Registra a loja no contexto global da requisição
            $this->tenantContext->setLoja($loja);

            // Compartilha a loja com todas as views do Blade (branding dinâmico)
            View::share('lojaAtual', $loja);
            
            // Injeta configurações no container se necessário
            app()->instance('tenant', $loja);
        }

        return $next($request);
    }
}
