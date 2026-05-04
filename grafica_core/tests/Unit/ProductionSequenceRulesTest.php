<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\Loja;
use App\Models\Pedido;
use App\Models\ProductionPhase;
use App\Models\ProductionStep;
use App\Models\Usuario;
use App\Services\Domain\ProductionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSequenceRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_nao_permite_retroceder_ou_pular_no_fluxo(): void
    {
        [$service, $loja, $usuario, $pedido, $step1, $step2, $step3] = $this->bootstrapFlow();

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'usuario_id' => $usuario->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Teste',
            'produto_nome' => 'Lona',
            'quantidade' => 1,
            'prioridade' => 'normal',
        ]);

        $service->moverOrdem($loja->id, $order->id, $step2->id, $usuario->id);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A OP só pode avançar para a próxima etapa.');

        $service->moverOrdem($loja->id, $order->id, $step1->id, $usuario->id);
    }

    public function test_nao_move_op_finalizada(): void
    {
        [$service, $loja, $usuario, $pedido, $step1, $step2, $step3] = $this->bootstrapFlow();

        $order = $service->criarOrdem([
            'loja_id' => $loja->id,
            'usuario_id' => $usuario->id,
            'pedido_id' => $pedido->id,
            'cliente_nome' => 'Cliente Teste',
            'produto_nome' => 'Etiqueta',
            'quantidade' => 50,
            'prioridade' => 'normal',
        ]);

        $service->moverOrdem($loja->id, $order->id, $step2->id, $usuario->id);
        $service->moverOrdem($loja->id, $order->id, $step3->id, $usuario->id);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('OP já finalizada');

        $service->moverOrdem($loja->id, $order->id, $step3->id, $usuario->id);
    }

    private function bootstrapFlow(): array
    {
        $service = app(ProductionService::class);

        $loja = Loja::query()->create([
            'nome_fantasia' => 'Loja Unit',
            'slug' => 'loja-unit-' . uniqid(),
            'responsavel_nome' => 'Responsavel',
            'responsavel_email' => 'unit+' . uniqid() . '@teste.com',
            'status' => 'ativa',
        ]);

        $usuario = Usuario::query()->create([
            'loja_id' => $loja->id,
            'nome' => 'Operador',
            'email' => 'operador.unit+' . uniqid() . '@teste.com',
            'senha' => '123456',
            'perfil' => 'producao',
            'ativo' => true,
        ]);

        $cliente = Cliente::query()->create([
            'loja_id' => $loja->id,
            'nome' => 'Cliente Unit',
            'email' => 'cliente.unit+' . uniqid() . '@teste.com',
            'status' => 'ativo',
        ]);

        $pedido = Pedido::query()->create([
            'loja_id' => $loja->id,
            'cliente_id' => $cliente->id,
            'responsavel_id' => $usuario->id,
            'numero' => 'PED-UNIT-' . uniqid(),
            'numero_sequencial' => 1,
            'codigo_pedido' => 'UT-26-00001',
            'status' => 'em_producao',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $phase = ProductionPhase::query()->create([
            'loja_id' => $loja->id,
            'nome' => 'Fluxo Unit',
            'ordem' => 1,
            'ativo' => true,
        ]);

        $step1 = ProductionStep::query()->create([
            'loja_id' => $loja->id,
            'production_phase_id' => $phase->id,
            'nome' => 'Recebimento',
            'ordem' => 1,
            'ativo' => true,
        ]);

        $step2 = ProductionStep::query()->create([
            'loja_id' => $loja->id,
            'production_phase_id' => $phase->id,
            'nome' => 'Producao',
            'ordem' => 2,
            'ativo' => true,
        ]);

        $step3 = ProductionStep::query()->create([
            'loja_id' => $loja->id,
            'production_phase_id' => $phase->id,
            'nome' => 'Entrega',
            'ordem' => 3,
            'ativo' => true,
        ]);

        return [$service, $loja, $usuario, $pedido, $step1, $step2, $step3];
    }
}
