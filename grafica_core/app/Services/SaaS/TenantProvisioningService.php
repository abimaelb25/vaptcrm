<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Serviço de provisionamento de novos inquilinos (Lojas) no SaaS.
*/

namespace App\Services\SaaS;

use App\Models\Loja;
use App\Models\Usuario;
use App\Models\SiteConfiguracao;
use App\Models\Categoria;
use App\Services\System\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantProvisioningService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected CommercialSubscriptionService $commercialSubscriptionService,
    ) {}

    /**
     * Provisiona uma nova loja com dados básicos e seu primeiro administrador.
     */
    public function provision(Loja $loja, array $adminData, int $planoId): Usuario
    {
        return DB::transaction(function () use ($loja, $adminData, $planoId) {
            // 1. Criar o usuário administrador vinculado à loja
            $admin = Usuario::create(array_merge($adminData, [
                'loja_id' => $loja->id,
                'perfil'  => 'administrador',
                'ativo'   => true,
            ]));

            // 2. Garantir assinatura comercial + trial automático (centralizado)
            $assinatura = $this->commercialSubscriptionService->ensureTrialForNewStore($loja);

            // 3. Criar o registro de Funcionário (Employee) para o administrador
            \App\Models\Employee::create([
                'loja_id' => $loja->id,
                'user_id' => $admin->id,
                'nome_completo' => $admin->nome,
                'status_funcional' => 'ativo',
                'cargo_interno' => 'Proprietário',
                'data_admissao' => now(),
            ]);

            // 4. Atualizar trial_ends_at da Loja para manter consistência
            if ($assinatura->trial_ends_at) {
                $loja->update(['trial_ends_at' => $assinatura->trial_ends_at]);
            }

            // 5. Provisionar estrutura de Catálogo e Site
            $this->seedDefaultSettings($loja);
            $this->seedDefaultCategories($loja);
            $this->seedDemoData($loja);

            Log::info("Nova loja provisionada com sucesso: {$loja->nome_fantasia} (ID: {$loja->id})");

            // Dispara e-mail de Boas Vindas via camada de serviço
            $plano = \App\Models\SaaS\Plano::find($planoId);
            if ($plano) {
                $this->notificationService->notifyOnboarding($loja, $admin, $plano);
            }

            return $admin;
        });
    }

    private function seedDemoData(Loja $loja): void
    {
        \App\Models\Banner::create([
            'loja_id' => $loja->id,
            'titulo' => 'Bem-vindo à nossa nova loja!',
            'subtitulo' => 'Confira nossos produtos de alta qualidade.',
            'imagem' => 'banners/default_welcome.jpg',
            'ativo' => true,
            'ordem' => 1,
        ]);

        $categoria = Categoria::where('loja_id', $loja->id)->first();
        if ($categoria) {
            \App\Models\Produto::create([
                'loja_id' => $loja->id,
                'categoria_id' => $categoria->id,
                'categoria' => $categoria->nome,
                'nome' => 'Cartão de Visita - Premium',
                'slug' => 'cartao-de-visita-premium-' . $loja->id,
                'descricao_curta' => 'Impressão em couché 300g com verniz localizado.',
                'preco_base' => 45.00,
                'ativo' => true,
                'visibilidade' => 'ambos',
                'modelo_cadastro' => 'simples',
            ]);
        }
    }

    private function seedDefaultSettings(Loja $loja): void
    {
        $defaults = [
            ['chave' => 'empresa_nome', 'valor' => $loja->nome_fantasia, 'tipo' => 'texto'],
            ['chave' => 'empresa_email', 'valor' => $loja->responsavel_email, 'tipo' => 'texto'],
            ['chave' => 'empresa_telefone', 'valor' => $loja->responsavel_whatsapp ?? '', 'tipo' => 'texto'],
            ['chave' => 'empresa_whatsapp', 'valor' => $loja->responsavel_whatsapp ?? '', 'tipo' => 'texto'],
            ['chave' => 'empresa_endereco', 'valor' => 'Endereço da sua loja', 'tipo' => 'texto'],
            ['chave' => 'aparencia_cor_primaria', 'valor' => '#FF7A00', 'tipo' => 'cor'],
            ['chave' => 'aparencia_cor_secundaria', 'valor' => '#1E293B', 'tipo' => 'cor'],
            ['chave' => 'aparencia_rodape_texto', 'valor' => 'Oferecemos soluções gráficas de alta qualidade com agilidade e compromisso.', 'tipo' => 'texto'],
            ['chave' => 'aparencia_modo', 'valor' => 'claro', 'tipo' => 'texto'],
        ];

        foreach ($defaults as $config) {
            SiteConfiguracao::create(array_merge($config, ['loja_id' => $loja->id]));
        }
    }

    private function seedDefaultCategories(Loja $loja): void
    {
        $categories = [
            ['nome' => 'Papelaria Personalizada', 'slug' => 'papelaria-personalizada', 'ativo' => true],
            ['nome' => 'Banners e Lonas', 'slug' => 'banners-e-lonas', 'ativo' => true],
            ['nome' => 'Cartões de Visita', 'slug' => 'cartoes-de-visita', 'ativo' => true],
        ];

        foreach ($categories as $cat) {
            Categoria::create(array_merge($cat, ['loja_id' => $loja->id]));
        }
    }
}
