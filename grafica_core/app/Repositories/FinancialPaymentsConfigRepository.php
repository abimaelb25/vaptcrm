<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Cupom;
use App\Models\SiteConfiguracao;
use Illuminate\Support\Collection;

class FinancialPaymentsConfigRepository
{
    public function countActiveCouponsByLoja(?int $lojaId): int
    {
        return Cupom::query()
            ->when($lojaId, fn ($query) => $query->where('loja_id', $lojaId))
            ->where('ativo', true)
            ->count();
    }

    public function getLatestCouponsByLoja(?int $lojaId, int $limit = 10): Collection
    {
        return Cupom::query()
            ->when($lojaId, fn ($query) => $query->where('loja_id', $lojaId))
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getPixConfigByLojaWithFallback(?int $lojaId): array
    {
        $chavesPix = ['empresa_pix_chave', 'empresa_pix_tipo', 'empresa_beneficiario', 'empresa_cidade'];

        $pix = SiteConfiguracao::query()
            ->when($lojaId, fn ($query) => $query->where('loja_id', $lojaId))
            ->whereIn('chave', $chavesPix)
            ->pluck('valor', 'chave')
            ->toArray();

        if (empty($pix)) {
            $pix = SiteConfiguracao::query()
                ->whereNull('loja_id')
                ->whereIn('chave', $chavesPix)
                ->pluck('valor', 'chave')
                ->toArray();
        }

        return [
            'chave' => $pix['empresa_pix_chave'] ?? '',
            'tipo' => $pix['empresa_pix_tipo'] ?? '',
            'beneficiario' => $pix['empresa_beneficiario'] ?? '',
            'cidade' => $pix['empresa_cidade'] ?? '',
        ];
    }
}
