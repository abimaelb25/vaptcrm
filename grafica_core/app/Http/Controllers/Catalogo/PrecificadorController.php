<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalogo;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-10
*/

use App\Http\Controllers\Controller;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PrecificadorController extends Controller
{
    /**
     * Renderiza a Calculadora UX.
     */
    public function index(): View
    {
        // Puxa pedidos em Rascunho para colocar no Select do Modal de Adicionar ao Orçamento
        $pedidosAbertos = Pedido::with('cliente')
            ->whereIn('status', [Pedido::STATUS_RASCUNHO, Pedido::STATUS_AGUARDANDO])
            ->latest()
            ->get();

        return view('painel.catalogo.precificacao.index', [
            'pedidosAbertos' => $pedidosAbertos
        ]);
    }

    /**
     * Salva o cálculo como um Produto do Catálogo Padrão (Ex: Cartão 1000un, Banner 2x1 padrão).
     */
    public function saveToProduct(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:100'],
            'preco_venda' => ['required', 'numeric', 'min:0'],
            'descricao' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            // Salva como Produto Ativo e Disponível. Destaque desativado para nao poluir a index por padrão
            Produto::create([
                'nome' => strip_tags($dados['nome']),
                'slug' => Str::slug($dados['nome']) . '-' . rand(100, 999),
                'categoria' => strip_tags($dados['categoria']),
                'preco_base' => $dados['preco_venda'],
                'descricao_completa' => strip_tags($dados['descricao'] ?? 'Produto configurado via Módulo de Precificação.'),
                'ativo' => true,
                'destaque' => false,
                'exige_arte' => false,
                'preco_arte' => 0
            ]);

            DB::commit();

            return redirect()->route('admin.catalog.pricing.index')
                ->with('sucesso', 'Produto criado com sucesso no catálogo com base no cálculo!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('erro', 'Erro ao salvar o produto: ' . $e->getMessage());
        }
    }

    /**
     * Salva o resultado do cálculo embutindo os detalhes no "Descritivo do Item" e injetando no Orçamento.
     */
    public function linkToOrder(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'pedido_id' => ['required', 'exists:pedidos,id'],
            'memoria_calculo' => ['required', 'string', 'max:2000'],
            'preco_venda_unitario' => ['required', 'numeric', 'min:0'],
            'quantidade' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            $pedido = Pedido::findOrFail($dados['pedido_id']);

            ItemPedido::create([
                'pedido_id' => $pedido->id,
                'produto_id' => null, // Deixamos Null pos é um item de balcão (custom dimensional)
                'descricao_item' => strip_tags($dados['memoria_calculo']),
                'quantidade' => $dados['quantidade'],
                'valor_unitario' => $dados['preco_venda_unitario'],
                'valor_total' => $dados['preco_venda_unitario'] * $dados['quantidade']
            ]);

            // Atualizamos os totais do Pedido de acordo com a soma
            $novoSubtotal = $pedido->itens()->sum('valor_total');
            $pedido->update([
                'subtotal' => $novoSubtotal,
                'total' => $novoSubtotal + $pedido->taxas_adicionais + $pedido->valor_frete
            ]);

            DB::commit();

            return redirect()->route('admin.catalog.pricing.index')
                ->with('sucesso', 'Composição salva com sucesso no Protocolo ' . $pedido->numero . '!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('erro', 'Erro ao injetar item no orçamento: ' . $e->getMessage());
        }
    }
}
