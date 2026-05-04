<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimulatePricingRequest;
use App\Models\Produto;
use App\Services\Domain\PricingEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-27
*/

class ProductPricingController extends Controller
{
    public function __construct(private PricingEngineService $engine)
    {
        // Garante que todo o controller requer autenticação
        $this->middleware('auth');
    }

    /**
     * Endpoint 1: Simular composição e preço (Sem Persistência)
     */
    public function simular(SimulatePricingRequest $request, Produto $produto): JsonResponse
    {
        // 1. Autorização Estrita (Evitar que Atendente altere ou Loja A acesse Loja B)
        $this->authorize('update', $produto);
        if ((int) $produto->loja_id !== (int) auth()->user()->loja_id) {
            Log::warning('Tentativa de acesso cross-tenant em simulação de preço', [
                'user_id' => auth()->id(),
                'produto_id' => $produto->id
            ]);
            return response()->json(['success' => false, 'message' => 'Acesso negado ao produto.'], 403);
        }

        try {
            // 2. Delegação do payload validado pelo Request
            $resultado = $this->engine->simularPayload($request->validated(), $produto->loja_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Simulação calculada com sucesso.',
                'data' => $resultado
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Falha ao processar simulação.',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Endpoint 2: Recalcular produto existente (Com persistência)
     */
    public function recalcular(Request $request, Produto $produto): JsonResponse
    {
        $this->authorize('update', $produto);
        if ((int) $produto->loja_id !== (int) auth()->user()->loja_id) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        try {
            $resultado = $this->engine->recalcularProdutoExistente($produto);
            
            $this->engine->efetivarRecalculo(
                $produto, 
                $resultado, 
                'recalculo_manual_admin', 
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Custo recalculado e histórico registrado com sucesso.',
                'data' => $resultado
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recalcular custos do produto.',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Endpoint 3: Buscar resumo de precificação atual
     */
    public function resumo(Produto $produto): JsonResponse
    {
        $this->authorize('view', $produto);
        if ((int) $produto->loja_id !== (int) auth()->user()->loja_id) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Resumo carregado.',
            'data' => [
                'modo_precificacao' => $produto->preco_base == $produto->preco_sugerido ? 'dinamico' : 'manual',
                'custo_producao'    => $produto->custo_producao,
                'custo_base'        => $produto->custo_base,
                'preco_sugerido'    => $produto->preco_sugerido,
                'preco_manual'      => $produto->preco_base,
                'margem_lucro'      => $produto->margem_lucro,
            ]
        ]);
    }
}
