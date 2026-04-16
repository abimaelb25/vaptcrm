<?php

declare(strict_types=1);

namespace App\Services;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-11 01:36 -03:00
*/

use App\Models\Cupom;

class CupomService
{
    public function validateAndApply(?string $codigo, float $subtotal, ?int $lojaId): array
    {
        if (empty($codigo) || ! $lojaId) {
            return [
                'valido' => false,
                'desconto' => 0,
                'cupom_id' => null,
                'mensagem' => empty($codigo) ? null : 'Loja não identificada para validar o cupom.',
            ];
        }

        $cupom = Cupom::query()
            ->where('loja_id', $lojaId)
            ->where('codigo', $codigo)
            ->where('ativo', true)
            ->first();

        if (! $cupom) {
            return [
                'valido' => false,
                'desconto' => 0,
                'cupom_id' => null,
                'mensagem' => 'Cupom não encontrado para esta loja.',
            ];
        }

        if (! $cupom->isValid()) {
            return [
                'valido' => false,
                'desconto' => 0,
                'cupom_id' => null,
                'mensagem' => 'Cupom expirado ou limite de uso atingido.',
            ];
        }

        if (($cupom->valor_minimo_pedido ?? 0) > 0 && $subtotal < (float) $cupom->valor_minimo_pedido) {
            return [
                'valido' => false,
                'desconto' => 0,
                'cupom_id' => null,
                'mensagem' => "Pedido mínimo de R$ {$cupom->valor_minimo_pedido} necessário.",
            ];
        }

        $desconto = $cupom->calcularDesconto($subtotal);

        return [
            'valido' => true,
            'desconto' => $desconto,
            'cupom_id' => $cupom->id,
            'mensagem' => null,
        ];
    }

    public function incrementUsage(int $cupomId): void
    {
        Cupom::where('id', $cupomId)->increment('quantidade_utilizada');
    }

    public function getActiveCoupons(?int $lojaId, int $perPage = 15)
    {
        return Cupom::query()
            ->when($lojaId, fn ($query) => $query->where('loja_id', $lojaId))
            ->where('ativo', true)
            ->latest()
            ->paginate($perPage);
    }
}
