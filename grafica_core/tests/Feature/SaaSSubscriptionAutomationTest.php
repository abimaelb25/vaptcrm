<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Loja;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\Plano;
use App\Services\SaaS\SubscriptionSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes para automação de sincronização de assinaturas SaaS.
 * 
 * Valida:
 * - Criação automática de assinatura ao criar loja
 * - Sincronização ao atualizar plano
 * - Modo trial é respeitado
 * - Comando de reparo funciona
 * - Idempotência (sem duplicação)
 */
final class SaaSSubscriptionAutomationTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionSyncService $syncService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->syncService = app(SubscriptionSyncService::class);
    }

    public function test_criar_loja_com_plano_cria_assinatura_automaticamente(): void
    {
        $plano = Plano::create([
            'nome' => 'Teste Automático',
            'slug' => 'teste-auto-' . uniqid(),
            'preco_mensal' => 99.90,
            'version' => 1,
            'ativo' => true,
        ]);

        $loja = Loja::create([
            'nome_fantasia' => 'Loja Auto Teste',
            'slug' => 'loja-auto-' . uniqid(),
            'responsavel_nome' => 'Test',
            'responsavel_email' => 'test-' . uniqid() . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
        ]);

        // Verificar que assinatura foi criada automaticamente pelo observer
        $assinatura = Assinatura::where('loja_id', $loja->id)->first();

        $this->assertNotNull($assinatura);
        $this->assertSame($plano->id, $assinatura->plano_id);
        $this->assertSame(Assinatura::STATUS_ACTIVE, $assinatura->status);
    }

    public function test_criar_loja_trial_cria_assinatura_trial(): void
    {
        $plano = Plano::create([
            'nome' => 'Trial Plan',
            'slug' => 'trial-' . uniqid(),
            'preco_mensal' => 0,
            'version' => 1,
            'ativo' => true,
        ]);

        $trialEnds = now()->addDays(14);

        $loja = Loja::create([
            'nome_fantasia' => 'Loja Trial Teste',
            'slug' => 'loja-trial-' . uniqid(),
            'responsavel_nome' => 'Test',
            'responsavel_email' => 'trial-' . uniqid() . '@example.com',
            'status' => 'trial',
            'plano_id' => $plano->id,
            'trial_ends_at' => $trialEnds,
        ]);

        $assinatura = Assinatura::where('loja_id', $loja->id)->first();

        $this->assertNotNull($assinatura);
        $this->assertSame(Assinatura::STATUS_TRIAL, $assinatura->status);
        $this->assertTrue($assinatura->trial_ends_at->isSameDay($trialEnds));
    }

    public function test_alterar_plano_da_loja_sincroniza_assinatura(): void
    {
        $planoOriginal = Plano::create([
            'nome' => 'Original',
            'slug' => 'original-' . uniqid(),
            'preco_mensal' => 99.90,
            'version' => 1,
            'ativo' => true,
        ]);

        $planoNovo = Plano::create([
            'nome' => 'Novo',
            'slug' => 'novo-' . uniqid(),
            'preco_mensal' => 199.90,
            'version' => 1,
            'ativo' => true,
        ]);

        $loja = Loja::create([
            'nome_fantasia' => 'Loja Upgrade',
            'slug' => 'loja-upgrade-' . uniqid(),
            'responsavel_nome' => 'Test',
            'responsavel_email' => 'upgrade-' . uniqid() . '@example.com',
            'status' => 'ativa',
            'plano_id' => $planoOriginal->id,
        ]);

        $assinatura = Assinatura::where('loja_id', $loja->id)->first();
        $this->assertSame($planoOriginal->id, $assinatura->plano_id);

        // Alterar plano da loja
        $loja->update(['plano_id' => $planoNovo->id]);

        // Verificar que assinatura foi sincronizada
        $assinatura->refresh();
        $this->assertSame($planoNovo->id, $assinatura->plano_id);
    }

    public function test_criar_loja_sem_plano_nao_cria_assinatura(): void
    {
        $loja = Loja::create([
            'nome_fantasia' => 'Loja Sem Plano',
            'slug' => 'sem-plano-' . uniqid(),
            'responsavel_nome' => 'Test',
            'responsavel_email' => 'sem-plano-' . uniqid() . '@example.com',
            'status' => 'ativa',
            // plano_id não preenchido
        ]);

        $assinatura = Assinatura::where('loja_id', $loja->id)->first();
        $this->assertNull($assinatura);
    }

    public function test_sync_service_nao_duplica_assinatura(): void
    {
        $plano = Plano::create([
            'nome' => 'Sem Duplicar',
            'slug' => 'sem-dup-' . uniqid(),
            'preco_mensal' => 99.90,
            'version' => 1,
            'ativo' => true,
        ]);

        $loja = Loja::create([
            'nome_fantasia' => 'Loja Sem Duplicação',
            'slug' => 'loja-dup-' . uniqid(),
            'responsavel_nome' => 'Test',
            'responsavel_email' => 'dup-' . uniqid() . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
        ]);

        // Primeira sincronização (já feita pelo observer)
        $count1 = Assinatura::where('loja_id', $loja->id)->count();
        $this->assertSame(1, $count1);

        // Sincronizar novamente via service
        $this->syncService->syncSubscriptionForStore($loja);

        $count2 = Assinatura::where('loja_id', $loja->id)->count();
        $this->assertSame(1, $count2, 'Não deve duplicar assinatura');
    }

    public function test_comando_repair_cria_assinatura_para_orfas(): void
    {
        $plano = Plano::create([
            'nome' => 'Para Reparo',
            'slug' => 'reparo-' . uniqid(),
            'preco_mensal' => 99.90,
            'version' => 1,
            'ativo' => true,
        ]);

        // Criar loja sem disparar observer (simulando loja órfã)
        $loja = Loja::create([
            'nome_fantasia' => 'Loja Órfã para Reparo',
            'slug' => 'orfa-reparo-' . uniqid(),
            'responsavel_nome' => 'Test',
            'responsavel_email' => 'orfa-' . uniqid() . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
        ]);

        // Remover assinatura (simulando estado órfão)
        Assinatura::where('loja_id', $loja->id)->delete();

        $this->assertNull(Assinatura::where('loja_id', $loja->id)->first());

        // Executar reparo via service
        $assinatura = $this->syncService->syncSubscriptionForStore($loja);

        // Verificar que assinatura foi criada
        $this->assertNotNull($assinatura);
        $this->assertSame($loja->id, $assinatura->loja_id);
        $this->assertSame($plano->id, $assinatura->plano_id);
    }

    public function test_encontrar_lojas_orfas(): void
    {
        $plano = Plano::create([
            'nome' => 'Para Buscar Órfãs',
            'slug' => 'buscar-orfa-' . uniqid(),
            'preco_mensal' => 99.90,
            'version' => 1,
            'ativo' => true,
        ]);

        // Criar 2 lojas com plano
        $loja1 = Loja::create([
            'nome_fantasia' => 'Órfã 1',
            'slug' => 'orfa-1-' . uniqid(),
            'responsavel_nome' => 'Test',
            'responsavel_email' => 'orfa1-' . uniqid() . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
        ]);

        $loja2 = Loja::create([
            'nome_fantasia' => 'Órfã 2',
            'slug' => 'orfa-2-' . uniqid(),
            'responsavel_nome' => 'Test',
            'responsavel_email' => 'orfa2-' . uniqid() . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
        ]);

        // Remover assinaturas (simulando lojas órfãs)
        Assinatura::whereIn('loja_id', [$loja1->id, $loja2->id])->delete();

        // Procurar órfãs
        $orfas = $this->syncService->findOrphanStores();

        $this->assertGreaterThanOrEqual(2, $orfas->count());
        $ids = $orfas->pluck('id')->toArray();
        $this->assertContains($loja1->id, $ids);
        $this->assertContains($loja2->id, $ids);
    }

    public function test_validar_consistencia_detecta_orfas(): void
    {
        $plano = Plano::create([
            'nome' => 'Validação',
            'slug' => 'validacao-' . uniqid(),
            'preco_mensal' => 99.90,
            'version' => 1,
            'ativo' => true,
        ]);

        $loja = Loja::create([
            'nome_fantasia' => 'Loja para Validar',
            'slug' => 'validar-' . uniqid(),
            'responsavel_nome' => 'Test',
            'responsavel_email' => 'validar-' . uniqid() . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
        ]);

        // Remover assinatura
        Assinatura::where('loja_id', $loja->id)->delete();

        // Validar deve lançar exceção
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('nenhuma assinatura SaaS correspondente');

        $this->syncService->validateStoreHasValidSubscription($loja);
    }

    public function test_plan_snapshot_contem_informacoes_corretas(): void
    {
        $plano = Plano::create([
            'nome' => 'Com Snapshot',
            'slug' => 'snapshot-' . uniqid(),
            'preco_mensal' => 299.90,
            'version' => 2,
            'ativo' => true,
        ]);

        $loja = Loja::create([
            'nome_fantasia' => 'Loja Snapshot',
            'slug' => 'loja-snap-' . uniqid(),
            'responsavel_nome' => 'Test',
            'responsavel_email' => 'snap-' . uniqid() . '@example.com',
            'status' => 'ativa',
            'plano_id' => $plano->id,
        ]);

        $assinatura = Assinatura::where('loja_id', $loja->id)->first();

        $this->assertIsArray($assinatura->plan_snapshot);
        $this->assertSame($plano->id, $assinatura->plan_snapshot['plano_id']);
        $this->assertSame($plano->nome, $assinatura->plan_snapshot['nome']);
        $this->assertSame($plano->slug, $assinatura->plan_snapshot['slug']);
    }
}
