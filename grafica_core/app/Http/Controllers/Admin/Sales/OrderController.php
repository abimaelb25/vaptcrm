<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Sales;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderRequest;
use App\Models\Cliente;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Usuario;
use App\Modules\Pedidos\Services\OrderBoardService;
use App\Services\Domain\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        private readonly OrderBoardService $boardService,
    ) {
        $this->authorizeResource(Pedido::class, 'pedido');
    }

    public function index(Request $request): View
    {
        return view('painel.pedidos.index', $this->boardService->buildIndexPayload($request, (int) auth()->user()->loja_id));
    }

    /**
     * Retorna o mapeamento oficial de colunas do Kanban.
     */
    protected function getStatusMap(): array
    {
        return $this->boardService->statusMap();
    }

    /**
     * Coleta métricas rápidas para o topo do painel.
     */
    protected function getQuickStats(int $lojaId): array
    {
        return $this->boardService->quickStats($lojaId);
    }

    public function create(): View
    {
        return view('painel.pedidos.create', [
            'clientes' => Cliente::query()->orderBy('nome')->get(),
            'produtos' => Produto::query()->where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        try {
            // Mapeamento dos itens para o formato do Service
            $items = array_map(function ($item) {
                $produto = Produto::find($item['produto_id']);
                return [
                    'produto_id'    => $item['produto_id'],
                    'quantity'      => (int) $item['quantidade'],
                    'unitary_value' => (float) $item['valor_unitario'],
                    'description'   => 'Item: ' . $produto->nome . ($item['especificacoes'] ? "\nEspecs: " . $item['especificacoes'] : ''),
                ];
            }, $request->input('itens'));

            $data = [
                'cliente_id'        => $request->cliente_id,
                'shipping_value'    => $request->valor_frete,
                'additional_fees'   => $request->taxas_adicionais,
                'discount'          => $request->desconto,
                'coupon_code'       => $request->cupom_codigo,
                'delivery_type'     => $request->tipo_entrega,
                'delivery_deadline' => $request->prazo_entrega,
                'observations'      => $request->observacoes,
                'items'             => $items,
                'status'            => Pedido::STATUS_RASCUNHO,
            ];

            $this->orderService->create($data, auth()->id());

            return redirect()->route('admin.sales.pedidos.index')->with('sucesso', 'Pedido/Orçamento registrado com sucesso!');
        } catch (\Throwable $e) {
            return back()->with('erro', 'Falha ao registrar pedido: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Pedido $pedido): View
    {
        $pedido->load(['cliente', 'responsavel', 'itens.produto', 'historico.usuario', 'pagamentos']);
        
        return view('painel.pedidos.show', [
            'pedido'    => $pedido,
            'statusMap' => $this->getStatusMap(),
            'equipe'    => Usuario::where('ativo', true)->orderBy('nome')->get()
        ]);
    }

    public function updateStatus(Request $request, Pedido $pedido): RedirectResponse
    {
        $this->authorize('update', $pedido);
        $request->validate(['status' => 'required|string', 'descricao' => 'nullable|string']);
        
        try {
            $this->orderService->updateStatus($pedido, $request->status, $request->descricao, auth()->id());
            return back()->with('sucesso', 'Status do pedido atualizado!');
        } catch (\Throwable $e) {
            return back()->with('erro', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    public function updateKanbanStatus(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:pedidos,id',
                'status'   => 'required|string'
            ]);
            
            $pedido = Pedido::findOrFail($request->order_id);
            $this->authorize('update', $pedido);
            
            if ($pedido->status === $request->status) {
                return response()->json(['sucesso' => true]);
            }

            $this->orderService->updateStatus(
                $pedido, 
                $request->status, 
                "Movimentação via Kanban.", 
                auth()->id()
            );

            return response()->json(['sucesso' => true, 'mensagem' => 'Status atualizado com sucesso!']);
        } catch (\Throwable $e) {
            \Log::error("Erro Kanban Pedido #{$request->order_id}: " . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'sucesso' => false, 
                'mensagem' => $e->getMessage() ?: 'Erro interno no servidor (verifique logs).'
            ], 422);
        }
    }

    public function marcarPago(Request $request, Pedido $pedido): RedirectResponse
    {
        $this->authorize('marcarPago', Pedido::class);
        try {
            $method = $request->input('forma_pagamento', 'Dinheiro');
            $pedido->update(['forma_pagamento' => $method]);
            
            $this->orderService->updateStatus($pedido, Pedido::STATUS_EM_PRODUCAO, 'Pagamento manual confirmado.', auth()->id());
            
            return back()->with('sucesso', 'Venda faturada e enviada para produção!');
        } catch (\Throwable $e) {
            return back()->with('erro', 'Erro ao faturar: ' . $e->getMessage());
        }
    }

    public function downloadArte(ItemPedido $item): BinaryFileResponse
    {
        if (!$item->caminho_arte || !Storage::exists($item->caminho_arte)) {
            abort(404, 'Arquivo de arte não encontrado.');
        }

        return response()->download(Storage::path($item->caminho_arte));
    }

    public function destroy(Pedido $pedido): RedirectResponse
    {
        $this->authorize('delete', $pedido);
        $pedido->delete();
        return back()->with('sucesso', 'Pedido removido via SoftDeletes.');
    }
}
