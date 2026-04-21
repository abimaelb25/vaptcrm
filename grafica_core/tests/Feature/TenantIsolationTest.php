<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Loja;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * TenantIsolationTest — Isolamento multi-tenant por loja_id
 *
 * BLOQUEADOR DE MERGE: Se qualquer teste desta classe falhar,
 * o merge na main é impedido pelo required status check "CI / Tests".
 *
 * ─── Por que estes testes existem? ─────────────────────────────────────────
 *
 * O trait HasTenancy aplica um global scope Eloquent que filtra todas as
 * queries por loja_id. Porém, esse scope contém o guard:
 *
 *   if (app()->runningInConsole()) { return; }
 *
 * Isso significa que durante `php artisan test` (contexto CLI), o scope
 * é DESATIVADO automaticamente — comportamento intencional para migrations
 * e comandos, mas que cria um ponto cego nos testes.
 *
 * Para testar o comportamento real de produção (HTTP requests), este arquivo
 * usa reflexão para desativar temporariamente o flag `isRunningInConsole`
 * via `withHttpContext()`. Isso simula fielmente o que acontece em produção,
 * onde requests HTTP não são console.
 *
 * ─── O que é testado? ───────────────────────────────────────────────────────
 *
 *   1. Registro do global scope nos modelos críticos
 *   2. Usuário da Loja A NÃO vê dados da Loja B (query all)
 *   3. Busca por ID de registro de outra loja retorna null
 *   4. Pedidos são isolados entre lojas (core do negócio)
 *   5. withoutGlobalScopes() funciona para o SuperAdmin (uso legítimo)
 *
 * NUNCA remover, ignorar (@group, --exclude) ou simplificar estes testes.
 * Eles são a última linha de defesa contra vazamento de dados entre tenants.
 */
final class TenantIsolationTest extends TestCase
{
    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function criarLoja(string $tag): Loja
    {
        return Loja::create([
            'nome_fantasia'    => 'Loja ' . $tag,
            'slug'             => 'loja-' . $tag,
            'responsavel_nome' => 'Resp ' . $tag,
            'responsavel_email' => $tag . '@ci.test',
            'status'           => 'ativa',
        ]);
    }

    private function criarUsuario(Loja $loja, string $tag): Usuario
    {
        return Usuario::create([
            'loja_id' => $loja->id,
            'nome'    => 'User ' . $tag,
            'email'   => 'user-' . $tag . '@ci.test',
            'senha'   => Hash::make('secret'),
            'perfil'  => 'administrador',
            'ativo'   => true,
        ]);
    }

    /**
     * Executa $callback simulando contexto HTTP (não-console).
     *
     * Durante `php artisan test`, app()->runningInConsole() retorna true,
     * o que desativa o global scope de tenant. Este método usa reflexão
     * para temporariamente marcar a aplicação como NÃO console, permitindo
     * que os testes validem o comportamento real de produção.
     *
     * O estado original é sempre restaurado no bloco finally, garantindo
     * que outros testes não sejam afetados.
     *
     * @template T
     * @param  callable(): T  $callback
     * @return T
     */
    private function withHttpContext(callable $callback): mixed
    {
        $app  = $this->app;
        $prop = (new \ReflectionObject($app))->getProperty('isRunningInConsole');
        $prop->setAccessible(true);

        $original = $prop->getValue($app);
        $prop->setValue($app, false);

        try {
            return $callback();
        } finally {
            // SEMPRE restaura o estado original para não contaminar outros testes
            $prop->setValue($app, $original);
        }
    }

    // ─── Testes ──────────────────────────────────────────────────────────────

    /**
     * Confirma que o global scope 'loja' está registrado nos modelos críticos.
     *
     * Se este teste falhar: o trait HasTenancy foi removido ou modificado
     * de forma a não registrar mais o scope — isolamento multi-tenant AUSENTE.
     */
    public function test_global_scope_loja_esta_registrado_nos_modelos_criticos(): void
    {
        $clienteScopes = (new Cliente())->getGlobalScopes();
        $pedidoScopes  = (new Pedido())->getGlobalScopes();

        $this->assertArrayHasKey(
            'loja',
            $clienteScopes,
            'CRÍTICO: Global scope "loja" não encontrado em Cliente. ' .
            'Verifique o trait HasTenancy. Isolamento multi-tenant AUSENTE.'
        );

        $this->assertArrayHasKey(
            'loja',
            $pedidoScopes,
            'CRÍTICO: Global scope "loja" não encontrado em Pedido. ' .
            'Fluxo de pedidos sem proteção de tenant. Regressão CRÍTICA.'
        );
    }

    /**
     * Usuário autenticado da Loja A só enxerga Clientes da própria loja.
     * Clientes da Loja B são completamente invisíveis.
     */
    public function test_usuario_ve_apenas_clientes_da_propria_loja(): void
    {
        $tag  = uniqid();
        $lojaA = $this->criarLoja('a-' . $tag);
        $lojaB = $this->criarLoja('b-' . $tag);

        $userA = $this->criarUsuario($lojaA, 'a-' . $tag);

        // Criar registros explicitamente com loja_id
        // (o hook 'creating' só auto-preenche quando loja_id está vazio)
        Cliente::create([
            'loja_id' => $lojaA->id,
            'nome'    => 'Cliente da Loja A',
            'status'  => 'ativo',
        ]);

        Cliente::create([
            'loja_id' => $lojaB->id,
            'nome'    => 'Cliente da Loja B',
            'status'  => 'ativo',
        ]);

        $resultado = $this->withHttpContext(function () use ($userA): \Illuminate\Database\Eloquent\Collection {
            Auth::login($userA);

            return Cliente::all();
        });

        $this->assertCount(
            1,
            $resultado,
            'CRÍTICO: Usuário da Loja A está vendo ' . $resultado->count() .
            ' clientes (esperado: 1). Isolamento multi-tenant QUEBRADO.'
        );

        $this->assertSame(
            'Cliente da Loja A',
            $resultado->first()->nome,
            'O cliente retornado não pertence à Loja A.'
        );

        $this->assertSame(
            $lojaA->id,
            $resultado->first()->loja_id,
            'loja_id do registro retornado não corresponde à Loja A.'
        );
    }

    /**
     * Busca por ID de um cliente de outra loja retorna null.
     *
     * Cenário de ataque: usuário manipula o ID na URL para tentar
     * acessar registro de outro tenant. O global scope deve impedir isso.
     */
    public function test_busca_por_id_nao_retorna_registro_de_outra_loja(): void
    {
        $tag  = uniqid();
        $lojaA = $this->criarLoja('a-' . $tag);
        $lojaB = $this->criarLoja('b-' . $tag);

        $userA = $this->criarUsuario($lojaA, 'a-' . $tag);

        $clienteB = Cliente::create([
            'loja_id' => $lojaB->id,
            'nome'    => 'Cliente Exclusivo Loja B',
            'status'  => 'ativo',
        ]);

        $resultado = $this->withHttpContext(function () use ($userA, $clienteB): ?Cliente {
            Auth::login($userA);

            return Cliente::find($clienteB->id);
        });

        $this->assertNull(
            $resultado,
            'CRÍTICO: Usuário da Loja A conseguiu acessar Cliente da Loja B por ID direto! ' .
            'ID vazado: ' . $clienteB->id . '. Isolamento multi-tenant QUEBRADO.'
        );
    }

    /**
     * Pedidos são isolados entre lojas.
     *
     * Pedido é o core do negócio no VaptCRM. Vazamento de pedidos entre
     * tenants é a regressão mais grave possível. Este teste é inegociável.
     */
    public function test_pedidos_sao_isolados_entre_lojas(): void
    {
        $tag  = uniqid();
        $lojaA = $this->criarLoja('a-' . $tag);
        $lojaB = $this->criarLoja('b-' . $tag);

        $userA   = $this->criarUsuario($lojaA, 'a-' . $tag);
        $userB   = $this->criarUsuario($lojaB, 'b-' . $tag);

        $clienteA = Cliente::create(['loja_id' => $lojaA->id, 'nome' => 'CLI-A', 'status' => 'ativo']);
        $clienteB = Cliente::create(['loja_id' => $lojaB->id, 'nome' => 'CLI-B', 'status' => 'ativo']);

        Pedido::create([
            'loja_id'           => $lojaA->id,
            'cliente_id'        => $clienteA->id,
            'responsavel_id'    => $userA->id,
            'numero'            => 'PED-A-' . $tag,
            'numero_sequencial' => 1,
            'codigo_pedido'     => 'LA-26-0001',
            'status'            => Pedido::STATUS_AGUARDANDO,
            'subtotal'          => 150.00,
            'total'             => 150.00,
        ]);

        Pedido::create([
            'loja_id'           => $lojaB->id,
            'cliente_id'        => $clienteB->id,
            'responsavel_id'    => $userB->id,
            'numero'            => 'PED-B-' . $tag,
            'numero_sequencial' => 1,
            'codigo_pedido'     => 'LB-26-0001',
            'status'            => Pedido::STATUS_AGUARDANDO,
            'subtotal'          => 300.00,
            'total'             => 300.00,
        ]);

        // Verificação para userA
        $pedidosA = $this->withHttpContext(function () use ($userA): \Illuminate\Database\Eloquent\Collection {
            Auth::login($userA);

            return Pedido::all();
        });

        $this->assertCount(
            1,
            $pedidosA,
            'CRÍTICO: Usuário da Loja A vê ' . $pedidosA->count() .
            ' pedidos (esperado: 1). Pedidos de outra loja estão expostos!'
        );

        $this->assertSame($lojaA->id, $pedidosA->first()->loja_id);

        // Verificação para userB — mesmo resultado, universo invertido
        $pedidosB = $this->withHttpContext(function () use ($userB): \Illuminate\Database\Eloquent\Collection {
            Auth::login($userB);

            return Pedido::all();
        });

        $this->assertCount(
            1,
            $pedidosB,
            'CRÍTICO: Usuário da Loja B vê ' . $pedidosB->count() .
            ' pedidos (esperado: 1). Isolamento multi-tenant QUEBRADO.'
        );

        $this->assertSame($lojaB->id, $pedidosB->first()->loja_id);
    }

    /**
     * withoutGlobalScopes() expõe dados de todos os tenants.
     *
     * Este teste é a validação inversa: confirma que o bypass explícito
     * (usado pelo SuperAdmin) funciona, e que portanto o scope existe e
     * estava ativo nos testes anteriores.
     *
     * Se este teste falhar: withoutGlobalScopes() está quebrado —
     * o SuperAdmin não consegue administrar o sistema.
     */
    public function test_without_global_scopes_expoe_dados_de_todos_os_tenants(): void
    {
        $tag  = uniqid();
        $lojaA = $this->criarLoja('a-' . $tag);
        $lojaB = $this->criarLoja('b-' . $tag);

        $userA = $this->criarUsuario($lojaA, 'a-' . $tag);

        Cliente::create(['loja_id' => $lojaA->id, 'nome' => 'CLI-A', 'status' => 'ativo']);
        Cliente::create(['loja_id' => $lojaB->id, 'nome' => 'CLI-B', 'status' => 'ativo']);

        $this->withHttpContext(function () use ($userA): void {
            Auth::login($userA);

            // Com scope → apenas Loja A
            $comEscopo = Cliente::count();
            $this->assertSame(1, $comEscopo, 'Scope deveria retornar apenas 1 cliente (Loja A).');

            // Sem scope → todos os tenants (acesso administrativo explícito)
            $semEscopo = Cliente::withoutGlobalScopes()->count();
            $this->assertSame(2, $semEscopo, 'withoutGlobalScopes() deveria retornar 2 clientes (ambas as lojas).');
        });
    }
}
