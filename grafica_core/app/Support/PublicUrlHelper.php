<?php

declare(strict_types=1);

namespace App\Support;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-17
| Descrição: Helper centralizado para geração de URLs públicas da loja
*/

use App\Models\Categoria;
use App\Models\Loja;
use App\Models\Produto;
use App\Services\SaaS\TenantContext;

class PublicUrlHelper
{
    /**
     * Verifica se estamos em ambiente local (desenvolvimento).
     */
    protected static function isLocal(): bool
    {
        return config('app.env') === 'local';
    }

    /**
     * Verifica se a loja precisa de query string para identificação.
     * Isso acontece quando não há subdomínio nem domínio personalizado.
     */
    protected static function needsQueryString(Loja $loja): bool
    {
        return empty($loja->dominio_personalizado) && empty($loja->subdominio);
    }

    /**
     * Gera a URL base da loja pública (SEM path, SEM query string).
     * 
     * Prioridade:
     * 1. Domínio personalizado (ex: minhagrafica.com.br)
     * 2. Subdomínio (ex: grafica.vaptcrm.com.br)
     * 3. Fallback para dev: APP_URL base (query string será adicionada depois)
     */
    protected static function baseUrl(?Loja $loja = null): string
    {
        $loja = $loja ?? self::getLojaFromContext();

        if (!$loja) {
            return rtrim(config('app.url'), '/');
        }

        // 1. Prioridade: Domínio personalizado
        if (!empty($loja->dominio_personalizado)) {
            $protocol = self::isLocal() ? 'http' : 'https';
            return $protocol . '://' . $loja->dominio_personalizado;
        }

        // 2. Subdomínio
        if (!empty($loja->subdominio)) {
            $baseDomain = config('app.saas_base_domain', 'vaptcrm.com.br');
            $protocol = self::isLocal() ? 'http' : 'https';
            return $protocol . '://' . $loja->subdominio . '.' . $baseDomain;
        }

        // 3. Fallback: ambiente de desenvolvimento - usa APP_URL
        return rtrim(config('app.url'), '/');
    }

    /**
     * Monta URL completa com path e query string (se necessário).
     */
    protected static function buildUrl(string $path = '', array $extraQuery = [], ?Loja $loja = null): string
    {
        $loja = $loja ?? self::getLojaFromContext();
        $base = self::baseUrl($loja);
        
        // Adiciona o path
        $url = $base . '/' . ltrim($path, '/');
        
        // Monta query string
        $query = [];
        
        // Em ambiente local sem subdomínio/domínio, adiciona ?loja=slug
        if ($loja && self::needsQueryString($loja) && self::isLocal()) {
            $query['loja'] = $loja->slug;
        }
        
        // Adiciona query params extras
        $query = array_merge($query, $extraQuery);
        
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }
        
        return $url;
    }

    /**
     * URL base da loja (página inicial).
     * @deprecated Use inicio() para clareza
     */
    public static function base(?Loja $loja = null): string
    {
        return self::buildUrl('', [], $loja);
    }

    /**
     * URL do catálogo público.
     */
    public static function catalogo(?Loja $loja = null): string
    {
        return self::buildUrl('catalogo', [], $loja);
    }

    /**
     * URL de um produto específico.
     */
    public static function produto(Produto $produto, ?Loja $loja = null): string
    {
        return self::buildUrl('produto/' . $produto->slug, [], $loja);
    }

    /**
     * URL de um produto por slug (para uso quando não temos o objeto Produto).
     */
    public static function produtoPorSlug(string $slug, ?Loja $loja = null): string
    {
        return self::buildUrl('produto/' . $slug, [], $loja);
    }

    /**
     * URL do catálogo filtrado por categoria.
     */
    public static function categoria(Categoria $categoria, ?Loja $loja = null): string
    {
        return self::buildUrl('catalogo', ['categoria' => $categoria->slug], $loja);
    }

    /**
     * URL da página inicial da loja.
     */
    public static function inicio(?Loja $loja = null): string
    {
        return self::buildUrl('', [], $loja);
    }

    /**
     * URL do carrinho.
     */
    public static function carrinho(?Loja $loja = null): string
    {
        return self::buildUrl('carrinho', [], $loja);
    }

    /**
     * URL do checkout.
     */
    public static function checkout(?Loja $loja = null): string
    {
        return self::buildUrl('checkout', [], $loja);
    }

    /**
     * Obtém a loja do contexto atual (TenantContext ou usuário logado).
     */
    protected static function getLojaFromContext(): ?Loja
    {
        // Tenta obter do TenantContext
        try {
            $tenantContext = app(TenantContext::class);
            if ($tenantContext->hasTenant()) {
                return $tenantContext->getLoja();
            }
        } catch (\Throwable $e) {
            // TenantContext não disponível
        }

        // Fallback: usuário logado
        if (auth()->check()) {
            $user = auth()->user();
            if ($user && $user->loja) {
                return $user->loja;
            }
        }

        return null;
    }
}
