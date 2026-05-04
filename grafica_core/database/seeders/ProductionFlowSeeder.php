<?php

declare(strict_types=1);

namespace Database\Seeders;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-20
| Descrição: Popula fases padrão da indústria gráfica e vincula etapas existentes.
|            Idempotente - pode rodar múltiplas vezes sem duplicação.
*/

use App\Models\Loja;
use App\Models\ProductionPhase;
use App\Models\ProductionStep;
use Illuminate\Database\Seeder;

class ProductionFlowSeeder extends Seeder
{
    /**
     * Fases padrão da indústria gráfica.
     */
    protected array $phases = [
        ['nome' => 'Pré-produção', 'ordem' => 1],
        ['nome' => 'Produção', 'ordem' => 2],
        ['nome' => 'Pós-produção', 'ordem' => 3],
        ['nome' => 'Finalização', 'ordem' => 4],
    ];

    /**
     * Mapeamento de etapas para fases.
     * Chave: nome da fase
     * Valor: array de nomes de etapas (normalizados para lowercase na comparação)
     */
    protected array $stepMapping = [
        'Pré-produção' => [
            'tratamento do arquivo',
            'tratamento de arquivos',
            'fechamento de arquivo',
            'imposição',
            'montagem',
            'imposição / montagem',
            'gravação de matrizes',
            'gravação de matriz',
            'ctp',
            'pré-impressão',
            'pre-impressao',
            'arte',
            'design',
            'aprovação de arte',
            'revisão',
        ],
        'Produção' => [
            'impressão ec-c7000',
            'impressao ec-c7000',
            'setup',
            'acerto',
            'setup / acerto',
            'aprovação de máquina',
            'aprovacao de maquina',
            'tiragem',
            'impressão digital',
            'impressao digital',
            'impressão offset',
            'impressao offset',
            'impressão ec-c7000',
            'impressão',
            'impressao',
            'offset',
            'digital',
            'serigrafia',
            'silk',
            'sublimação',
            'sublimacao',
            'uv',
        ],
        'Pós-produção' => [
            'refile',
            'refile / corte',
            'corte',
            'enobrecimento',
            'laminação',
            'laminacao',
            'plastificação',
            'plastificacao',
            'verniz',
            'hot stamping',
            'conformação',
            'conformacao',
            'dobra',
            'vinco',
            'picote',
            'encadernação',
            'encadernacao',
            'grampo',
            'wire-o',
            'espiral',
            'costura',
            'cola',
            'colagem',
            'ilhós',
            'ilhos',
            'bastão',
            'bastao',
            'acabamento',
        ],
        'Finalização' => [
            'triagem',
            'triagem e qualidade',
            'qualidade',
            'controle de qualidade',
            'conferência',
            'conferencia',
            'contagem',
            'contagem e embalagem',
            'embalagem',
            'expedição',
            'expedicao',
            'envio',
            'despacho',
            'separação',
            'separacao',
            'entrega',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏭 Iniciando seed de fluxo de produção...');

        // Busca todas as lojas com status ativa
        $lojas = Loja::where('status', 'ativa')->get(['id']);

        if ($lojas->isEmpty()) {
            $this->command->warn('⚠️  Nenhuma loja ativa encontrada.');
            return;
        }

        $this->command->info("📦 Processando {$lojas->count()} loja(s)...");

        foreach ($lojas as $loja) {
            $lojaId = (int) $loja->id;
            $this->command->line("   → Loja ID: {$lojaId}");
            
            $this->createPhasesForLoja($lojaId);
            $this->linkStepsToPhases($lojaId);
            $this->ensureProductionHasAtLeastOneStep($lojaId);
        }

        $this->command->info('✅ Seed de fluxo de produção concluído!');
    }

    /**
     * Cria as fases padrão para uma loja.
     */
    protected function createPhasesForLoja(int $lojaId): void
    {
        foreach ($this->phases as $phaseData) {
            ProductionPhase::firstOrCreate(
                [
                    'loja_id' => $lojaId,
                    'nome' => $phaseData['nome'],
                ],
                [
                    'ordem' => $phaseData['ordem'],
                    'ativo' => true,
                ]
            );
        }

        $this->command->line("      ✓ Fases verificadas/criadas");
    }

    /**
     * Vincula etapas existentes às fases corretas.
     */
    protected function linkStepsToPhases(int $lojaId): void
    {
        // Carrega fases da loja indexadas por nome
        $phases = ProductionPhase::where('loja_id', $lojaId)
            ->pluck('id', 'nome');

        $fallbackPhaseId = (int) (ProductionPhase::where('loja_id', $lojaId)
            ->orderBy('ordem')
            ->value('id') ?? 0);

        // Reavalia todas as etapas para manter vínculo obrigatório com fase
        $steps = ProductionStep::where('loja_id', $lojaId)
            ->get();

        if ($steps->isEmpty()) {
            $this->command->line("      ✓ Nenhuma etapa para vincular");
            return;
        }

        $linked = 0;
        $fallbackLinked = 0;

        foreach ($steps as $step) {
            $phaseId = $this->findPhaseForStep($step->nome, $phases);

            if ($phaseId) {
                if ((int) $step->production_phase_id !== (int) $phaseId) {
                    $step->update(['production_phase_id' => $phaseId]);
                }
                $linked++;
                continue;
            }

            if (!$step->production_phase_id && $fallbackPhaseId > 0) {
                $step->update(['production_phase_id' => $fallbackPhaseId]);
                $fallbackLinked++;
            }
        }

        $total = $steps->count();

        $this->command->line("      ✓ Etapas mapeadas: {$linked}/{$total} | fallback aplicado: {$fallbackLinked}");
    }

    /**
     * Encontra a fase correta para uma etapa baseado no nome.
     */
    protected function findPhaseForStep(string $stepName, $phases): ?int
    {
        $normalizedName = $this->normalize($stepName);

        foreach ($this->stepMapping as $phaseName => $stepNames) {
            foreach ($stepNames as $mappedName) {
                // Verifica correspondência exata ou parcial
                if ($normalizedName === $mappedName || str_contains($normalizedName, $mappedName) || str_contains($mappedName, $normalizedName)) {
                    return $phases->get($phaseName);
                }
            }
        }

        return null;
    }

    /**
     * Normaliza string para comparação.
     */
    protected function normalize(string $value): string
    {
        // Remove acentos
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        
        // Lowercase e trim
        return mb_strtolower(trim($value));
    }

    protected function ensureProductionHasAtLeastOneStep(int $lojaId): void
    {
        $productionPhase = ProductionPhase::where('loja_id', $lojaId)
            ->where('nome', 'Produção')
            ->first();

        if (!$productionPhase) {
            return;
        }

        $activeCount = ProductionStep::where('loja_id', $lojaId)
            ->where('production_phase_id', $productionPhase->id)
            ->where('ativo', true)
            ->count();

        if ($activeCount > 0) {
            return;
        }

        ProductionStep::create([
            'loja_id' => $lojaId,
            'production_phase_id' => $productionPhase->id,
            'nome' => 'Impressão EC-C7000',
            'ordem' => 1,
            'ativo' => true,
        ]);

        $this->command->line('      ✓ Etapa padrão criada para fase Produção');
    }
}
