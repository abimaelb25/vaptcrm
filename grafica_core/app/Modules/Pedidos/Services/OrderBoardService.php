<?php

declare(strict_types=1);

namespace App\Modules\Pedidos\Services;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Modules\Pedidos\Repositories\OrderBoardRepository;
use Illuminate\Http\Request;

class OrderBoardService
{
    public function __construct(
        private readonly OrderBoardRepository $repository
    ) {
    }

    public function buildIndexPayload(Request $request, int $lojaId): array
    {
        $query = $this->repository->baseQueryWithRelations();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->cliente . '%');
            })->orWhere('numero', 'like', '%' . $request->cliente . '%');
        }

        if ($request->filled('responsavel_id')) {
            $query->where('responsavel_id', $request->responsavel_id);
        }

        if ($request->has('online')) {
            $query->where('origem', 'online');
        } else {
            $query->whereIn('origem', ['interno', 'orcamento_convertido', 'pdv']);
        }

        if (!$request->filled('status')) {
            $query->where('status', '!=', Pedido::STATUS_CANCELADO);
        }

        $pedidosAgrupados = $query->latest()->get()->groupBy('status');
        $statusMap = $this->statusMap();

        $columns = [];
        foreach ($statusMap as $slug => $conf) {
            $columns[] = [
                'slug' => $slug,
                'label' => $conf['label'],
                'color' => $conf['color'],
                'orders' => $pedidosAgrupados->get($slug, collect()),
            ];
        }

        return [
            'columns' => $columns,
            'equipe' => $this->repository->team(),
            'titulo' => $request->has('online') ? 'Pedidos E-commerce' : 'Painel Kanban de Pedidos',
            'estatisticas' => $this->quickStats($lojaId),
        ];
    }

    public function statusMap(): array
    {
        return [
            Pedido::STATUS_RASCUNHO => ['label' => 'Criando Arte', 'color' => 'text-pink-500 border-pink-500', 'icon' => 'fa-palette'],
            Pedido::STATUS_AGUARDANDO => ['label' => 'Em Aberto', 'color' => 'text-yellow-500 border-yellow-500', 'icon' => 'fa-clock'],
            Pedido::STATUS_EM_PRODUCAO => ['label' => 'Em Produção', 'color' => 'text-purple-600 border-purple-600', 'icon' => 'fa-print'],
            Pedido::STATUS_PRONTO => ['label' => 'Aguardando Retirada', 'color' => 'text-teal-500 border-teal-500', 'icon' => 'fa-box'],
            Pedido::STATUS_EM_TRANSPORTE => ['label' => 'Em Transporte', 'color' => 'text-blue-500 border-blue-500', 'icon' => 'fa-truck'],
            Pedido::STATUS_ENTREGUE => ['label' => 'Entregue', 'color' => 'text-emerald-500 border-emerald-500', 'icon' => 'fa-check-double'],
            Pedido::STATUS_AGUARDANDO_PAGAMENTO => ['label' => 'Aguardando Pagamento', 'color' => 'text-red-500 border-red-500', 'icon' => 'fa-dollar-sign'],
        ];
    }

    public function quickStats(int $lojaId): array
    {
        return [
            'clientes' => Cliente::where('loja_id', $lojaId)->count(),
            'orcamentos' => Pedido::where('loja_id', $lojaId)->where('status', Pedido::STATUS_RASCUNHO)->count(),
            'pedidos' => Pedido::where('loja_id', $lojaId)->count(),
            'entregues' => Pedido::where('loja_id', $lojaId)->where('status', Pedido::STATUS_ENTREGUE)->count(),
        ];
    }
}
