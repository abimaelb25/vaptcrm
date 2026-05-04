<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:20
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class Insumo extends Model
{
    use SoftDeletes, HasTenancy;

    public const TIPOS_ITEM_OPERACIONAL = [
        'consumivel',
        'embalagem',
        'componente',
        'apoio',
        'ignorado',
    ];

    protected $fillable = [
        'loja_id',
        'nome',
        'codigo_interno',
        'categoria',
        'tipo_item_operacional',
        'unidade_medida',
        'unidade_compra',
        'quantidade_por_compra',
        'quantidade_subunidades_por_compra',
        'unidade_subunidade',
        'quantidade_consumo_por_subunidade',
        'controlar_estoque',
        'usar_na_precificacao',
        'estoque_atual',
        'estoque_minimo',
        'estoque_maximo',
        'custo_medio',
        'ultimo_custo',
        'custo_unitario_consumo',
        'ativo',
        'pode_ser_excluido',
        'inativado_em',
        'inativado_por_usuario_id',
        'motivo_inativacao',
        'observacao',
    ];

    protected $casts = [
        'estoque_atual'           => 'float',
        'estoque_minimo'          => 'float',
        'estoque_maximo'          => 'float',
        'custo_medio'             => 'float',
        'ultimo_custo'            => 'float',
        'custo_unitario_consumo'  => 'float',
        'quantidade_por_compra'                   => 'float',
        'quantidade_subunidades_por_compra'      => 'float',
        'quantidade_consumo_por_subunidade'      => 'float',
        'controlar_estoque'                      => 'boolean',
        'usar_na_precificacao'    => 'boolean',
        'ativo'                   => 'boolean',
    ];

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(EstoqueMovimentacao::class);
    }

    public function documentosFiscaisItens(): HasMany
    {
        return $this->hasMany(DocumentoFiscalEntradaItem::class, 'insumo_id');
    }

    public function mapeamentosFornecedores(): HasMany
    {
        return $this->hasMany(FornecedorProdutoMapeamento::class, 'insumo_id');
    }

    /**
     * Retorna se este insumo possui conversão entre unidade de compra e unidade de consumo.
     * Ex: compra em "pacote" de 500 "folhas".
     */
    public function temConversaoUnidade(): bool
    {
        if ($this->temDoisNiveisConversao()) {
            return true;
        }

        // Compatibilidade legada: cadastros inválidos onde compra e subunidade
        // representam o mesmo conceito (ex: frasco/frascos). Nesses casos,
        // usamos quantidade_consumo_por_subunidade como fator simples.
        if ($this->unidadeCompraEquivaleSubunidade()
            && ((float) ($this->quantidade_consumo_por_subunidade ?? 0)) > 0.0) {
            return true;
        }

        return !empty($this->unidade_compra)
            && ((float) $this->quantidade_por_compra) > 1.0;
    }

    /**
     * Retorna se este insumo possui conversão em DOIS NÍVEIS de embalagem.
     * Ex: 1 caixa → 6 frascos → 100 ml cada.
     * Requer que unidade_compra e quantidade_por_compra já estejam definidos (um nível)
     * e que os três campos de subunidade estejam todos preenchidos.
     */
    public function temDoisNiveisConversao(): bool
    {
        if ($this->unidadeCompraEquivaleSubunidade()) {
            return false;
        }

        return !empty($this->unidade_compra)
            && !empty($this->unidade_subunidade)
            && ((float) ($this->quantidade_subunidades_por_compra ?? 0)) > 0.0
            && ((float) ($this->quantidade_consumo_por_subunidade ?? 0)) > 0.0;
    }

    /**
     * Retorna o fator total de conversão entre a unidade de compra e a unidade final de consumo.
     *
     * Um nível : fator = quantidade_por_compra
     *             Ex: 1 pacote → 500 folhas  → fator = 500
     *
     * Dois níveis: fator = quantidade_subunidades_por_compra × quantidade_consumo_por_subunidade
     *             Ex: 1 caixa → 6 frascos × 100 ml → fator = 600
     *
     * Protegido contra divisão por zero via max(0.0001, ...).
     */
    public function getFatorTotalConversao(): float
    {
        if ($this->unidadeCompraEquivaleSubunidade()) {
            return max(0.0001, (float) ($this->quantidade_consumo_por_subunidade ?? 1));
        }

        if ($this->temDoisNiveisConversao()) {
            $sub     = max(0.0001, (float) $this->quantidade_subunidades_por_compra);
            $consumo = max(0.0001, (float) $this->quantidade_consumo_por_subunidade);
            return $sub * $consumo;
        }

        return max(0.0001, (float) ($this->quantidade_por_compra ?? 1));
    }

    /**
     * Calcula o custo por subunidade intermediária dado o valor total de compra.
     * NOTA: valorCompra deve ser o custo da UNIDADE DE COMPRA (caixa), não do consumo.
     * Para obter a partir do custo de consumo armazenado, use getCustoPorSubunidadeEfetivo().
     * Retorna 0 se não houver dois níveis.
     */
    public function getCustoPorSubunidade(float $valorCompra): float
    {
        if (!$this->temDoisNiveisConversao()) {
            return 0.0;
        }

        $sub = max(0.0001, (float) $this->quantidade_subunidades_por_compra);
        return $valorCompra / $sub;
    }

    /**
     * Custo por subunidade (frasco) calculado a partir do custo de consumo já armazenado.
     *
     * O InventoryService grava custo_unitario_consumo em unidade FINAL (ml).
     * Para obter o custo por frasco: custo_ml × quantidade_ml_por_frasco.
     *
     * Exemplo: custo_ml = 0,0833 × 100 ml/frasco = R$ 8,33/frasco
     */
    public function getCustoPorSubunidadeEfetivo(): float
    {
        if (!$this->temDoisNiveisConversao()) {
            return 0.0;
        }

        $consumoPorSub = max(0.0001, (float) $this->quantidade_consumo_por_subunidade);
        return $this->getCustoEfetivo() * $consumoPorSub;
    }

    /**
     * Custo por unidade de compra (caixa) calculado a partir do custo de consumo já armazenado.
     *
     * Exemplo: custo_ml = 0,0833 × 600 ml/caixa = R$ 49,98/caixa
     */
    public function getCustoPorUnidadeCompraEfetivo(): float
    {
        if (!$this->temConversaoUnidade()) {
            return $this->getCustoEfetivo();
        }

        return $this->getCustoEfetivo() * $this->getFatorTotalConversao();
    }

    /**
     * Retorna o custo efetivo por unidade de consumo.
     * Usado pela engine de precificação e na ficha técnica de produtos.
     *
     * Se houver conversão, retorna custo_unitario_consumo (custo por folha, metro, etc.).
     * Caso contrário, retorna custo_medio (equivalente — compra e consumo são a mesma unidade).
     */
    public function getCustoEfetivo(): float
    {
        if (!$this->temConversaoUnidade()) {
            return (float) $this->custo_medio;
        }

        // Com dois níveis de conversão (caixa → frasco → ml):
        // O InventoryService grava custo_medio e custo_unitario_consumo
        // usando getFatorTotalConversao() da ÉPOCA da entrada. Se o insumo
        // foi criado com 1 nível (fator = quantidade_por_compra = 6),
        // o custo gravado fica no nível de frasco (49.98/6 = 8.33).
        // Ao ativar o segundo nível, o fator real passa a ser 600,
        // mas o custo no banco não foi reprocessado.
        //
        // Solução: reconstruir o custo de compra a partir do dado armazenado
        // e dividir pelo fator total real atual.
        if ($this->temDoisNiveisConversao()) {
            $custoRef = ($this->custo_unitario_consumo !== null)
                ? (float) $this->custo_unitario_consumo
                : (float) $this->custo_medio;

            if ($custoRef <= 0) {
                return 0.0;
            }

            $qtdCompra = (float) ($this->quantidade_por_compra ?? 1);
            $qtdSub = (float) ($this->quantidade_subunidades_por_compra ?? 1);
            $qtdConsumoPorSub = max(0.0001, (float) ($this->quantidade_consumo_por_subunidade ?? 1));
            $fatorTotal = $this->getFatorTotalConversao(); // sub × consumoPorSub

            // Cenário legado: insumo migrou de 1 nível para 2 níveis.
            // O InventoryService gravou custo no nível de subunidade (frasco),
            // usando fator = quantidade_por_compra (que coincide com subunidades).
            // Detectamos isso quando quantidade_por_compra ≈ quantidade_subunidades_por_compra
            // e ambos são > 1 (indicando que o campo nunca foi atualizado para o fator total).
            // 
            // Adicionalmente, em alguns casos o campo quantidade_por_compra
            // foi preenchido com o valor de consumo (ex: 100) antes da ativação do segundo nível.
            $legadoMigrado = $qtdCompra > 1.0
                && (abs($qtdCompra - $qtdSub) < 0.01 || abs($qtdCompra - $qtdConsumoPorSub) < 0.01)
                && $fatorTotal > $qtdCompra + 0.01;

            if ($legadoMigrado) {
                // custoRef está em nível de subunidade → dividir por consumo por subunidade
                return $custoRef / $qtdConsumoPorSub;
            }

            // Cenário normal: custo já está no nível final de consumo (ml).
            // Pode ser:
            // - Insumo criado com 2 níveis desde o início (qtdCompra = 1 ou fatorTotal)
            // - Insumo que teve o custo re-normalizado pelo controller
            return $custoRef;
        }

        // Um nível simples: usa custo_unitario_consumo se disponível
        if ($this->custo_unitario_consumo !== null) {
            return (float) $this->custo_unitario_consumo;
        }

        // Fallback legado: alguns cadastros antigos guardaram custo_medio no nível
        // de COMPRA (pacote/rolo/caixa) sem preencher custo_unitario_consumo.
        // Para manter coerência de estoque e precificação, normaliza para o nível
        // de consumo (folha/ml/metro) dividindo pelo fator real de conversão.
        // Isso cobre tanto conversão simples quanto fallback legado com
        // unidade_compra equivalente à subunidade (ex: frasco/frascos).
        $fatorConversao = max(0.0001, (float) $this->getFatorTotalConversao());
        if ($fatorConversao > 1.0) {
            return (float) $this->custo_medio / $fatorConversao;
        }

        return (float) $this->custo_medio;
    }

    /**
     * Retorna custos já resolvidos em semântica de domínio para UI.
     *
     * - custo_por_unidade_consumo: custo por unidade operacional (folha/ml/metro)
     * - custo_por_unidade_compra: custo por unidade de compra (pacote/caixa/rolo)
     * - custo_por_subunidade: custo por unidade interna quando houver dois níveis
     * - fonte_base_tipo: indica se a base veio de consumo direto ou fallback legado
     */
    public function getResumoConversaoCustos(): array
    {
        $temConversao = $this->temConversaoUnidade();
        $temDoisNiveis = $this->temDoisNiveisConversao();
        $configuracaoCompraInvalida = $this->unidadeCompraEquivaleSubunidade();

        $custoConsumo = $this->getCustoEfetivo();
        $custoCompra = $temConversao
            ? $this->getCustoPorUnidadeCompraEfetivo()
            : $custoConsumo;

        $custoSubunidade = $temDoisNiveis
            ? $this->getCustoPorSubunidadeEfetivo()
            : null;

        $fonteBaseTipo = 'consumo';
        $fonteBaseValor = $this->custo_unitario_consumo !== null
            ? (float) $this->custo_unitario_consumo
            : (float) $this->custo_medio;

        if ($temConversao && $this->custo_unitario_consumo === null) {
            $fonteBaseTipo = 'compra_legacy_normalizada';
            $fonteBaseValor = (float) $this->custo_medio;
        }

        return [
            'tem_conversao' => $temConversao,
            'tem_dois_niveis' => $temDoisNiveis,
            'configuracao_compra_invalida' => $configuracaoCompraInvalida,
            'quantidade_base_por_unidade_compra' => $temConversao ? (float) $this->getFatorTotalConversao() : 1.0,
            'custo_por_unidade_base' => (float) $custoConsumo,
            'custo_por_unidade_consumo' => (float) $custoConsumo,
            'custo_por_unidade_compra' => (float) $custoCompra,
            'custo_por_subunidade' => $custoSubunidade !== null ? (float) $custoSubunidade : null,
            'fonte_base_tipo' => $fonteBaseTipo,
            'fonte_base_valor' => (float) $fonteBaseValor,
            'unidade_consumo' => (string) $this->unidade_medida,
            'unidade_compra' => (string) $this->unidade_compra_display,
            'unidade_subunidade' => (string) ($this->unidade_subunidade ?? ''),
        ];
    }

    public function unidadeCompraEquivaleSubunidade(): bool
    {
        if (empty($this->unidade_compra) || empty($this->unidade_subunidade)) {
            return false;
        }

        return $this->normalizarTokenUnidade($this->unidade_compra)
            === $this->normalizarTokenUnidade($this->unidade_subunidade);
    }

    private function normalizarTokenUnidade(string $value): string
    {
        $token = trim(mb_strtolower($value));

        $token = strtr($token, [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'ê' => 'e',
            'í' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u',
            'ç' => 'c',
        ]);

        $token = preg_replace('/\s+/', '', $token) ?? $token;

        if (strlen($token) > 3 && str_ends_with($token, 's')) {
            $token = substr($token, 0, -1);
        }

        return $token;
    }

    /**
     * Retorna a unidade de compra formatada para exibição.
     * Usa unidade_compra se definida, senão unidade_medida.
     */
    public function getUnidadeCompraDisplayAttribute(): string
    {
        return $this->unidade_compra ?? $this->unidade_medida;
    }

    public function getTipoItemOperacionalLabelAttribute(): string
    {
        return match ($this->tipo_item_operacional) {
            'consumivel' => 'Consumivel',
            'embalagem' => 'Embalagem',
            'componente' => 'Componente',
            'apoio' => 'Apoio',
            'ignorado' => 'Ignorado',
            default => 'Consumivel',
        };
    }

    public function entraNoCustoPrincipal(): bool
    {
        return (bool) $this->usar_na_precificacao;
    }

    /**
     * Verifica se o insumo está com estoque baixo.
     */
    public function estaComEstoqueBaixo(): bool
    {
        if (!$this->controlar_estoque) {
            return false;
        }

        return $this->estoque_atual <= $this->estoque_minimo;
    }

    /**
     * Retorna a cor/status formatado.
     */
    public function getStatusEstoqueAttribute(): string
    {
        if (!$this->controlar_estoque) return 'nao_controlado';
        if ($this->estoque_atual <= 0) return 'crítico';
        if ($this->estaComEstoqueBaixo()) return 'baixo';
        return 'ok';
    }

    /**
     * Inativa o insumo, preservando histórico e marcando quem inativou.
     *
     * @param string $motivo Motivo da inativação
     * @param ?int $usuarioId ID do usuário que inativou (usa auth se null)
     */
    public function inativar(string $motivo = '', ?int $usuarioId = null): void
    {
        $this->update([
            'ativo' => false,
            'inativado_em' => now(),
            'inativado_por_usuario_id' => $usuarioId ?? auth()->id(),
            'motivo_inativacao' => $motivo,
            'pode_ser_excluido' => false, // Após inativação, não permitir exclusão
        ]);
    }

    /**
     * Reativa o insumo (reverter inativação).
     */
    public function reativar(): void
    {
        $this->update([
            'ativo' => true,
            'inativado_em' => null,
            'inativado_por_usuario_id' => null,
            'motivo_inativacao' => null,
        ]);
    }

    /**
     * Marca o insumo como não-excluível (usado internamente para proteger de exclusões acidentais).
     */
    public function marcarNaoExcluivel(string $motivo = ''): void
    {
        $this->update([
            'pode_ser_excluido' => false,
            'motivo_inativacao' => $motivo ? "Protegido contra exclusão: {$motivo}" : 'Protegido contra exclusão',
        ]);
    }

    /**
     * Permite exclusão novamente (use com cuidado).
     */
    public function permitirExclusao(): void
    {
        $this->update([
            'pode_ser_excluido' => true,
        ]);
    }
}

