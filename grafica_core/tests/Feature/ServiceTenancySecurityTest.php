<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Loja;
use App\Models\Pedido;
use App\Models\ProductionOrder;
use App\Models\ProductionPhase;
use App\Models\ProductionStep;
use App\Models\Usuario;
use App\Services\Domain\OrderService;
use App\Services\Domain\ProductionService;
use App\Services\WhatsApp\WhatsAppAccountService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * ServiceTenancySecurityTest — Segurança de Tenancy nos Services
 *
 * BLOQUEADOR DE MERGE: Qualquer falha aqui bloqueia o merge na main.
 *
 * ─── Objetivo ──────────────────────────────────────────────────────────────
 *
 * Garantir que os services do domínio NÃO executam operações sem um
 * contexto de loja válido. Services sem loja_id são a causa mais comum
 * de vazamento de dados entre tenants.
 *
 * Cada teste verifica um padrão diferente de proteção:
 *
 *   A) PROTEÇÃO POR EXCEÇÃO EXPLÍCITA
 *      O service valida loja_id na entrada e lança exceção se inválido.
 *      Padrão usado por: ProductionService
 *
 *   B) PROTEÇÃO POR CONTEXTO DE AUTENTICAÇÃO
 *      O service deriva loja_id do usuário autenticado (Auth::user()->loja_id).
 *      Se não houver usuário, lança RuntimeException.
 *      Padrão usado por: OrderService
 *
 *   C) PROTEÇÃO POR ISOLAMENTO DE QUERY
 *      O service usa WHERE loja_id = ? em todas as queries, impedindo que
 *      dados de outro tenant sejam retornados mesmo com IDs corretos.
 *      Padrão usado por: ProductionService.moverOrdem
 *
 *   D) PROTEÇÃO POR ESCOPO DE MODELO
 *      O service usa scopes de modelo (forLoja()) que garantem que queries
 *      nunca cruzam fronteiras de tenant.
 *      Padrão usado por: WhatsAppAccountService
 *
 * ─── O que NÃO é testado aqui? ─────────────────────────────────────────────
 *
 * - Isolamento a nível de Eloquent global scope → TenantIsolationTest
 * - Regras de negócio de pedido/produção → ProductionOrderApiTest
 * - Módulo WhatsApp → WhatsApp/WhatsAppModuleTest
 */
final class ServiceTenancySecurityTest extends TestCase
{
    // ─── A) ProductionService — proteção por exceção explícita ───────────────

    /**
     * ProductionService.criarOrdem() lança InvalidArgumentException quando
     * loja_id é zero ou ausente no array de dados.
     *
     * Por que isso importa:
     * Se esse guard fosse removido, uma OP poderia ser criada sem tenant,
     * associando dados de produção a loja_id = 0 (inexistente) ou null,
     * tornando o registro invisível para todos e quebrado para sempre.
     */
    public function test_production_service_rejeita_criacao_sem_loja_id(): void
    {
        /** @var ProductionService $service */
        $service = app(ProductionService::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Loja inválida');

        $service->criarOrdem([
            // loja_id ausente intencionalmente
            'pedido_id'    => 1,
            'cliente_nome' => 'Teste',
            'produto_nome' => 'Banner',
            'quantidade'   => 1,
            'prioridade'   => 'normal',
        ]);
    }

    /**
     * ProductionService.criarOrdem() lança InvalidArgumentException quando
     * loja_id é explicitamente zero.
     */
    public function test_production_service_rejeita_loja_id_zero(): void
    {
        /** @var ProductionService $service */
        $service = app(ProductionService::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Loja inválida');

        $service->criarOrdem([
            'loja_id'      => 0,
            'pedido_id'    => 1,
            'cliente_nome' => 'Teste',
            'produto_nome' => 'Adesivo',
            'quantidade'   => 1,
            'prioridade'   => 'normal',
        ]);
    }

    // ─── B) OrderService — proteção por contexto de autenticação ─────────────

    /**
     * OrderService.create() lança RuntimeException quando não há usuário
     * autenticado e nenhum contexto de tenant disponível.
     *
     * Por que isso importa:
     * Se esse guard fosse ignorado, o pedido seria criado com loja_id = null,
     * corrompendo dados e tornando o pedido inacessível para qualquer loja.
     * Num SaaS multi-tenant, pedido sem loja_id é dados órfãos — lixo que
     * pode ser acessado por qualquer tenant via queries sem scope.
     */
    public function test_order_service_rejeita_criacao_sem_usuario_autenticado(): void
    {
        Auth::logout();

        /** @var OrderService $service */
        $service = app(OrderService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Contexto de loja não identificado');

        $service->create([
            'cliente_id'    => 1,
            'status'        => Pedido::STATUS_AGUARDANDO,
            'items'         => [
                [
                    'produto_id'    => 1,
                    'description'   => 'Item Teste',
                    'quantity'      => 1,
                    'unitary_value' => 100.00,
                ],
            ],
            'delivery_type' => 'retirada',
        ], userId: 1);
    }

    // ─── C) ProductionService — proteção por isolamento de query ─────────────

    /**
     * ProductionService.moverOrdem() lança ModelNotFoundException quando
     * o orderId existe mas pertence a uma loja diferente do lojaId fornecido.
     *
     * Cenário de ataque:
     * - Loja A tem OP com ID 999
     * - Operador da Loja B chama moverOrdem(lojaB.id, 999, ...)
     * - O service DEVE rejeitar — a OP 999 não é da Loja B
     *
     * Por que isso importa:
     * Sem esse guard, um operador malicioso poderia mover ordens de produção
     * de concorrentes, causando caos operacional real.
     */
    public function test_production_service_impede_mover_ordem_de_outra_loja(): void
    {
        $lojaA = Loja::factory()->create();
        $lojaB = Loja::factory()->create();

        $userA = Usuario::factory()->paraLoja($lojaA)->create();

        // Setup de fluxo mínimo para Loja A
        $phase = ProductionPhase::create([
            'loja_id' => $lojaA->id,
            'nome'    => 'Fluxo Loja A',
            'ordem'   => 1,
            'ativo'   => true,
        ]);

        $step1 = ProductionStep::create([
            'loja_id'              => $lojaA->id,
            'production_phase_id'  => $phase->id,
            'nome'                 => 'Etapa 1',
            'ordem'                => 1,
            'ativo'                => true,
        ]);

        $step2 = ProductionStep::create([
            'loja_id'              => $lojaA->id,
            'production_phase_id'  => $phase->id,
            'nome'                 => 'Etapa 2',
            'ordem'                => 2,
            'ativo'                => true,
        ]);

        // Criar OP na Loja A diretamente (bypassa service para setup)
        $opLojaA = ProductionOrder::create([
            'loja_id'             => $lojaA->id,
            'pedido_id'           => 1,
            'production_step_id'  => $step1->id,
            'cliente_nome'        => 'Cliente Loja A',
            'produto_nome'        => 'Banner',
            'quantidade'          => 1,
            'status'              => 'em_producao',
            'status_atual'        => $step1->nome,
            'prioridade'          => 'normal',
        ]);

        /** @var ProductionService $service */
        $service = app(ProductionService::class);

        // Loja B tenta mover a OP da Loja A → DEVE ser rejeitado
        $this->expectException(ModelNotFoundException::class);

        $service->moverOrdem(
            lojaId: $lojaB->id,    // Loja B
            orderId: $opLojaA->id, // OP pertence à Loja A
            nextStepId: $step2->id,
            usuarioId: $userA->id
        );
    }

    // ─── D) WhatsAppAccountService — proteção por escopo de modelo ───────────

    /**
     * WhatsAppAccountService.onboard() cria conta WhatsApp corretamente
     * associada à loja e impede que outra loja a veja via scope forLoja().
     *
     * Por que isso importa:
     * Uma conta WhatsApp vincada à loja errada permitiria que mensagens
     * de uma loja fossem enviadas pelo número de outra — violação direta
     * das políticas da Meta e do isolamento do SaaS.
     */
    public function test_whatsapp_account_e_isolada_por_loja(): void
    {
        $lojaA = Loja::factory()->create();
        $lojaB = Loja::factory()->create();

        /** @var WhatsAppAccountService $service */
        $service = app(WhatsAppAccountService::class);

        $service->onboard($lojaA, [
            'waba_id'          => 'waba_test_' . uniqid(),
            'phone_number_id'  => 'pn_' . uniqid(),
            'phone_number'     => '+55119' . rand(10000000, 99999999),
            'display_name'     => 'Loja A WA',
            'access_token'     => 'fake-token-loja-a',
        ]);

        // Loja B não tem contas
        $contasLojaB = \App\Models\WhatsApp\WhatsAppAccount::forLoja($lojaB->id)->count();

        $this->assertSame(
            0,
            $contasLojaB,
            'CRÍTICO: Loja B enxerga conta WhatsApp da Loja A. ' .
            'Isolamento de accounts WhatsApp QUEBRADO.'
        );

        // Loja A tem exatamente 1 conta
        $contasLojaA = \App\Models\WhatsApp\WhatsAppAccount::forLoja($lojaA->id)->count();

        $this->assertSame(
            1,
            $contasLojaA,
            'A conta WhatsApp criada para Loja A deveria ser visível via forLoja().'
        );
    }
}
