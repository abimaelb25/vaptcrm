<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Loja;
use App\Models\Usuario;
use App\Services\Domain\CatalogService;
use App\Services\Domain\OrderService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeder para gerar dados de Mockup e testar funcionalidades.
 * Autoria: Abimael Borges
 * Site: https://abimaelborges.adv.br
 * Data: 2026-04-15 02:00 BRT
 */
class MockupDataSeeder extends Seeder
{
    public function __construct(
        protected CatalogService $catalogService,
        protected OrderService $orderService
    ) {}

    public function run()
    {
        // 1. Garantir existência de uma Loja e Usuário Admin
        $loja = Loja::firstOrCreate(['id' => 1], [
            'nome' => 'Gráfica Mockup',
            'slug' => 'grafica-mockup',
            'email' => 'loja@mockup.com',
            'cnpj' => '00.000.000/0001-00',
            'status' => 'ativo'
        ]);

        $admin = Usuario::firstOrCreate(['email' => 'admin@mockup.com'], [
            'nome' => 'Administrador Teste',
            'senha' => bcrypt('senha123'),
            'perfil' => 'administrador',
            'loja_id' => $loja->id,
            'ativo' => true
        ]);

        // 2. Criar Categoria de Mockup
        $categoria = Categoria::firstOrCreate(['slug' => 'cartoes-de-visita'], [
            'nome' => 'Cartões de Visita',
            'loja_id' => $loja->id,
            'ativo' => true,
            'ordem_exibicao' => 1
        ]);

        // 3. Preparar Imagem de Mockup (Simulação de Upload)
        $imagePath = 'C:\Users\User\.gemini\antigravity\brain\37e86925-f967-408f-ad52-eba7d57f272f\produto_teste_grafica_1776228998974.png';
        
        if (file_exists($imagePath)) {
            // Copiar para um local temporário que o Laravel consiga tratar como UploadedFile
            $tempPath = storage_path('app/temp_mockup.png');
            copy($imagePath, $tempPath);

            $uploadedFile = new UploadedFile(
                $tempPath,
                'mockup_card.png',
                'image/png',
                null,
                true // modo teste
            );

            // 4. Criar Produto via Service (Valida Upload e Banco)
            $produto = $this->catalogService->saveProduct([
                'nome' => 'Cartão de Visita Premium - Mockup',
                'descricao_curta' => 'Cartão de visita com acabamento luxo.',
                'descricao_completa' => 'Produto gerado automaticamente para testes de sistema.',
                'preco_base' => 150.00,
                'categoria_id' => $categoria->id,
                'loja_id' => $loja->id,
                'ativo' => true,
                'imagem_destaque' => $uploadedFile,
                'variacoes' => [
                    ['tipo_variacao' => 'Papel', 'nome_opcao' => 'Couchê 300g', 'acrescimo_venda' => 0],
                    ['tipo_variacao' => 'Acabamento', 'nome_opcao' => 'Verniz Localizado', 'acrescimo_venda' => 50.00],
                ]
            ]);

            $this->command->info("Produto mockup criado: {$produto->nome}");
        } else {
            $this->command->error("Imagem de mockup não encontrada em: {$imagePath}");
            return;
        }

        // 5. Criar Cliente de Teste
        $cliente = Cliente::firstOrCreate(['email' => 'cliente@teste.com'], [
            'nome' => 'João Silva de Oliveira',
            'telefone' => '11999999999',
            'loja_id' => $loja->id
        ]);

        // 6. Criar Pedido de Teste (Valida Kanban e Pedidos)
        $pedido = $this->orderService->create([
            'cliente_id' => $cliente->id,
            'items' => [
                [
                    'produto_id' => $produto->id,
                    'quantity' => 2,
                    'unitary_value' => 200.00, // Preço base + acabamento
                    'description' => 'Cartão de Visita Premium com Verniz Localizado'
                ]
            ],
            'shipping_value' => 15.00,
            'delivery_type' => 'entrega_local',
            'status' => Pedido::STATUS_AGUARDANDO
        ], $admin->id);

        $this->command->info("Pedido mockup criado: #{$pedido->numero}");
    }
}
