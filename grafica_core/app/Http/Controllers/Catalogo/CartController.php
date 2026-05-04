<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use App\Services\Catalogo\CartService;
use App\Services\SaaS\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller do Carrinho de Compras do Catálogo Público
 */
class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private TenantContext $tenantContext
    ) {}

    /**
     * Exibe a página do carrinho
     */
    public function index(): View
    {
        // Proteção: carrinho só funciona no contexto de uma Loja
        if (!$this->tenantContext->hasTenant()) {
            abort(404);
        }

        // Valida itens (remove produtos inativos)
        $removidos = $this->cartService->validarItens();

        return view('publico.carrinho', [
            'carrinho' => $this->cartService->getResumo(),
            'itensRemovidos' => $removidos,
        ]);
    }

    /**
     * Adiciona um produto ao carrinho
     */
    public function adicionar(Request $request): JsonResponse|RedirectResponse
    {
        if (!$this->tenantContext->hasTenant()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Loja não encontrada'], 404);
            }
            abort(404);
        }

        $request->validate([
            'produto_id' => 'required|integer|exists:produtos,id',
            'quantidade' => 'sometimes|integer|min:1|max:9999',
            'variacao_id' => 'nullable|integer|exists:produto_variacoes,id',
            'observacoes' => 'nullable|string|max:500',
        ]);

        try {
            $item = $this->cartService->adicionar(
                produtoId: (int) $request->produto_id,
                quantidade: (int) ($request->quantidade ?? 1),
                variacaoId: $request->variacao_id ? (int) $request->variacao_id : null,
                observacoes: $request->observacoes
            );

            $resumo = $this->cartService->getResumo();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produto adicionado ao carrinho!',
                    'item' => $item,
                    'carrinho' => $resumo,
                ]);
            }

            return redirect()->back()->with('sucesso', 'Produto adicionado ao carrinho!');

        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('erro', $e->getMessage());
        }
    }

    /**
     * Atualiza a quantidade de um item
     */
    public function atualizar(Request $request, string $itemKey): JsonResponse|RedirectResponse
    {
        if (!$this->tenantContext->hasTenant()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Loja não encontrada'], 404);
            }
            abort(404);
        }

        $request->validate([
            'quantidade' => 'required|integer|min:0|max:9999',
        ]);

        $sucesso = $this->cartService->atualizarQuantidade(
            itemKey: $itemKey,
            quantidade: (int) $request->quantidade
        );

        $resumo = $this->cartService->getResumo();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $sucesso,
                'message' => $sucesso ? 'Quantidade atualizada!' : 'Item não encontrado.',
                'carrinho' => $resumo,
            ]);
        }

        return redirect()->route('site.carrinho')
            ->with($sucesso ? 'sucesso' : 'erro', $sucesso ? 'Quantidade atualizada!' : 'Item não encontrado.');
    }

    /**
     * Remove um item do carrinho
     */
    public function remover(Request $request, string $itemKey): JsonResponse|RedirectResponse
    {
        if (!$this->tenantContext->hasTenant()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Loja não encontrada'], 404);
            }
            abort(404);
        }

        $sucesso = $this->cartService->remover($itemKey);

        $resumo = $this->cartService->getResumo();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $sucesso,
                'message' => $sucesso ? 'Item removido!' : 'Item não encontrado.',
                'carrinho' => $resumo,
            ]);
        }

        return redirect()->route('site.carrinho')
            ->with($sucesso ? 'sucesso' : 'erro', $sucesso ? 'Item removido!' : 'Item não encontrado.');
    }

    /**
     * Limpa todo o carrinho
     */
    public function limpar(Request $request): JsonResponse|RedirectResponse
    {
        if (!$this->tenantContext->hasTenant()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Loja não encontrada'], 404);
            }
            abort(404);
        }

        $this->cartService->limpar();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Carrinho limpo!',
                'carrinho' => $this->cartService->getResumo(),
            ]);
        }

        return redirect()->route('site.carrinho')->with('sucesso', 'Carrinho limpo!');
    }

    /**
     * Retorna o resumo do carrinho (para AJAX/API)
     */
    public function resumo(): JsonResponse
    {
        if (!$this->tenantContext->hasTenant()) {
            return response()->json(['error' => 'Loja não encontrada'], 404);
        }

        return response()->json([
            'success' => true,
            'carrinho' => $this->cartService->getResumo(),
        ]);
    }
}
