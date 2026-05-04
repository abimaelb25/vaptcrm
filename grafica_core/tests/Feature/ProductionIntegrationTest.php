<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Cliente;
use App\Models\Insumo;
use App\Models\Loja;
use App\Models\Pedido;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderStep;
use App\Models\ProductionOrderStepInsumo;
use App\Models\ProductionPhase;
use App\Models\ProductionStep;
use App\Models\ProductionStepInsumo;
use App\Models\Usuario;
use App\Services\Domain\ProductionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_op_com_snapshot_de_etapas(): void
    {
        [$service, $loja, , $pedido, $step1, $step2, $step3] = $this->bootstrapFlow();

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Snapshot',
            'produto_nome' => 'Banner 2x3',
            'quantidade' => 5,
        ]);

        $stages = ProductionOrderStep::where('production_order_id', $order->id)
            ->orderBy('ordem_snapshot')
            ->get();

        $this->assertCount(3, $stages);
        $this->assertSame('Preparo', $stages[0]->nome_snapshot);
        $this->assertSame('Impressao', $stages[1]->nome_snapshot);
        $this->assertSame('Acabamento', $stages[2]->nome_snapshot);
        $this->assertSame(1, $stages[0]->ordem_snapshot);
        $this->assertSame(2, (int) $stages[1]->ordem_snapshot);
        $this->assertSame('Fluxo Principal', $stages[0]->fase_snapshot);
    }

    public function test_snapshot_preserva_equipamento_vinculado(): void
    {
        [$service, $loja, , $pedido, $step1] = $this->bootstrapFlow();

        $asset = Asset::create([
            'loja_id' => $loja->id,
            'nome' => 'Impressora EC-C7000',
            'tipo' => 'impressora',
            'data_aquisicao' => '2025-01-01',
            'valor_aquisicao' => 50000,
            'vida_util_meses' => 120,
        ]);

        $step1->update(['asset_id' => $asset->id]);

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Equip',
            'produto_nome' => 'Adesivo',
            'quantidade' => 10,
        ]);

        $firstStage = ProductionOrderStep::where('production_order_id', $order->id)
            ->where('production_step_id', $step1->id)
            ->first();

        $this->assertNotNull($firstStage);
        $this->assertSame($asset->id, $firstStage->asset_id);
    }

    public function test_calcula_insumos_previstos_na_criacao_da_op(): void
    {
        [$service, $loja, , $pedido, $step1] = $this->bootstrapFlow();

        $tinta = Insumo::create([
            'loja_id' => $loja->id,
            'nome' => 'Tinta Cyan',
            'unidade_medida' => 'litro',
            'estoque_atual' => 50,
        ]);

        $papel = Insumo::create([
            'loja_id' => $loja->id,
            'nome' => 'Papel Couche A3',
            'unidade_medida' => 'folha',
            'estoque_atual' => 1000,
        ]);

        ProductionStepInsumo::create([
            'loja_id' => $loja->id,
            'production_step_id' => $step1->id,
            'insumo_id' => $tinta->id,
            'quantidade_por_unidade' => 0.05,
        ]);

        ProductionStepInsumo::create([
            'loja_id' => $loja->id,
            'production_step_id' => $step1->id,
            'insumo_id' => $papel->id,
            'quantidade_por_unidade' => 2,
        ]);

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Insumo',
            'produto_nome' => 'Folder A3',
            'quantidade' => 100,
        ]);

        $stage = ProductionOrderStep::where('production_order_id', $order->id)
            ->where('production_step_id', $step1->id)
            ->first();

        $insumos = ProductionOrderStepInsumo::where('production_order_step_id', $stage->id)->get();

        $this->assertCount(2, $insumos);

        $tintaConsumo = $insumos->firstWhere('insumo_id', $tinta->id);
        $papelConsumo = $insumos->firstWhere('insumo_id', $papel->id);

        $this->assertEquals(5.0, $tintaConsumo->quantidade_prevista);    // 0.05 * 100
        $this->assertEquals(200.0, $papelConsumo->quantidade_prevista);   // 2 * 100
        $this->assertFalse($tintaConsumo->baixa_estoque_realizada);
    }

    public function test_tempo_estimado_propagado_no_snapshot(): void
    {
        [$service, $loja, , $pedido, $step1] = $this->bootstrapFlow();

        $step1->update(['tempo_estimado_minutos' => 30]);

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Tempo',
            'produto_nome' => 'Cartao',
            'quantidade' => 50,
        ]);

        $stage = ProductionOrderStep::where('production_order_id', $order->id)
            ->where('production_step_id', $step1->id)
            ->first();

        $this->assertSame(30, $stage->tempo_estimado);
    }

    public function test_update_step_status_calcula_tempo_real(): void
    {
        [$service, $loja, $usuario, $pedido, $step1] = $this->bootstrapFlow();

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Status',
            'produto_nome' => 'Lona',
            'quantidade' => 1,
        ]);

        $stage = ProductionOrderStep::where('production_order_id', $order->id)
            ->where('production_step_id', $step1->id)
            ->first();

        $service->updateStepStatus($stage, 'em_andamento', $usuario->id);
        $stage->refresh();

        $this->assertSame('em_andamento', $stage->status);
        $this->assertNotNull($stage->data_inicio);

        $service->updateStepStatus($stage, 'concluido', $usuario->id);
        $stage->refresh();

        $this->assertSame('concluido', $stage->status);
        $this->assertNotNull($stage->data_fim);
        $this->assertNotNull($stage->tempo_real);
    }

    public function test_progresso_da_op_reflete_etapas_concluidas(): void
    {
        [$service, $loja, $usuario, $pedido, $step1, $step2, $step3] = $this->bootstrapFlow();

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Progresso',
            'produto_nome' => 'Adesivo',
            'quantidade' => 10,
        ]);

        $this->assertSame(0, $order->progresso);

        $stages = ProductionOrderStep::where('production_order_id', $order->id)
            ->orderBy('ordem_snapshot')
            ->get();

        $service->updateStepStatus($stages[0], 'concluido', $usuario->id);

        $this->assertSame(33, $order->fresh()->progresso);

        $service->updateStepStatus($stages[1], 'concluido', $usuario->id);
        $service->updateStepStatus($stages[2], 'concluido', $usuario->id);

        $this->assertSame(100, $order->fresh()->progresso);
    }

    private function bootstrapFlow(): array
    {
        $service = app(ProductionService::class);

        $loja = Loja::create([
            'nome_fantasia' => 'Loja Integração',
            'slug' => 'loja-integracao-' . uniqid(),
            'responsavel_nome' => 'Responsavel',
            'responsavel_email' => 'resp+' . uniqid() . '@teste.com',
            'status' => 'ativa',
        ]);

        $usuario = Usuario::create([
            'loja_id' => $loja->id,
            'nome' => 'Operador',
            'email' => 'op+' . uniqid() . '@teste.com',
            'senha' => '123456',
            'perfil' => 'producao',
            'ativo' => true,
        ]);

        $cliente = Cliente::create([
            'loja_id' => $loja->id,
            'nome' => 'Cliente OP',
            'email' => 'cli+' . uniqid() . '@teste.com',
            'status' => 'ativo',
        ]);

        $pedido = Pedido::create([
            'loja_id' => $loja->id,
            'cliente_id' => $cliente->id,
            'responsavel_id' => $usuario->id,
            'numero' => 'PED-' . uniqid(),
            'numero_sequencial' => 1,
            'codigo_pedido' => 'LT-26-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'status' => 'em_producao',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $phase = ProductionPhase::create([
            'loja_id' => $loja->id,
            'nome' => 'Fluxo Principal',
            'ordem' => 1,
            'ativo' => true,
        ]);

        $step1 = ProductionStep::create([
            'loja_id' => $loja->id,
            'production_phase_id' => $phase->id,
            'nome' => 'Preparo',
            'ordem' => 1,
            'ativo' => true,
        ]);

        $step2 = ProductionStep::create([
            'loja_id' => $loja->id,
            'production_phase_id' => $phase->id,
            'nome' => 'Impressao',
            'ordem' => 2,
            'ativo' => true,
        ]);

        $step3 = ProductionStep::create([
            'loja_id' => $loja->id,
            'production_phase_id' => $phase->id,
            'nome' => 'Acabamento',
            'ordem' => 3,
            'ativo' => true,
        ]);

        return [$service, $loja, $usuario, $pedido, $step1, $step2, $step3];
    }
}
