<?php

declare(strict_types=1);

namespace App\Support\Menu;

class MenuState
{
    /**
     * Resolve estado ativo de um item do menu com regras centralizadas.
     */
    public static function isActive(array $item): bool
    {
        $route = request()->route();

        if (!$route) {
            return false;
        }

        $routeName = (string) ($route->getName() ?? '');
        $isActive = false;

        $activeRoutes = $item['active_routes'] ?? [];
        if (is_string($activeRoutes) && $activeRoutes !== '') {
            $activeRoutes = [$activeRoutes];
        }

        if (is_array($activeRoutes) && !empty($activeRoutes)) {
            $isActive = in_array($routeName, $activeRoutes, true);
        }

        $activePatterns = $item['active_patterns'] ?? ($item['active_pattern'] ?? []);
        if (is_string($activePatterns) && $activePatterns !== '') {
            $activePatterns = [$activePatterns];
        }

        if (!$isActive && is_array($activePatterns)) {
            foreach ($activePatterns as $pattern) {
                if (!is_string($pattern) || $pattern === '') {
                    continue;
                }

                if (request()->routeIs($pattern)) {
                    $isActive = true;
                    break;
                }
            }
        }

        if (!$isActive && isset($item['route']) && is_string($item['route'])) {
            $isActive = $routeName === $item['route'];
        }

        if (!$isActive) {
            return false;
        }

        $activeParams = $item['active_params'] ?? [];
        if (!is_array($activeParams) || empty($activeParams)) {
            return true;
        }

        foreach ($activeParams as $param => $expected) {
            $current = $route->parameter((string) $param);
            if ((string) $current !== (string) $expected) {
                return false;
            }
        }

        return true;
    }
}
