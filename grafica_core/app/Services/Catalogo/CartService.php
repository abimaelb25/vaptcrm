<?php

declare(strict_types=1);

namespace App\Services\Catalogo;

use App\Models\Produto;
use App\Models\ProdutoVariacao;
use App\Services\SaaS\TenantContext;
use Illuminate\Support\Facades\Session;

/**
 * Serviço de Carrinho de Compras para o Catálogo Público
 * 
 * Gerencia carrinho na sessão com isolamento por loja (multi-tenant).
 * Cada loja possui sua própria chave de sessão: cart_{loja_id}
 */
class CartService
{
    private ?int $lojaId;

    public function __construct(TenantContext $tenantContext)
    {
        $this->lojaId = $tenantContext->getLojaId();
    }

    /**
     * Retorna a chave de sessão do carrinho para a loja atual
     */
    private function getCartKey(): string
    {
        return 'cart_' . ($this->lojaId ?? 'guest');
    }

    /**
     * Retorna o carrinho completo da loja atual
     */
    public function getCart(): array
    {
        $cart = Session::get($this->getCartKey(), [
            'items' => [],
            'loja_id' => $this->lojaId,
            'updated_at' => now()->toDateTimeString(),
        ]);

        // Garante que o carrinho pertence à loja atual (segurança)
        if (($cart['loja_id'] ?? null) !== $this->lojaId) {
            return [
                'items' => [],
                'loja_id' => $this->lojaId,
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        return $cart;
    }

    /**
     * Salva o carrinho na sessão
     */
    private function saveCart(array $cart): void
    {
        $cart['updated_at'] = now()->toDateTimeString();
        Session::put($this->getCartKey(), $cart);
    }

    /**
     * Adiciona um produto ao carrinho
     * 
     * @throws \InvalidArgumentException Se produto não pertence à loja
     */
    public function adicionar(
        int $produtoId,
        int $quantidade = 1,
        ?int $variacaoId = null,
        ?string $observacoes = null
    ): array {
        // Busca o produto com validação de tenant (scope global)
        $produto = Produto::where('id', $produtoId)
            ->where('ativo', true)
            ->whereIn('visibilidade', ['publico', 'ambos'])
            ->first();

        if (!$produto) {
            throw new \InvalidArgumentException('Produto não encontrado ou não disponível.');
        }

        // Valida variação se fornecida
        $variacao = null;
        $variacaoNome = null;
        $precoUnitario = $produto->preco_base ?? 0;

        if ($variacaoId) {
            $variacao = ProdutoVariacao::where('id', $variacaoId)
                ->where('produto_id', $produtoId)
                ->where('ativo', true)
                ->first();

            if ($variacao) {
                $variacaoNome = $variacao->nome;
                // Se a variação tem preço próprio, usa-o
                if ($variacao->preco) {
                    $precoUnitario = $variacao->preco;
                }
            }
        }

        $cart = $this->getCart();

        // Chave única: produto + variação (se houver)
        $itemKey = $variacaoId ? "produto_{$produtoId}_var_{$variacaoId}" : "produto_{$produtoId}";

        if (isset($cart['items'][$itemKey])) {
            // Incrementa quantidade se já existe
            $cart['items'][$itemKey]['quantidade'] += $quantidade;
        } else {
            // Adiciona novo item
            $cart['items'][$itemKey] = [
                'produto_id' => $produtoId,
                'nome' => $produto->nome,
                'slug' => $produto->slug,
                'imagem' => $produto->imagem_principal,
                'preco_unitario' => $precoUnitario,
                'quantidade' => $quantidade,
                'variacao_id' => $variacaoId,
                'variacao_nome' => $variacaoNome,
                'observacoes' => $observacoes,
            ];
        }

        // Atualiza observações se fornecidas
        if ($observacoes !== null) {
            $cart['items'][$itemKey]['observacoes'] = $observacoes;
        }

        $this->saveCart($cart);

        return $cart['items'][$itemKey];
    }

    /**
     * Atualiza a quantidade de um item no carrinho
     */
    public function atualizarQuantidade(string $itemKey, int $quantidade): bool
    {
        $cart = $this->getCart();

        if (!isset($cart['items'][$itemKey])) {
            return false;
        }

        if ($quantidade <= 0) {
            return $this->remover($itemKey);
        }

        $cart['items'][$itemKey]['quantidade'] = $quantidade;
        $this->saveCart($cart);

        return true;
    }

    /**
     * Remove um item do carrinho
     */
    public function remover(string $itemKey): bool
    {
        $cart = $this->getCart();

        if (!isset($cart['items'][$itemKey])) {
            return false;
        }

        unset($cart['items'][$itemKey]);
        $this->saveCart($cart);

        return true;
    }

    /**
     * Limpa todo o carrinho
     */
    public function limpar(): void
    {
        Session::forget($this->getCartKey());
    }

    /**
     * Retorna todos os itens do carrinho
     */
    public function getItems(): array
    {
        return $this->getCart()['items'] ?? [];
    }

    /**
     * Conta o total de itens no carrinho
     */
    public function contarItens(): int
    {
        $items = $this->getItems();
        return array_sum(array_column($items, 'quantidade'));
    }

    /**
     * Conta o número de linhas (produtos distintos) no carrinho
     */
    public function contarLinhas(): int
    {
        return count($this->getItems());
    }

    /**
     * Calcula o subtotal do carrinho
     */
    public function getSubtotal(): float
    {
        $items = $this->getItems();
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += ($item['preco_unitario'] ?? 0) * ($item['quantidade'] ?? 1);
        }

        return $subtotal;
    }

    /**
     * Verifica se o carrinho está vazio
     */
    public function isEmpty(): bool
    {
        return $this->contarLinhas() === 0;
    }

    /**
     * Retorna resumo do carrinho para exibição
     */
    public function getResumo(): array
    {
        return [
            'items' => $this->getItems(),
            'total_itens' => $this->contarItens(),
            'total_linhas' => $this->contarLinhas(),
            'subtotal' => $this->getSubtotal(),
            'subtotal_formatado' => 'R$ ' . number_format($this->getSubtotal(), 2, ',', '.'),
            'loja_id' => $this->lojaId,
        ];
    }

    /**
     * Valida se todos os itens do carrinho ainda são válidos
     * Remove itens de produtos inativos ou indisponíveis
     */
    public function validarItens(): array
    {
        $cart = $this->getCart();
        $removidos = [];

        foreach ($cart['items'] as $itemKey => $item) {
            $produto = Produto::where('id', $item['produto_id'])
                ->where('ativo', true)
                ->whereIn('visibilidade', ['publico', 'ambos'])
                ->first();

            if (!$produto) {
                $removidos[] = $item['nome'];
                unset($cart['items'][$itemKey]);
            }
        }

        if (!empty($removidos)) {
            $this->saveCart($cart);
        }

        return $removidos;
    }
}
