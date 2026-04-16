<?php

declare(strict_types=1);

namespace App\Support\Menu;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data de Modificação: 15/04/2026 19:10
*/

use App\Models\Usuario;
use Illuminate\Support\Facades\Route;

class PainelMenu
{
    /**
     * Gera a estrutura de menu para o usuário baseado no seu perfil e fluxo operacional.
     */
    public static function forUser(Usuario $user): array
    {
        $instance = new self();
        $perfil = $instance->normalizeProfile($user);
        
        $menu = [];

        // 1. COMERCIAL (admin, atendente)
        $comercial = [
            'section' => 'Comercial',
            'roles' => ['admin', 'gerente', 'atendente'],
            'items' => array_filter([
                [
                    'label' => 'Visão Geral',
                    'route' => 'admin.dashboard',
                    'icon' => 'home',
                    'active_pattern' => 'admin.dashboard',
                ],
                [
                    'label' => 'Pedidos',
                    'route' => 'admin.sales.pedidos.index',
                    'icon' => 'shopping-bag',
                    'active_pattern' => 'admin.sales.pedidos.*',
                ],
                [
                    'label' => 'PDV',
                    'route' => 'admin.pos.index',
                    'icon' => 'calculator',
                    'is_pdv' => true,
                ],
                [
                    'label' => 'Clientes',
                    'route' => 'admin.sales.clientes.index',
                    'icon' => 'users',
                    'active_pattern' => 'admin.sales.clientes.*',
                ],
                [
                    'label' => 'Produtos',
                    'route' => 'admin.catalog.produtos.index',
                    'icon' => 'cube',
                    'active_pattern' => 'admin.catalog.produtos.*',
                ],
                [
                    'label' => 'Categorias',
                    'route' => 'admin.catalog.categorias.index',
                    'icon' => 'tag',
                    'active_pattern' => 'admin.catalog.categorias.*',
                ],
            ]),
        ];

        // 2. PRODUÇÃO (admin, producao)
        $producao = [
            'section' => 'Produção',
            'roles' => ['admin', 'gerente', 'producao'],
            'items' => array_filter([
                [
                    'label' => 'Chão de Fábrica',
                    'route' => 'admin.ops.production.index',
                    'icon' => 'building-office-2',
                    'active_pattern' => 'admin.ops.production.*',
                ],
                [
                    'label' => 'Configurações OPs',
                    'route' => 'admin.ops.production.settings',
                    'icon' => 'adjustments-horizontal',
                    'active_pattern' => 'admin.ops.production.settings',
                    'roles' => ['admin', 'gerente'],
                ],
            ]),
        ];

        // 3. ESTOQUE (admin, producao)
        $estoque = [
            'section' => 'Estoque',
            'roles' => ['admin', 'gerente', 'producao'],
            'items' => array_filter([
                [
                    'label' => 'Insumos',
                    'route' => 'admin.inventory.insumos.index',
                    'icon' => 'cube-transparent',
                    'active_pattern' => 'admin.inventory.insumos.*',
                ],
                [
                    'label' => 'Movimentações',
                    'route' => 'admin.inventory.movimentacoes.index',
                    'icon' => 'arrows-right-left',
                    'active_pattern' => 'admin.inventory.movimentacoes.*',
                ],
                [
                    'label' => 'Fornecedores',
                    'route' => 'admin.inventory.fornecedores.index',
                    'icon' => 'truck',
                    'active_pattern' => 'admin.inventory.fornecedores.*',
                ],
                [
                    'label' => 'Alertas',
                    'route' => 'admin.inventory.insumos.alertas',
                    'icon' => 'exclamation-triangle',
                    'active_pattern' => 'admin.inventory.insumos.alertas',
                ],
            ]),
        ];

        // 4. ATIVOS (admin, producao)
        $ativos = [
            'section' => 'Ativos',
            'roles' => ['admin', 'gerente', 'producao'],
            'items' => array_filter([
                [
                    'label' => 'Equipamentos (Ativos)',
                    'route' => 'admin.inventory.assets.index',
                    'icon' => 'wrench-screwdriver',
                    'active_pattern' => 'admin.inventory.assets.*',
                ],
            ]),
        ];

        // 5. FINANCEIRO (admin, financeiro)
        $financeiro = [
            'section' => 'Financeiro',
            'roles' => ['admin', 'gerente', 'financeiro'],
            'items' => array_filter([
                [
                    'label' => 'Resumo Financeiro',
                    'route' => 'admin.finance.index',
                    'icon' => 'banknotes',
                    'active_pattern' => 'admin.finance.index',
                ],
                [
                    'label' => 'Contas a Receber',
                    'route' => 'admin.finance.receivable',
                    'icon' => 'currency-dollar',
                    'active_pattern' => 'admin.finance.receivable*',
                ],
                [
                    'label' => 'Contas a Pagar',
                    'route' => 'admin.finance.payable',
                    'icon' => 'credit-card',
                    'active_pattern' => 'admin.finance.payable*',
                ],
                [
                    'label' => 'Extrato / Movimentos',
                    'route' => 'admin.finance.transactions.index',
                    'icon' => 'list-bullet',
                    'active_pattern' => 'admin.finance.transactions*',
                ],
                [
                    'label' => 'Caixa (Turnos)',
                    'route' => 'admin.bi.caixas.index',
                    'icon' => 'list-bullet',
                    'active_pattern' => 'admin.bi.caixas.*',
                ],
                [
                    'label' => 'Relatórios BI',
                    'route' => 'admin.bi.index',
                    'icon' => 'presentation-chart-line',
                    'active_pattern' => 'admin.bi.*',
                    'roles' => ['admin', 'gerente'],
                ],
            ]),
        ];

        // 6. EQUIPE (admin)
        $equipe = [
            'section' => 'Equipe',
            'roles' => ['admin', 'gerente'],
            'items' => array_filter([
                [
                    'label' => 'Funcionários',
                    'route' => 'admin.system.equipe.index',
                    'icon' => 'user-group',
                    'active_pattern' => 'admin.system.equipe.*',
                ],
            ]),
        ];

        // 7. SITE E MARCA (admin)
        $site = [
            'section' => 'Site e Marca',
            'roles' => ['admin'],
            'items' => array_filter([
                [
                    'label' => 'Aparência',
                    'route' => 'admin.system.aparencia.index',
                    'icon' => 'paint-brush',
                    'active_pattern' => 'admin.system.aparencia.*',
                ],
                [
                    'label' => 'Depoimentos',
                    'route' => 'admin.system.depoimentos.index',
                    'icon' => 'chat-bubble-bottom-center-text',
                    'active_pattern' => 'admin.system.depoimentos.*',
                ],
                [
                    'label' => 'Páginas Legais',
                    'route' => 'admin.system.paginas-legais.index',
                    'icon' => 'document-text',
                    'active_pattern' => 'admin.system.paginas-legais.*',
                ],
            ]),
        ];

        // 8. SISTEMA (admin)
        $sistema = [
            'section' => 'Sistema',
            'roles' => ['admin'],
            'items' => array_filter([
                [
                    'label' => 'Configurações',
                    'route' => 'admin.system.config.index',
                    'icon' => 'cog-6-tooth',
                    'active_pattern' => 'admin.system.config.*',
                ],
            ]),
        ];

        // 9. AJUDA E SUPORTE (todos)
        $ajuda = [
            'section' => 'Ajuda e Suporte',
            'roles' => ['admin', 'gerente', 'atendente', 'producao', 'financeiro', 'fallback'],
            'items' => array_filter([
                [
                    'label' => 'Central de Ajuda',
                    'route' => 'admin.support.help.index',
                    'icon' => 'video-camera',
                    'active_pattern' => 'admin.support.help.*',
                ],
                [
                    'label' => 'Meus Tickets',
                    'route' => 'admin.support.meus-tickets.index',
                    'icon' => 'lifebuoy',
                    'active_pattern' => 'admin.support.meus-tickets.*',
                ],
            ]),
        ];

        // 10. CONTA (todos)
        $conta = [
            'section' => 'Conta',
            'roles' => ['admin', 'gerente', 'atendente', 'producao', 'financeiro', 'fallback'],
            'items' => array_filter([
                [
                    'label' => 'Meu Perfil',
                    'url' => route('admin.system.equipe.show', $user->id),
                    'icon' => 'user-circle',
                    'active_pattern' => 'admin.system.equipe.show',
                ],
                [
                    'label' => 'Minha Assinatura',
                    'route' => 'admin.billing.index',
                    'icon' => 'identification',
                    'active_pattern' => 'admin.billing.*',
                    'roles' => ['admin'],
                ],
                [
                    'label' => 'Ver catálogo',
                    'url' => route('site.catalogo'),
                    'icon' => 'arrow-top-right-on-square',
                    'external' => true,
                ],
                [
                    'label' => 'Sair',
                    'route' => 'auth.sair',
                    'icon' => 'arrow-left-on-rectangle',
                    'color' => 'rose',
                ],
            ]),
        ];

        $allSections = [
            $comercial,
            $producao,
            $estoque,
            $ativos,
            $financeiro,
            $equipe,
            $site,
            $sistema,
            $ajuda,
            $conta
        ];

        foreach ($allSections as $section) {
            if ($instance->canAccessSection($perfil, $section)) {
                // Filtra os itens individuais por role também
                $filteredItems = array_filter($section['items'], function($item) use ($perfil, $instance) {
                    if (!isset($item['roles'])) return true;
                    return in_array($perfil, $item['roles']);
                });

                if (!empty($filteredItems)) {
                    $menu[] = [
                        'section' => $section['section'],
                        'items' => array_values($filteredItems)
                    ];
                }
            }
        }

        return $menu;
    }

    /**
     * Verifica se o perfil tem acesso à seção.
     */
    protected function canAccessSection(string $perfil, array $section): bool
    {
        if ($perfil === 'admin') return true;
        return in_array($perfil, $section['roles']);
    }

    /**
     * Normaliza o perfil para slugs estáveis.
     */
    public function normalizeProfile(Usuario $user): string
    {
        $perfilStr = strtolower($user->perfil ?? '');
        
        if ($user->isSuperAdmin()) {
            return 'admin';
        }

        return match (true) {
            str_contains($perfilStr, 'administrador') => 'admin',
            str_contains($perfilStr, 'gerente')      => 'gerente',
            str_contains($perfilStr, 'atendente') || str_contains($perfilStr, 'comercial') => 'atendente',
            str_contains($perfilStr, 'producao') || str_contains($perfilStr, 'produção') || str_contains($perfilStr, 'operação')  => 'producao',
            str_contains($perfilStr, 'financeiro') || str_contains($perfilStr, 'administrativo') => 'financeiro',
            default => 'fallback',
        };
    }
}
