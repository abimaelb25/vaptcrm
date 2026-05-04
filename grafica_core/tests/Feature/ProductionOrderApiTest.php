<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Loja;
use App\Models\Pedido;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderHistory;
use App\Models\ProductionPhase;
use App\Models\ProductionStep;
use App\Models\Usuario;
use App\Services\Domain\ProductionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionOrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_op_no_primeiro_passo_e_registra_historico_inicial(): void
    {
        [$service, $loja, $usuario, $pedido, $step1] = $this->bootstrapFlow();

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'usuario_id' => $usuario->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Teste',
            'produto_nome' => 'Banner 1x2',
            'quantidade' => 2,
            'prioridade' => 'normal',
        ]);

        $this->assertInstanceOf(ProductionOrder::class, $order);
        $this->assertSame($step1->id, $order->production_step_id);

        $this->assertDatabaseHas('production_order_histories', [
            'production_order_id' => $order->id,
            'etapa_origem_id' => null,
            'etapa_destino_id' => $step1->id,
            'usuario_id' => $usuario->id,
        ]);
    }

    public function test_move_op_para_proxima_etapa_valida(): void
    {
        [$service, $loja, $usuario, $pedido, $step1, $step2] = $this->bootstrapFlow();

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'usuario_id' => $usuario->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Teste',
            'produto_nome' => 'Adesivo',
            'quantidade' => 10,
            'prioridade' => 'alta',
        ]);

        $moved = $service->moverOrdem($loja->id, $order->id, $step2->id, $usuario->id, 'Indo para impressão');

        $this->assertSame($step2->id, $moved->production_step_id);
        $this->assertSame('Impressao', $moved->status_atual);

        $this->assertDatabaseHas('production_order_histories', [
            'production_order_id' => $order->id,
            'etapa_origem_id' => $step1->id,
            'etapa_destino_id' => $step2->id,
            'usuario_id' => $usuario->id,
        ]);
    }

    public function test_bloqueia_salto_de_etapa(): void
    {
        [$service, $loja, $usuario, $pedido, $step1, $step2, $step3] = $this->bootstrapFlow();

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'usuario_id' => $usuario->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Teste',
            'produto_nome' => 'Cartao',
            'quantidade' => 100,
            'prioridade' => 'urgente',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A OP só pode avançar para a próxima etapa.');

        $service->moverOrdem($loja->id, $order->id, $step3->id, $usuario->id);
    }

    public function test_registra_historico_completo_apos_movimentar(): void
    {
        [$service, $loja, $usuario, $pedido, $step1, $step2] = $this->bootstrapFlow();

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'usuario_id' => $usuario->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Teste',
            'produto_nome' => 'Faixa',
            'quantidade' => 1,
            'prioridade' => 'normal',
        ]);

        $service->moverOrdem($loja->id, $order->id, $step2->id, $usuario->id, 'Movimento de teste');

        $historyCount = ProductionOrderHistory::query()
            ->where('production_order_id', $order->id)
            ->count();

        $this->assertSame(2, $historyCount);
    }

    private function bootstrapFlow(): array
    {
        $service = app(ProductionService::class);

        $loja = Loja::query()->create([
            'nome_fantasia' => 'Loja Teste',
            'slug' => 'loja-teste-' . uniqid(),
            'responsavel_nome' => 'Responsavel',
            'responsavel_email' => 'responsavel+' . uniqid() . '@teste.com',
            'status' => 'ativa',
        ]);

        $usuario = Usuario::query()->create([
            'loja_id' => $loja->id,
            'nome' => 'Operador',
            'email' => 'operador+' . uniqid() . '@teste.com',
            'senha' => '123456',
            'perfil' => 'producao',
            'ativo' => true,
        ]);

        $cliente = Cliente::query()->create([
            'loja_id' => $loja->id,
            'nome' => 'Cliente OP',
            'email' => 'cliente+' . uniqid() . '@teste.com',
            'status' => 'ativo',
        ]);

        $pedido = Pedido::query()->create([
            'loja_id' => $loja->id,
            'cliente_id' => $cliente->id,
            'responsavel_id' => $usuario->id,
            'numero' => 'PED-' . uniqid(),
            'numero_sequencial' => 1,
            'codigo_pedido' => 'LT-26-00001',
            'status' => 'em_producao',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $phase = ProductionPhase::query()->create([
            'loja_id' => $loja->id,
            'nome' => 'Fluxo Principal',
            'ordem' => 1,
            'ativo' => true,
        ]);

        $step1 = ProductionStep::query()->create([
            'loja_id' => $loja->id,
            'production_phase_id' => $phase->id,
            'nome' => 'Preparo',
            'ordem' => 1,
            'ativo' => true,
        ]);

        $step2 = ProductionStep::query()->create([
            'loja_id' => $loja->id,
            'production_phase_id' => $phase->id,
            'nome' => 'Impressao',
            'ordem' => 2,
            'ativo' => true,
        ]);

        $step3 = ProductionStep::query()->create([
            'loja_id' => $loja->id,
            'production_phase_id' => $phase->id,
            'nome' => 'Acabamento',
            'ordem' => 3,
            'ativo' => true,
        ]);

        return [$service, $loja, $usuario, $pedido, $step1, $step2, $step3];
    }
}
