<?php

declare(strict_types=1);

namespace App\Services;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-11 01:37 -03:00
*/

use App\Models\SiteConfiguracao;
use Illuminate\Support\Facades\Cache;

class FreteService
{
    public function getConfig(?int $lojaId): array
    {
        if (! $lojaId) {
            return [
                'ativo' => false,
                'valor' => 0.0,
                'obrigatorio' => false,
            ];
        }

        $cacheKey = "frete_config:{$lojaId}";

        return Cache::remember($cacheKey, 3600, function () use ($lojaId) {
            $configs = SiteConfiguracao::query()
                ->where('loja_id', $lojaId)
                ->whereIn('chave', ['frete_fixo_ativo', 'frete_fixo_valor', 'frete_fixo_obrigatorio'])
                ->pluck('valor', 'chave')
                ->toArray();

            if (empty($configs)) {
                $configs = SiteConfiguracao::query()
                    ->whereNull('loja_id')
                    ->whereIn('chave', ['frete_fixo_ativo', 'frete_fixo_valor', 'frete_fixo_obrigatorio'])
                    ->pluck('valor', 'chave')
                    ->toArray();
            }

            return [
                'ativo' => ($configs['frete_fixo_ativo'] ?? '0') === '1',
                'valor' => (float) ($configs['frete_fixo_valor'] ?? 0),
                'obrigatorio' => ($configs['frete_fixo_obrigatorio'] ?? '0') === '1',
            ];
        });
    }

    public function saveConfig(?int $lojaId, array $data): void
    {
        if (! $lojaId) {
            return;
        }

        SiteConfiguracao::updateOrCreate(
            ['loja_id' => $lojaId, 'chave' => 'frete_fixo_ativo'],
            ['valor' => $data['ativo'] ? '1' : '0', 'tipo' => 'boolean']
        );

        SiteConfiguracao::updateOrCreate(
            ['loja_id' => $lojaId, 'chave' => 'frete_fixo_valor'],
            ['valor' => number_format($data['valor'], 2, '.', ''), 'tipo' => 'decimal']
        );

        SiteConfiguracao::updateOrCreate(
            ['loja_id' => $lojaId, 'chave' => 'frete_fixo_obrigatorio'],
            ['valor' => $data['obrigatorio'] ? '1' : '0', 'tipo' => 'boolean']
        );

        Cache::forget("frete_config:{$lojaId}");
        Cache::forget('site_configuracoes_todas');
    }

    public function calculateForOrder(?int $lojaId, ?string $tipoEntrega = null): float
    {
        $config = $this->getConfig($lojaId);

        if (! $config['ativo']) {
            return 0;
        }

        if ($config['obrigatorio']) {
            return $config['valor'];
        }

        if ($tipoEntrega && $tipoEntrega !== 'retirada') {
            return $config['valor'];
        }

        return 0;
    }
}
