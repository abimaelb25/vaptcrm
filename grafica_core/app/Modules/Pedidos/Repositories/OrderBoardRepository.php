<?php

declare(strict_types=1);

namespace App\Modules\Pedidos\Repositories;

use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;

class OrderBoardRepository
{
    public function baseQueryWithRelations()
    {
        return Pedido::query()->with(['cliente', 'responsavel', 'itens.produto', 'pagamentos']);
    }

    public function team(): Collection
    {
        return Usuario::query()
            ->whereIn('perfil', ['administrador', 'gerente', 'atendente', 'produção', 'financeiro'])
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();
    }
}
