<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 03:50 (correção: trial 15 dias, fonte única)
| Descrição: Serviço de provisionamento de novos inquilinos (Lojas) no SaaS.
*/

namespace App\Services\SaaS;

use App\Models\Loja;
use App\Models\Usuario;
use App\Models\SiteConfiguracao;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantProvisioningService
{
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

            // 2. Criar a Assinatura inicial (Trial de 15 dias)
            \App\Models\SaaS\Assinatura::create([
                'loja_id'       => $loja->id,
                'plano_id'      => $planoId,
                'status'        => 'trial',
                'trial_ends_at' => now()->addDays(15),
            ]);

            // 3. Atualizar trial_ends_at da Loja para manter consistência
            $loja->update(['trial_ends_at' => now()->addDays(15)]);

            // 3. Provisionar configurações de site padrão
            $this->seedDefaultSettings($loja);

            // 4. Provisionar categorias de exemplo (opcional mas recomendado para onboarding)
            $this->seedDefaultCategories($loja);

            Log::info("Nova loja provisionada com sucesso: {$loja->nome_fantasia} (ID: {$loja->id})");

            return $admin;
        });
    }

    /**
     * Semeia configurações iniciais de aparência e contato.
     */
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

    /**
     * Semeia categorias iniciais de template.
     */
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
