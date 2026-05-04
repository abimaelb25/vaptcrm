<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\Insumo;
use App\Models\Loja;
use App\Models\EstoqueMovimentacao;
use App\Models\NfeImportacao;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\Plano;
use App\Models\SaaS\PlanoFeature;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class InsumoFlowTest extends TestCase
{
    public function test_store_can_redirect_to_initial_entry_flow(): void
    {
        [$user, $loja] = $this->createTenantWithInventoryFeature();

        $response = $this->actingAs($user)->post(route('admin.inventory.insumos.store'), [
            'nome' => 'Cola de teste',
            'codigo_interno' => 'COLA-001',
            'categoria' => 'Adesivos',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'caixa',
            'quantidade_por_compra' => 600,
            'quantidade_subunidades_por_compra' => 6,
            'unidade_subunidade' => 'frascos',
            'quantidade_consumo_por_subunidade' => 100,
            'controlar_estoque' => '1',
            'usar_na_precificacao' => '1',
            'estoque_minimo' => 0,
            'estoque_maximo' => '',
            'observacao' => 'Cadastro com atalho',
            'submit_action' => 'save_and_entry',
        ]);

        $insumo = Insumo::query()->where('nome', 'Cola de teste')->first();

        $this->assertNotNull($insumo);
        $this->assertSame($loja->id, $insumo->loja_id);

        $response->assertRedirect(route('admin.inventory.movimentacoes.entrada', ['insumo_id' => $insumo->id]));

        $this->assertDatabaseHas('insumos', [
            'id' => $insumo->id,
            'loja_id' => $loja->id,
            'nome' => 'Cola de teste',
        ]);
    }

    public function test_processar_ajuste_persiste_motivo_estruturado_e_diferenca(): void
    {
        [$user, $loja] = $this->createTenantWithInventoryFeature();

        $insumo = Insumo::create([
            'loja_id' => $loja->id,
            'nome' => 'Cola de silicone liquida 100ml',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'estoque_atual' => 600,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
        ]);

        $response = $this->actingAs($user)->post(route('admin.inventory.insumos.processar-ajuste', $insumo), [
            'quantidade' => 550,
            'motivo_rapido' => 'inventario_fisico',
            'detalhe_motivo' => 'Divergencia conferida no fechamento',
        ]);

        $response->assertRedirect(route('admin.inventory.insumos.index'));

        $insumo->refresh();
        $this->assertSame(550.0, (float) $insumo->estoque_atual);

        $movimentacao = EstoqueMovimentacao::query()
            ->where('insumo_id', $insumo->id)
            ->where('tipo', 'ajuste')
            ->latest('id')
            ->first();

        $this->assertNotNull($movimentacao);
        $this->assertSame(-50.0, (float) $movimentacao->quantidade);
        $this->assertStringContainsString('Motivo: Inventario fisico', (string) $movimentacao->descricao);
        $this->assertStringContainsString('Detalhe: Divergencia conferida no fechamento', (string) $movimentacao->descricao);
    }

    public function test_entrada_por_unidade_compra_converte_para_unidade_base(): void
    {
        [$user, $loja] = $this->createTenantWithInventoryFeature();

        $insumo = Insumo::create([
            'loja_id' => $loja->id,
            'nome' => 'Papel Sulfite',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'folha',
            'unidade_compra' => 'pacote',
            'quantidade_por_compra' => 40,
            'estoque_atual' => 0,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
            'custo_medio' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('admin.inventory.movimentacoes.processar-entrada'), [
            'insumo_id' => $insumo->id,
            'quantidade' => 2,
            'custo_unitario' => 58.65,
            'em_unidade_compra' => '1',
            'fornecedor_id' => null,
            'data_movimentacao' => now()->toDateTimeString(),
            'descricao' => 'Entrada de teste',
        ]);

        $response->assertRedirect(route('admin.inventory.movimentacoes.index'));

        $insumo->refresh();
        $this->assertEqualsWithDelta(80.0, (float) $insumo->estoque_atual, 0.0001);
        $this->assertEqualsWithDelta(1.46625, (float) $insumo->custo_medio, 0.000001);

        $mov = EstoqueMovimentacao::query()->where('insumo_id', $insumo->id)->latest('id')->first();
        $this->assertNotNull($mov);
        $this->assertSame('entrada', $mov->tipo);
        $this->assertEqualsWithDelta(80.0, (float) $mov->quantidade_base, 0.0001);
        $this->assertSame('entrada', (string) $mov->origem_tela);
    }

    public function test_ajuste_positivo_e_negativo_nao_recalcula_custo_medio_como_compra(): void
    {
        [$user, $loja] = $this->createTenantWithInventoryFeature();

        $insumo = Insumo::create([
            'loja_id' => $loja->id,
            'nome' => 'Cola Teste',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'estoque_atual' => 600,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
            'custo_medio' => 0.0833,
            'custo_unitario_consumo' => 0.0833,
        ]);

        $this->actingAs($user)->post(route('admin.inventory.insumos.processar-ajuste', $insumo), [
            'quantidade' => 720,
            'motivo_rapido' => 'inventario_fisico',
            'detalhe_motivo' => 'Contagem maior',
        ])->assertRedirect(route('admin.inventory.insumos.index'));

        $insumo->refresh();
        $this->assertEqualsWithDelta(720.0, (float) $insumo->estoque_atual, 0.0001);
        $this->assertEqualsWithDelta(0.0833, (float) $insumo->custo_medio, 0.000001);

        $this->actingAs($user)->post(route('admin.inventory.insumos.processar-ajuste', $insumo), [
            'quantidade' => 550,
            'motivo_rapido' => 'balanco_mensal',
            'detalhe_motivo' => 'Contagem menor',
        ])->assertRedirect(route('admin.inventory.insumos.index'));

        $insumo->refresh();
        $this->assertEqualsWithDelta(550.0, (float) $insumo->estoque_atual, 0.0001);
        $this->assertEqualsWithDelta(0.0833, (float) $insumo->custo_medio, 0.000001);
    }

    public function test_ajuste_guiado_calcula_saldo_por_embalagens_parciais(): void
    {
        [$user, $loja] = $this->createTenantWithInventoryFeature();

        $insumo = Insumo::create([
            'loja_id' => $loja->id,
            'nome' => 'Tinta Pigmentada TJet 4L',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'frasco',
            'quantidade_por_compra' => 1000,
            'estoque_atual' => 16700,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
            'custo_medio' => 0.15,
            'custo_unitario_consumo' => 0.15,
        ]);

        $response = $this->actingAs($user)->post(route('admin.inventory.insumos.processar-ajuste', $insumo), [
            'modo_ajuste' => 'guiado',
            'qtd_embalagens_contadas' => 4,
            'volume_por_embalagem' => 700,
            'motivo_rapido' => 'inventario_fisico',
            'detalhe_motivo' => 'Contagem por frascos parciais',
        ]);

        $response->assertRedirect(route('admin.inventory.insumos.index'));

        $insumo->refresh();
        $this->assertEqualsWithDelta(2800.0, (float) $insumo->estoque_atual, 0.0001);
        $this->assertEqualsWithDelta(0.15, (float) $insumo->custo_medio, 0.000001);
    }

    public function test_editar_cadastro_do_insumo_nao_altera_saldo_real(): void
    {
        [$user, $loja] = $this->createTenantWithInventoryFeature();

        $insumo = Insumo::create([
            'loja_id' => $loja->id,
            'nome' => 'Papel A4',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'folha',
            'unidade_compra' => 'pacote',
            'quantidade_por_compra' => 40,
            'estoque_atual' => 120,
            'estoque_minimo' => 10,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
            'ativo' => true,
        ]);

        $response = $this->actingAs($user)->put(route('admin.inventory.insumos.update', $insumo), [
            'nome' => 'Papel A4 Premium',
            'codigo_interno' => 'PAP-A4',
            'categoria' => 'Papel',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'folha',
            'unidade_compra' => 'pacote',
            'quantidade_por_compra' => 50,
            'quantidade_subunidades_por_compra' => '',
            'unidade_subunidade' => '',
            'quantidade_consumo_por_subunidade' => '',
            'controlar_estoque' => '1',
            'usar_na_precificacao' => '1',
            'estoque_minimo' => 5,
            'estoque_maximo' => 500,
            'ativo' => '1',
            'observacao' => 'Atualizacao estrutural',
        ]);

        $response->assertRedirect(route('admin.inventory.insumos.index'));
        $insumo->refresh();
        $this->assertEqualsWithDelta(120.0, (float) $insumo->estoque_atual, 0.0001);
    }

    public function test_isolamento_por_loja_no_processamento_de_entrada(): void
    {
        [$userLoja1, $loja1] = $this->createTenantWithInventoryFeature();
        [, $loja2] = $this->createTenantWithInventoryFeature();

        $insumoOutraLoja = Insumo::create([
            'loja_id' => $loja2->id,
            'nome' => 'Insumo Loja 2',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'estoque_atual' => 0,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
        ]);

        $response = $this->actingAs($userLoja1)->post(route('admin.inventory.movimentacoes.processar-entrada'), [
            'insumo_id' => $insumoOutraLoja->id,
            'quantidade' => 10,
            'custo_unitario' => 5,
            'em_unidade_compra' => '0',
            'fornecedor_id' => null,
            'data_movimentacao' => now()->toDateTimeString(),
            'descricao' => 'Tentativa invalida',
        ]);

        $response->assertSessionHasErrors('insumo_id');

        $this->assertDatabaseMissing('estoque_movimentacoes', [
            'insumo_id' => $insumoOutraLoja->id,
            'descricao' => 'Tentativa invalida',
        ]);

        $insumoOutraLoja->refresh();
        $this->assertEqualsWithDelta(0.0, (float) $insumoOutraLoja->estoque_atual, 0.0001);
    }

    public function test_admin_can_permanently_delete_insumo_without_movements(): void
    {
        [$user, $loja] = $this->createTenantWithInventoryFeature();

        $insumo = Insumo::create([
            'loja_id' => $loja->id,
            'nome' => 'Insumo Excluivel',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'estoque_atual' => 0,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
            'ativo' => true,
        ]);

        $response = $this->actingAs($user)->delete(route('admin.inventory.insumos.destroy', $insumo));

        $response->assertRedirect(route('admin.inventory.insumos.index'));
        $response->assertSessionHas('sucesso', 'Insumo excluido permanentemente.');

        $this->assertDatabaseMissing('insumos', [
            'id' => $insumo->id,
        ]);
    }

    public function test_confirmacao_nfe_exige_unidade_de_estoque_explicita_ao_criar_item(): void
    {
        [$user, $loja] = $this->createTenantWithInventoryFeature();

        $importacao = NfeImportacao::create([
            'loja_id' => $loja->id,
            'usuario_id' => $user->id,
            'chave_nfe' => 'NFE-TESTE-UNIDADE-EXPLICITA',
            'numero' => '123',
            'serie' => '1',
            'data_emissao' => now(),
            'valor_total' => 45.90,
            'xml_path' => 'nfe-importacoes/teste.xml',
            'status' => 'preview',
            'payload_json' => [
                'chave_nfe' => 'NFE-TESTE-UNIDADE-EXPLICITA',
                'numero' => '123',
                'serie' => '1',
                'valor_total' => 45.90,
                'itens' => [[
                    'numero_item' => 1,
                    'descricao' => 'Papel fotografico',
                    'codigo_fornecedor' => 'PAP-1',
                    'unidade' => 'UNID',
                    'quantidade' => 1,
                    'valor_unitario' => 45.90,
                    'valor_total' => 45.90,
                ]],
            ],
            'alertas_json' => [],
        ]);

        $response = $this->actingAs($user)
            ->from(route('admin.inventory.nfe-importacao.show', $importacao))
            ->post(route('admin.inventory.nfe-importacao.confirmar', $importacao), [
                'valor_total_nota' => 45.90,
                'items' => [[
                    'acao' => 'criar',
                    'tipo_item_operacional' => 'consumivel',
                    'tratamento_financeiro' => 'custo_proprio',
                    'valor_financeiro_alocado' => 45.90,
                    'novo_nome' => 'Papel fotografico 10x15',
                    'categoria' => 'Papel',
                    'unidade_medida' => '',
                    'unidade_compra' => 'UNID',
                    'quantidade_por_compra' => 100,
                    'controlar_estoque' => '1',
                    'usar_na_precificacao' => '1',
                ]],
            ]);

        $response->assertRedirect(route('admin.inventory.nfe-importacao.show', $importacao));
        $response->assertSessionHasErrors(['items.0.unidade_medida']);
    }

    private function createTenantWithInventoryFeature(): array
    {
        $suffix = (string) now()->timestamp . random_int(1000, 9999);

        $plano = Plano::create([
            'nome' => 'Plano Estoque ' . $suffix,
            'slug' => 'plano-estoque-' . $suffix,
            'preco_mensal' => 99.90,
            'version' => 1,
            'ativo' => true,
        ]);

        PlanoFeature::create([
            'plano_id' => $plano->id,
            'feature_key' => 'modulo_estoque',
            'enabled' => true,
        ]);

        $loja = Loja::create([
            'nome_fantasia' => 'Loja Estoque ' . $suffix,
            'slug' => 'loja-estoque-' . $suffix,
            'responsavel_nome' => 'Responsavel Teste',
            'responsavel_email' => 'resp-estoque-' . $suffix . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
            'storage_limit_mb' => 1024,
            'storage_used_bytes' => 0,
        ]);

        $user = Usuario::create([
            'loja_id' => $loja->id,
            'nome' => 'Usuario Estoque ' . $suffix,
            'email' => 'user-estoque-' . $suffix . '@example.com',
            'senha' => Hash::make('secret123'),
            'perfil' => 'administrador',
            'ativo' => true,
        ]);

        Assinatura::create([
            'loja_id' => $loja->id,
            'plano_id' => $plano->id,
            'status' => Assinatura::STATUS_ACTIVE,
            'plan_version' => 1,
            'plan_snapshot' => [
                'plano_id' => $plano->id,
                'nome' => $plano->nome,
                'slug' => $plano->slug,
                'version' => 1,
            ],
            'billing_cycle' => Assinatura::BILLING_MONTHLY,
            'next_billing_at' => now()->addMonth(),
        ]);

        return [$user, $loja];
    }
}