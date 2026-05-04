<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProductionOrder;
use App\Models\Usuario;

class ProductionOrderPolicy
{
    private const ALLOWED_PROFILES = [
        'super_admin',
        'administrador',
        'gerente',
        'producao',
        'produção',
        'atendente',
    ];

    public function view(Usuario $user, ProductionOrder $order): bool
    {
        return $this->isAllowed($user)
            && (int) $user->loja_id === (int) $order->loja_id;
    }

    public function move(Usuario $user, ProductionOrder $order): bool
    {
        return $this->view($user, $order);
    }

    public function create(Usuario $user): bool
    {
        return $this->isAllowed($user) && !empty($user->loja_id);
    }

    private function isAllowed(Usuario $user): bool
    {
        $perfil = mb_strtolower((string) $user->perfil);

        return $user->ativo && in_array($perfil, self::ALLOWED_PROFILES, true);
    }
}
