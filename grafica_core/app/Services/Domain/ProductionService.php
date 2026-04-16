<?php

declare(strict_types=1);

namespace App\Services\Domain;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:50
| Descrição: Motor de produção para gestão de ordens e etapas de chão de fábrica.
*/

use App\Models\ProductionOrder;
use App\Models\ProductionStep;
use App\Models\ProductionOrderStep;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    /**
     * Cria uma Ordem de Produção baseada em um Pedido.
     */
    public function createFromOrder(Pedido $order): ProductionOrder
    {
        return DB::transaction(function () use ($order) {
            // Verifica se já existe uma OP para este pedido
            $po = ProductionOrder::where('pedido_id', $order->id)->first();
            
            if (!$po) {
                $po = ProductionOrder::create([
                    'loja_id' => $order->loja_id,
                    'pedido_id' => $order->id,
                    'status' => 'aguardando',
                    'prioridade' => 'media',
                    'data_previsao' => $order->prazo_entrega,
                ]);

                // Adiciona as etapas padrão da loja (se houver)
                $steps = ProductionStep::where('loja_id', $order->loja_id)
                    ->where('ativo', true)
                    ->orderBy('ordem')
                    ->get();

                foreach ($steps as $step) {
                    ProductionOrderStep::create([
                        'loja_id' => $order->loja_id,
                        'production_order_id' => $po->id,
                        'production_step_id' => $step->id,
                        'status' => 'pendente',
                    ]);
                }
            }

            return $po;
        });
    }

    /**
     * Atualiza o status de uma etapa de produção.
     */
    public function updateStepStatus(ProductionOrderStep $orderStep, string $status, ?int $userId): void
    {
        DB::transaction(function () use ($orderStep, $status, $userId) {
            $data = ['status' => $status];
            
            if ($status === 'em_andamento') {
                $data['data_inicio'] = now();
                $data['responsavel_id'] = $userId ?? $orderStep->responsavel_id;
                
                // Atualiza a OP pai para 'em_producao' se ainda não estiver
                if ($orderStep->order->status === 'aguardando') {
                    $orderStep->order->update(['status' => 'em_producao', 'data_inicio' => now()]);
                }
            }

            if ($status === 'concluido') {
                $data['data_fim'] = now();
                
                // Calcula tempo real se houver data_inicio
                if ($orderStep->data_inicio) {
                    $data['tempo_real'] = (int) $orderStep->data_inicio->diffInMinutes(now());
                }
            }

            $orderStep->update($data);

            // Verifica se todas as etapas da OP foram concluídas
            $this->checkProductionCompletion($orderStep->order);
        });
    }

    /**
     * Verifica conclusão total da OP e atualiza o pedido.
     */
    protected function checkProductionCompletion(ProductionOrder $po): void
    {
        $total = $po->stages()->count();
        $concluidas = $po->stages()->where('status', 'concluido')->count();

        if ($total > 0 && $total === $concluidas) {
            $po->update([
                'status' => 'finalizado',
                'data_conclusao' => now()
            ]);

            // Opcional: Atualizar status do pedido para "Pronto para Retirada" ou similar
            $po->pedido->update(['status' => 'finalizado']);
        }
    }
}
