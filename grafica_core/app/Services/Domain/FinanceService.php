<?php

declare(strict_types=1);

namespace App\Services\Domain;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:30
| Descrição: Motor financeiro operacional para gestão de títulos e pagamentos.
*/

use App\Models\FinancialTitle;
use App\Models\FinancialPayment;
use App\Models\FinancialCategory;
use App\Models\FinancialAccount;
use App\Models\Pedido;
use App\Models\MovimentacaoFinanceira;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceService
{
    /**
     * Gera um título a receber baseado em um pedido.
     */
    public function createReceivableFromOrder(Pedido $order): FinancialTitle
    {
        return DB::transaction(function () use ($order) {
            // Verifica se já existe um título para este pedido
            $title = FinancialTitle::where('origem', 'pedido')
                ->where('referencia_id', $order->id)
                ->first();

            if (!$title) {
                $title = FinancialTitle::create([
                    'loja_id' => $order->loja_id,
                    'tipo' => 'receber',
                    'origem' => 'pedido',
                    'referencia_id' => $order->id,
                    'descricao' => "Venda: Pedido #{$order->numero}",
                    'valor_total' => $order->total,
                    'valor_pago' => 0,
                    'saldo_restante' => $order->total,
                    'data_emissao' => now(),
                    'data_vencimento' => now()->addDays(2), // Padrão 2 dias para boletos/pix se não pago
                    'status' => 'aberto',
                ]);
            } else {
                // Se o total do pedido mudou, atualizamos o título
                if ($title->valor_total != $order->total) {
                    $title->valor_total = $order->total;
                    $title->atualizarSaldos();
                }
            }

            return $title;
        });
    }

    /**
     * Registra um pagamento parcial ou total para um título.
     */
    public function addPayment(FinancialTitle $title, array $data): FinancialPayment
    {
        return DB::transaction(function () use ($title, $data) {
            $payment = FinancialPayment::create([
                'loja_id' => $title->loja_id,
                'financial_title_id' => $title->id,
                'financial_account_id' => $data['account_id'] ?? null,
                'valor' => (float) $data['valor'],
                'forma_pagamento' => $data['forma_pagamento'] ?? 'Dinheiro',
                'data_pagamento' => $data['data_pagamento'] ?? now(),
                'comprovante_path' => $data['comprovante_path'] ?? null,
            ]);

            // Compatibilidade Legado: Criar espelho na tabela antiga para balancetes simplificados
            MovimentacaoFinanceira::create([
                'loja_id' => $title->loja_id,
                'tipo' => $title->tipo === 'receber' ? 'entrada' : 'saida',
                'categoria' => $title->categoria?->nome ?? ($title->tipo === 'receber' ? 'Vendas' : 'Despesas'),
                'valor' => $payment->valor,
                'data_movimentacao' => $payment->data_pagamento,
                'forma_pagamento' => $payment->forma_pagamento,
                'status' => 'pago',
                'pedido_id' => $title->origem === 'pedido' ? $title->referencia_id : null,
                'descricao' => "PAG: {$title->descricao}",
            ]);

            return $payment;
        });
    }

    /**
     * Facilita a criação de uma despesa manual (A Pagar).
     */
    public function createExpense(array $data): FinancialTitle
    {
        return FinancialTitle::create([
            'tipo' => 'pagar',
            'origem' => 'manual',
            'descricao' => $data['descricao'],
            'categoria_id' => $data['categoria_id'] ?? null,
            'valor_total' => (float) $data['valor'],
            'valor_pago' => 0,
            'saldo_restante' => (float) $data['valor'],
            'data_emissao' => $data['data_emissao'] ?? now(),
            'data_vencimento' => $data['data_vencimento'] ?? now(),
            'status' => 'aberto',
            'observacao' => $data['observacao'] ?? null,
        ]);
    }

    /**
     * Divide um título em múltiplas parcelas.
     */
    public function splitTitle(FinancialTitle $original, int $installments, int $daysInterval = 30): void
    {
        DB::transaction(function () use ($original, $installments, $daysInterval) {
            $valorParcela = floor(($original->valor_total / $installments) * 100) / 100;
            $resto = $original->valor_total - ($valorParcela * $installments);

            for ($i = 1; $i <= $installments; $i++) {
                $valorFinal = ($i === $installments) ? ($valorParcela + $resto) : $valorParcela;
                
                FinancialTitle::create([
                    'loja_id' => $original->loja_id,
                    'tipo' => $original->tipo,
                    'origem' => $original->origem,
                    'referencia_id' => $original->referencia_id,
                    'descricao' => "{$original->descricao} ({$i}/{$installments})",
                    'categoria_id' => $original->categoria_id,
                    'valor_total' => $valorFinal,
                    'saldo_restante' => $valorFinal,
                    'data_emissao' => $original->data_emissao,
                    'data_vencimento' => $original->data_vencimento->addDays(($i - 1) * $daysInterval),
                    'status' => 'aberto',
                ]);
            }

            // Remove o título original "pai" se ele for apenas um placeholder
            $original->delete();
        });
    }
}
