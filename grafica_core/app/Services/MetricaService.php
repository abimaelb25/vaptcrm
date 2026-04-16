<?php

declare(strict_types=1);

namespace App\Services;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 20:53
*/

use App\Models\MetricaSite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MetricaService
{
    /**
     * Registra uma visualização (view) de uma entidade no catálogo.
     */
    public function registrarView(Request $request, string $tipoEntidade, ?int $idEntidade = null): void
    {
        $this->registrar($request, 'view', $tipoEntidade, $idEntidade);
    }

    /**
     * Registra um clique (click) em um botão ou link específico.
     */
    public function registrarClick(Request $request, string $tipoEntidade, ?int $idEntidade = null): void
    {
        $this->registrar($request, 'click', $tipoEntidade, $idEntidade);
    }

    /**
     * Lógica central de captura de telemetria.
     */
    private function registrar(Request $request, string $tipoAcao, string $tipoEntidade, ?int $idEntidade): void
    {
        try {
            $userAgent = $request->header('User-Agent', '');
            
            MetricaSite::create([
                'tipo'          => $tipoAcao,
                'entidade_tipo' => $tipoEntidade,
                'entidade_id'   => $idEntidade,
                'origem'        => $this->detectarOrigem($request),
                'dispositivo'   => $this->detectarDispositivo($userAgent),
                'navegador'     => $this->parseNavegador($userAgent),
                'ip'            => $request->ip(),
            ]);
        } catch (\Exception $e) {
            // Silencioso para não quebrar a navegação do cliente por erro de log
            \Illuminate\Support\Facades\Log::warning('[BI] Erro ao registrar métrica: ' . $e->getMessage());
        }
    }

    /**
     * Identifica a origem do tráfego (UTM ou Referrer).
     */
    private function detectarOrigem(Request $request): string
    {
        if ($request->filled('utm_source')) {
            return (string) $request->utm_source;
        }

        $referrer = $request->header('referer');
        if ($referrer) {
            $host = parse_url($referrer, PHP_URL_HOST);
            return $host ?: 'Direto';
        }

        return 'Direto';
    }

    /**
     * Detecção simplificada de dispositivo baseada em User-Agent.
     */
    private function detectarDispositivo(string $ua): string
    {
        $ua = strtolower($ua);
        if (str_contains($ua, 'mobi') || str_contains($ua, 'tablet') || str_contains($ua, 'android')) {
            return 'mobile';
        }
        return 'desktop';
    }

    /**
     * Parse básico do nome do navegador.
     */
    private function parseNavegador(string $ua): string
    {
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Safari')) return 'Safari';
        if (str_contains($ua, 'Edge'))   return 'Edge';
        return 'Outro';
    }
}
