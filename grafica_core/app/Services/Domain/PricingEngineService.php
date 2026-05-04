<?php

declare(strict_types=1);

namespace App\Services\Domain;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Descrição: Engine matemática de apuração de custos e precificação.
*/

use App\Models\Produto;
use App\Models\LojaPrecificacaoConfig;
use App\Models\ProdutoHistoricoPreco;
use Illuminate\Support\Facades\DB;
use App\Models\Insumo;
use App\Models\ServicoProducao;

class PricingEngineService
{
    /**
     * Simula o custo com base em um payload não persistido (usado na view via POST).
     * O Payload deve vir com a estrutura da ficha: insumos[], servicos[], tempo_producao_min, perda_percentual
     */
    public function simularPayload(array $payload, int $lojaId): array
    {
        $config = LojaPrecificacaoConfig::where('loja_id', $lojaId)->first();
        if (!$config) {
            throw new \Exception('A loja não possui configuração de precificação ativa.');
        }

        $custoInsumos = 0.0;
        if (!empty($payload['insumos'])) {
            $insumosIds = array_column($payload['insumos'], 'insumo_id');
            // Valida tenant
            $insumosDB = Insumo::where('loja_id', $lojaId)->whereIn('id', $insumosIds)->get()->keyBy('id');
            
            foreach ($payload['insumos'] as $item) {
                if ($insumosDB->has($item['insumo_id'])) {
                    $insumo = $insumosDB->get($item['insumo_id']);
                    if (!(bool) $insumo->usar_na_precificacao) {
                        continue;
                    }
                    $qtd = (float) ($item['quantidade'] ?? 0);
                    $perda = (float) ($item['fator_perda'] ?? 0);
                    $qtdReal = $qtd * (1 + ($perda / 100));
                    // getCustoEfetivo() retorna custo por unidade de consumo (folha, metro, ml...)
                    // Garante que o cálculo não use o custo do pacote/rolo inteiro.
                    $custoInsumos += $qtdReal * $insumo->getCustoEfetivo();
                }
            }
        }

        $custoServicos = 0.0;
        if (!empty($payload['servicos'])) {
            $servicosIds = array_column($payload['servicos'], 'servico_producao_id');
            // Valida tenant
            $servicosDB = ServicoProducao::where('loja_id', $lojaId)->whereIn('id', $servicosIds)->get()->keyBy('id');
            
            foreach ($payload['servicos'] as $item) {
                if ($servicosDB->has($item['servico_producao_id'])) {
                    $servico = $servicosDB->get($item['servico_producao_id']);
                    $qtd = (float) ($item['quantidade'] ?? 0);
                    $fator = (float) ($item['fator_aplicacao'] ?? 1.0);
                    $custoServicos += $qtd * $servico->custo_base * $fator;
                }
            }
        }

        $tempoMin = (int) ($payload['tempo_producao_min'] ?? 0);
        $qtdBase = max(1, (int) ($payload['quantidade_base'] ?? 1));
        $perdaGeral = (float) ($payload['perda_percentual'] ?? 0);
        $margemLucro = (float) ($payload['margem_lucro'] ?? 20.0);

        return $this->executarMatematica(
            $config, 
            $custoInsumos, 
            $custoServicos, 
            $tempoMin, 
            $qtdBase, 
            $perdaGeral, 
            $margemLucro
        );
    }

    /**
     * Recalcula o custo de um produto já com Ficha Técnica salva no banco.
     */
    public function recalcularProdutoExistente(Produto $produto): array
    {
        $ficha = $produto->fichaTecnica;
        
        if (!$ficha || !$ficha->ativo) {
            throw new \Exception('Produto não possui ficha técnica ativa.');
        }

        $config = LojaPrecificacaoConfig::where('loja_id', $produto->loja_id)->first();
        if (!$config) {
            throw new \Exception('A loja não possui configuração de precificação ativa.');
        }

        $custoInsumos = 0.0;
        foreach ($ficha->insumos as $item) {
            if (!(bool) ($item->insumo->usar_na_precificacao ?? true)) {
                continue;
            }
            $qtdReal = $item->quantidade * (1 + ($item->fator_perda / 100));
            // getCustoEfetivo() retorna custo por unidade de consumo (folha, metro, ml...)
            $custoInsumos += $qtdReal * $item->insumo->getCustoEfetivo();
        }

        $custoServicos = 0.0;
        foreach ($ficha->servicos as $item) {
            $custoServicos += $item->quantidade * $item->servicoProducao->custo_base * $item->fator_aplicacao;
        }

        return $this->executarMatematica(
            $config, 
            $custoInsumos, 
            $custoServicos, 
            $ficha->tempo_producao_min, 
            $ficha->quantidade_base, 
            $ficha->perda_percentual, 
            $produto->margem_lucro > 0 ? $produto->margem_lucro : 20.0
        );
    }

    /**
     * Efetiva o recálculo no banco de dados, protegendo o modo manual.
     */
    public function efetivarRecalculo(Produto $produto, array $calculo, string $origem, ?int $userId = null): void
    {
        DB::transaction(function () use ($produto, $calculo, $origem, $userId) {
            $config = LojaPrecificacaoConfig::where('loja_id', $produto->loja_id)->first();
            
            // Verifica o rollout da loja. Se não estiver ativa a precificação dinâmica, 
            // não salvamos no banco para não sujar o fluxo atual.
            if (!$config || !$config->precificacao_dinamica_ativa) {
                return;
            }

            $precoAntigo = $produto->preco_base;
            
            // Decisão: Atualizamos custo_base, custo_producao.
            // O preco_base (Manual) NÃO é sobrescrito a menos que o modo de produto determine.
            // O preco_sugerido é sempre salvo.
            $produto->update([
                'custo_producao' => $calculo['custo_direto_total'],
                'custo_base'     => $calculo['custo_base_final'],
                'preco_sugerido' => $calculo['preco_sugerido'],
            ]);

            ProdutoHistoricoPreco::create([
                'loja_id'              => $produto->loja_id,
                'produto_id'           => $produto->id,
                'modo_precificacao'    => $produto->preco_sugerido == $produto->preco_base ? 'dinamico' : 'manual',
                'custo_base'           => $calculo['custo_base_final'],
                'preco_equilibrio'     => $calculo['preco_equilibrio'],
                'preco_sugerido'       => $calculo['preco_sugerido'],
                'preco_manual_vigente' => $precoAntigo,
                'origem_recalculo'     => $origem,
                'usuario_responsavel_id'=> $userId,
            ]);
        });
    }

    /**
     * Core matemático
     */
    private function executarMatematica(
        LojaPrecificacaoConfig $config,
        float $custoInsumos,
        float $custoServicos,
        int $tempoMin,
        int $qtdBase,
        float $perdaGeral,
        float $margemLucro
    ): array {
        $custoDiretoTotal = $custoInsumos + $custoServicos;
        $tempoHoras = $tempoMin / 60.0;
        $custoIndireto = $tempoHoras * $config->custo_fixo_hora;

        $custoBaseUnitario = ($custoDiretoTotal + $custoIndireto) / max(1, $qtdBase);
        $custoBaseFinal = $custoBaseUnitario * (1 + ($perdaGeral / 100));

        $fatorEncargos = $config->encargos_totais / 100.0;
        $divisorEquilibrio = max(0.01, (1 - $fatorEncargos));
        $precoEquilibrio = $custoBaseFinal / $divisorEquilibrio;

        $fatorLucro = $margemLucro / 100.0;
        $divisorSugerido = max(0.01, (1 - $fatorEncargos - $fatorLucro));
        $precoSugerido = $custoBaseFinal / $divisorSugerido;

        return [
            'custo_insumos' => round($custoInsumos, 4),
            'custo_servicos' => round($custoServicos, 4),
            'custo_direto_total' => round($custoDiretoTotal, 4),
            'custo_indireto' => round($custoIndireto, 4),
            'custo_base_final' => round($custoBaseFinal, 4),
            'encargos_percentual' => $config->encargos_totais,
            'margem_lucro_aplicada' => $margemLucro,
            'preco_equilibrio' => round($precoEquilibrio, 2),
            'preco_sugerido' => round($precoSugerido, 2),
            'preco_sugerido_margens' => [
                '10%' => round($custoBaseFinal / max(0.01, (1 - $fatorEncargos - 0.10)), 2),
                '20%' => round($custoBaseFinal / max(0.01, (1 - $fatorEncargos - 0.20)), 2),
                '30%' => round($custoBaseFinal / max(0.01, (1 - $fatorEncargos - 0.30)), 2),
                '40%' => round($custoBaseFinal / max(0.01, (1 - $fatorEncargos - 0.40)), 2),
            ]
        ];
    }
}
