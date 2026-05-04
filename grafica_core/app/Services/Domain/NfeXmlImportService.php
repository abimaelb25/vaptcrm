<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\DocumentoFiscalEntrada;
use App\Models\DocumentoFiscalEntradaItem;
use App\Models\EstoqueMovimentacao;
use App\Models\Fornecedor;
use App\Models\FornecedorProdutoMapeamento;
use App\Models\Insumo;
use App\Models\NfeImportacao;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class NfeXmlImportService
{
    private function normalizeFiscalPurchaseUnit(?string $value): ?string
    {
        $unit = trim((string) $value);

        if ($unit === '') {
            return null;
        }

        return match (mb_strtoupper($unit)) {
            'UN', 'UND', 'UNID', 'UNIDADE' => 'unidade',
            default => mb_strtolower($unit),
        };
    }

    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly InsumoOperationalClassifierService $insumoClassifierService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function criarPreview(UploadedFile $xmlFile, int $lojaId, int $usuarioId): NfeImportacao
    {
        $this->assertEstruturaImportacaoDisponivel();

        $xmlPath = $xmlFile->storeAs(
            'nfe-importacoes/' . $lojaId . '/' . now()->format('Y/m'),
            now()->format('YmdHis') . '_' . uniqid('nfe_', true) . '.xml',
            'local'
        );

        if (!is_string($xmlPath) || $xmlPath === '') {
            throw ValidationException::withMessages([
                'xml_file' => 'Nao foi possivel armazenar o XML para analise.',
            ]);
        }

        $xmlContent = Storage::disk('local')->get($xmlPath);
        $payload = $this->parseXml($xmlContent);
        $alertas = $this->montarAlertas($lojaId, $payload);

        return NfeImportacao::create([
            'loja_id' => $lojaId,
            'usuario_id' => $usuarioId,
            'chave_nfe' => $payload['chave_nfe'],
            'numero' => $payload['numero'],
            'serie' => $payload['serie'],
            'data_emissao' => $payload['data_emissao'],
            'valor_total' => $payload['valor_total'],
            'xml_path' => $xmlPath,
            'status' => 'preview',
            'payload_json' => $payload,
            'alertas_json' => $alertas,
        ]);
    }

    public function montarDadosPreview(NfeImportacao $importacao, int $lojaId): array
    {
        $payload = (array) ($importacao->payload_json ?? []);
        $itens = (array) ($payload['itens'] ?? []);

        $fornecedorData = (array) ($payload['fornecedor'] ?? []);
        $fornecedorExistente = $this->buscarFornecedorExistente($lojaId, $fornecedorData);
        $insumosAtivos = Insumo::where('ativo', true)
            ->orderBy('nome')
            ->get([
                'id',
                'nome',
                'unidade_medida',
                'unidade_compra',
                'quantidade_por_compra',
                'quantidade_subunidades_por_compra',
                'unidade_subunidade',
                'quantidade_consumo_por_subunidade',
                'categoria',
                'codigo_interno',
                'tipo_item_operacional',
                'controlar_estoque',
                'usar_na_precificacao',
            ]);

        $itensComSugestao = [];
        foreach ($itens as $item) {
            $sugestao = $this->sugerirInsumo($lojaId, $fornecedorExistente?->id, (array) $item);

            $itensComSugestao[] = array_merge((array) $item, [
                'sugestao_insumo_id' => $sugestao['insumo_id'] ?? null,
                'sugestao_tipo' => $sugestao['tipo'] ?? null,
                'sugestao_nome' => $sugestao['nome'] ?? null,
                'sugestao_alerta_unidade' => $sugestao['alerta_unidade'] ?? null,
                'sugestao_tipo_operacional' => $this->insumoClassifierService->sugestaoTipoPorDescricao((string) ($item['descricao'] ?? '')),
            ]);
        }

        return [
            'payload' => $payload,
            'itens' => $itensComSugestao,
            'fornecedor_existente' => $fornecedorExistente,
            'insumos_ativos' => $insumosAtivos,
            'alertas' => (array) ($importacao->alertas_json ?? []),
        ];
    }

    public function confirmarImportacao(NfeImportacao $importacao, array $acoesItens, int $lojaId, int $usuarioId): DocumentoFiscalEntrada
    {
        $this->assertEstruturaImportacaoDisponivel();

        if ($importacao->status !== 'preview') {
            throw ValidationException::withMessages([
                'importacao' => 'Esta importacao nao esta disponivel para confirmacao.',
            ]);
        }

        $payload = (array) ($importacao->payload_json ?? []);
        $chave = (string) ($payload['chave_nfe'] ?? '');

        if ($chave === '') {
            throw ValidationException::withMessages([
                'xml_file' => 'Nao foi possivel identificar a chave da NF-e no XML.',
            ]);
        }

        $duplicada = DocumentoFiscalEntrada::where('loja_id', $lojaId)
            ->where('chave_nfe', $chave)
            ->exists();

        if ($duplicada) {
            throw ValidationException::withMessages([
                'xml_file' => 'Esta NF-e ja foi importada para esta loja.',
            ]);
        }

        return DB::transaction(function () use ($importacao, $acoesItens, $lojaId, $usuarioId, $payload): DocumentoFiscalEntrada {
            $fornecedor = $this->resolverFornecedor($lojaId, (array) ($payload['fornecedor'] ?? []));
            $itens = (array) ($payload['itens'] ?? []);

            $documento = DocumentoFiscalEntrada::create([
                'loja_id' => $lojaId,
                'fornecedor_id' => $fornecedor?->id,
                'chave_nfe' => (string) ($payload['chave_nfe'] ?? ''),
                'numero' => (string) ($payload['numero'] ?? ''),
                'serie' => (string) ($payload['serie'] ?? ''),
                'data_emissao' => $payload['data_emissao'] ?? null,
                'valor_total' => (float) ($payload['valor_total'] ?? 0),
                'xml_path' => $importacao->xml_path,
                'status_importacao' => 'confirmada',
                'usuario_responsavel_id' => $usuarioId,
            ]);

            $resumo = [
                'criados' => 0,
                'vinculados' => 0,
                'ignorados' => 0,
                'movimentacoes' => 0,
            ];

            foreach ($itens as $idx => $item) {
                $dadosAcao = (array) ($acoesItens[$idx] ?? []);
                $acao = (string) ($dadosAcao['acao'] ?? 'ignorar');
                $insumo = null;

                if ($acao === 'criar') {
                    $nomeNovo = trim((string) ($dadosAcao['novo_nome'] ?? ''));
                    if ($nomeNovo === '') {
                        throw ValidationException::withMessages([
                            "items.{$idx}.novo_nome" => 'Informe o nome do novo insumo.',
                        ]);
                    }

                    // Conversão de unidade: o usuário pode ter informado no preview
                    // a unidade de consumo (unidade_medida) e quantas há por compra.
                    $unidadeMedida       = trim((string) ($dadosAcao['unidade_medida'] ?? ''));
                    if ($unidadeMedida === '') {
                        throw ValidationException::withMessages([
                            "items.{$idx}.unidade_medida" => 'Informe explicitamente a unidade de estoque/consumo do novo insumo.',
                        ]);
                    }

                    $unidadeCompra       = $this->normalizeFiscalPurchaseUnit($dadosAcao['unidade_compra'] ?? null) ?? '';
                    $qtdPorCompra        = (float) ($dadosAcao['quantidade_por_compra'] ?? 1);
                    $qtdPorCompra        = max(0.0001, $qtdPorCompra);

                    // Se não informou unidade de compra distinta, desativa conversão.
                    if ($unidadeCompra === '' || $qtdPorCompra <= 1) {
                        $unidadeCompra = null;
                        $qtdPorCompra  = 1;
                    }

                    // Segundo nível de embalagem (ex: caixa → frasco → ml)
                    $qtdSubunidades    = (float) ($dadosAcao['quantidade_subunidades_por_compra'] ?? 0);
                    $unidadeSubunidade = trim((string) ($dadosAcao['unidade_subunidade'] ?? ''));
                    $qtdConsumoPorSub  = (float) ($dadosAcao['quantidade_consumo_por_subunidade'] ?? 0);

                    $temDoisNiveis = $unidadeCompra !== null
                        && $unidadeSubunidade !== ''
                        && $qtdSubunidades > 0
                        && $qtdConsumoPorSub > 0;

                    if (!$temDoisNiveis) {
                        $qtdSubunidades    = null;
                        $unidadeSubunidade = null;
                        $qtdConsumoPorSub  = null;
                    }

                    $dadosInsumo = $this->insumoClassifierService->normalizarCamposInsumo([
                        'tipo_item_operacional' => (string) ($dadosAcao['tipo_item_operacional'] ?? 'consumivel'),
                        'controlar_estoque' => (bool) ($dadosAcao['controlar_estoque'] ?? true),
                        'usar_na_precificacao' => (bool) ($dadosAcao['usar_na_precificacao'] ?? true),
                    ]);

                    $insumo = Insumo::create([
                        'loja_id'              => $lojaId,
                        'nome'                 => $nomeNovo,
                        'codigo_interno'       => $item['codigo_fornecedor'] ?? null,
                        'categoria'            => $dadosAcao['categoria'] ?? null,
                        'tipo_item_operacional' => $dadosInsumo['tipo_item_operacional'],
                        'unidade_medida'       => mb_strtolower($unidadeMedida),
                        'unidade_compra'       => $unidadeCompra,
                        'quantidade_por_compra' => $qtdPorCompra,
                        'quantidade_subunidades_por_compra'  => $qtdSubunidades,
                        'unidade_subunidade'                  => $unidadeSubunidade,
                        'quantidade_consumo_por_subunidade'   => $qtdConsumoPorSub,
                        'controlar_estoque'    => $dadosInsumo['controlar_estoque'],
                        'usar_na_precificacao' => $dadosInsumo['usar_na_precificacao'],
                        'estoque_atual'        => 0,
                        'estoque_minimo'       => 0,
                        'estoque_maximo'       => null,
                        'custo_medio'          => 0,
                        'ultimo_custo'         => null,
                        'ativo'                => true,
                    ]);

                    $resumo['criados']++;
                }

                if ($acao === 'vincular') {
                    $insumoId = (int) ($dadosAcao['insumo_id'] ?? 0);
                    $insumo = Insumo::where('loja_id', $lojaId)->find($insumoId);

                    if (!$insumo) {
                        throw ValidationException::withMessages([
                            "items.{$idx}.insumo_id" => 'O insumo selecionado nao pertence a loja atual.',
                        ]);
                    }

                    $resumo['vinculados']++;
                }

                if ($acao === 'ignorar') {
                    $resumo['ignorados']++;
                }

                DocumentoFiscalEntradaItem::create([
                    'loja_id' => $lojaId,
                    'documento_id' => $documento->id,
                    'insumo_id' => $insumo?->id,
                    'codigo_fornecedor' => $item['codigo_fornecedor'] ?? null,
                    'descricao' => $item['descricao'] ?? '',
                    'ncm' => $item['ncm'] ?? null,
                    'cfop' => $item['cfop'] ?? null,
                    'unidade' => $item['unidade'] ?? null,
                    'quantidade' => (float) ($item['quantidade'] ?? 0),
                    'valor_unitario' => (float) ($item['valor_unitario'] ?? 0),
                    'valor_total' => (float) ($item['valor_total'] ?? 0),
                    'impostos_json' => $item['impostos'] ?? null,
                    'acao_definida' => in_array($acao, ['criar', 'vincular', 'ignorar'], true) ? $acao : 'ignorar',
                    'tipo_item_operacional' => $dadosAcao['tipo_item_operacional'] ?? 'consumivel',
                    'tratamento_financeiro' => $dadosAcao['tratamento_financeiro'] ?? 'custo_proprio',
                    'valor_financeiro_alocado' => (float) ($dadosAcao['valor_financeiro_alocado'] ?? ($item['valor_total'] ?? 0)),
                    'confirmacao_desconsideracao' => (bool) ($dadosAcao['confirmacao_desconsideracao'] ?? false),
                ]);

                if ($insumo) {
                    if ((bool) $insumo->controlar_estoque) {
                        // A NF-e sempre informa quantidade e valor na unidade de compra fiscal.
                        // Passamos em_unidade_compra=true para que o InventoryService aplique a
                        // conversão (quantidade_por_compra) e registre o custo por unidade de consumo.
                        $this->inventoryService->registrarEntrada($insumo, [
                            'quantidade'         => (float) ($item['quantidade'] ?? 0),
                            'custo_unitario'     => (float) ($item['valor_unitario'] ?? 0),
                            'em_unidade_compra'  => true,
                            'fornecedor_id'      => $fornecedor?->id,
                            'data_movimentacao'  => $payload['data_emissao'] ?? now(),
                            'origem'             => 'compra',
                            'descricao'          => 'Entrada via NF-e ' . ($payload['numero'] ?? '-') . '/' . ($payload['serie'] ?? '-') . ' chave ' . ($payload['chave_nfe'] ?? '-'),
                        ]);

                        $resumo['movimentacoes']++;
                    }

                    $this->atualizarMapeamentoFornecedor((int) $lojaId, $fornecedor?->id, (array) $item, $insumo->id);
                }
            }

            $importacao->update([
                'fornecedor_id' => $fornecedor?->id,
                'documento_fiscal_entrada_id' => $documento->id,
                'status' => 'confirmada',
                'confirmada_em' => now(),
            ]);

            $this->auditLogService->log(
                'estoque_nfe_xml',
                'importacao_confirmada',
                $documento->id,
                null,
                [
                    'chave_nfe' => $documento->chave_nfe,
                    'fornecedor_id' => $fornecedor?->id,
                    'resumo' => $resumo,
                    'resumo_financeiro' => $this->montarResumoFinanceiro((array) ($acoesItens ?? []), (float) ($payload['valor_total'] ?? 0)),
                    'importacao_id' => $importacao->id,
                ]
            );

            return $documento;
        });
    }

    public function reabrirImportacao(NfeImportacao $importacao): void
    {
        if ($importacao->status !== 'confirmada') {
            throw ValidationException::withMessages([
                'importacao' => 'Apenas importacoes confirmadas podem ser reabertas.',
            ]);
        }

        DB::transaction(function () use ($importacao): void {
            $documento = $importacao->documentoFiscalEntrada;

            if ($documento) {
                $chaveNfe = (string) $documento->chave_nfe;

                // Para cada insumo afetado, reverter as movimentacoes desta NF-e
                $itensAfetados = $documento->itens()->whereNotNull('insumo_id')->get();

                foreach ($itensAfetados as $itemDocumento) {
                    $insumo = Insumo::query()->lockForUpdate()->find($itemDocumento->insumo_id);
                    if (!$insumo) {
                        continue;
                    }

                    // Localizar movimentacoes criadas por esta NF-e (pela chave na descricao)
                    $movimentacoes = EstoqueMovimentacao::where('insumo_id', $insumo->id)
                        ->where('descricao', 'like', '%chave ' . $chaveNfe . '%')
                        ->get();

                    $movimentacoes->each->delete();

                    // Recalcular estoque e custo_medio reproduzindo as movimentacoes restantes
                    $this->recalcularEstoqueInsumo($insumo);
                }

                // Excluir o documento fiscal (cascade exclui os itens)
                $documento->delete();
            }

            $importacao->update([
                'status'                     => 'preview',
                'confirmada_em'              => null,
                'documento_fiscal_entrada_id' => null,
                'fornecedor_id'              => null,
            ]);

            $this->auditLogService->log(
                'estoque_nfe_xml',
                'importacao_reaberta',
                $importacao->id,
                null,
                ['chave_nfe' => (string) ($importacao->payload_json['chave_nfe'] ?? '')]
            );
        });
    }

    /**
     * Recalcula estoque_atual e custo_medio de um insumo reproduzindo todas as
     * movimentacoes restantes em ordem cronologica.
     */
    private function recalcularEstoqueInsumo(Insumo $insumo): void
    {
        $movimentacoes = EstoqueMovimentacao::where('insumo_id', $insumo->id)
            ->orderBy('data_movimentacao')
            ->orderBy('id')
            ->get();

        $estoqueAtual = 0.0;
        $custoMedio   = 0.0;
        $ultimoCusto  = null;

        foreach ($movimentacoes as $mov) {
            $q = (float) $mov->quantidade_base;
            if ($mov->tipo === 'entrada') {
                $c = (float) $mov->custo_unitario;
                if ($estoqueAtual + $q > 0) {
                    $custoMedio = ($estoqueAtual * $custoMedio + $q * $c) / ($estoqueAtual + $q);
                }
                $estoqueAtual += $q;
                $ultimoCusto   = $c;
            } else {
                $estoqueAtual = max(0.0, $estoqueAtual - $q);
            }
        }

        $insumo->update([
            'estoque_atual' => max(0.0, $estoqueAtual),
            'custo_medio'   => $custoMedio,
            'ultimo_custo'  => $ultimoCusto,
        ]);
    }

    public function parseXml(string $xmlContent): array
    {
        $this->assertXmlSeguro($xmlContent);

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($xml === false) {
            throw ValidationException::withMessages([
                'xml_file' => 'Nao foi possivel interpretar o XML da NF-e.',
            ]);
        }

        $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');

        $infNfeNode = $xml->xpath('//nfe:infNFe');
        if (!is_array($infNfeNode) || !isset($infNfeNode[0])) {
            throw ValidationException::withMessages([
                'xml_file' => 'O XML enviado nao possui estrutura valida de NF-e.',
            ]);
        }

        $infNfe = $infNfeNode[0];
        $ide = $infNfe->ide;
        $emit = $infNfe->emit;
        $total = $infNfe->total->ICMSTot ?? null;

        $itens = [];
        foreach ($infNfe->det as $det) {
            $prod = $det->prod;
            $imposto = $det->imposto;

            $quantidade = $this->toFloat((string) ($prod->qCom ?? '0'));
            $valorUnitario = $this->toFloat((string) ($prod->vUnCom ?? '0'));
            $valorTotal = $this->toFloat((string) ($prod->vProd ?? '0'));

            $itens[] = [
                'numero_item' => (int) ($det['nItem'] ?? 0),
                'descricao' => trim((string) ($prod->xProd ?? '')),
                'codigo_fornecedor' => trim((string) ($prod->cProd ?? '')),
                'ncm' => trim((string) ($prod->NCM ?? '')),
                'cfop' => trim((string) ($prod->CFOP ?? '')),
                'unidade' => trim((string) ($prod->uCom ?? '')),
                'quantidade' => $quantidade,
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorTotal,
                'impostos' => $this->extrairImpostos($imposto),
            ];
        }

        $fornecedorCnpj = trim((string) ($emit->CNPJ ?? ''));
        $fornecedorCpf = trim((string) ($emit->CPF ?? ''));
        $fornecedorDoc = $fornecedorCnpj !== '' ? $fornecedorCnpj : $fornecedorCpf;

        return [
            'chave_nfe' => $this->extrairChaveNfe($infNfe),
            'numero' => trim((string) ($ide->nNF ?? '')),
            'serie' => trim((string) ($ide->serie ?? '')),
            'data_emissao' => $this->normalizarData((string) ($ide->dhEmi ?? $ide->dEmi ?? '')),
            'valor_total' => $this->toFloat((string) ($total->vNF ?? '0')),
            'fornecedor' => [
                'nome' => trim((string) ($emit->xFant ?? $emit->xNome ?? '')),
                'razao_social' => trim((string) ($emit->xNome ?? '')),
                'cnpj_cpf' => $this->somenteDigitos($fornecedorDoc),
            ],
            'itens' => $itens,
        ];
    }

    private function montarResumoFinanceiro(array $acoesItens, float $valorTotalNota): array
    {
        $valorAlocadoConsumiveis = 0.0;
        $valorAlocadoEmbalagensComponentes = 0.0;
        $valorDesconsiderado = 0.0;

        foreach ($acoesItens as $item) {
            $tipo = (string) ($item['tipo_item_operacional'] ?? 'consumivel');
            $tratamento = (string) ($item['tratamento_financeiro'] ?? 'custo_proprio');
            $valor = (float) ($item['valor_financeiro_alocado'] ?? 0);

            if ($tratamento === 'desconsiderar') {
                $valorDesconsiderado += $valor;
                continue;
            }

            if ($tipo === 'consumivel') {
                $valorAlocadoConsumiveis += $valor;
            } else {
                $valorAlocadoEmbalagensComponentes += $valor;
            }
        }

        $valorAlocadoTotal = $valorAlocadoConsumiveis + $valorAlocadoEmbalagensComponentes;

        return [
            'valor_total_nota' => round($valorTotalNota, 2),
            'valor_alocado_consumiveis' => round($valorAlocadoConsumiveis, 2),
            'valor_alocado_embalagens_componentes' => round($valorAlocadoEmbalagensComponentes, 2),
            'valor_desconsiderado' => round($valorDesconsiderado, 2),
            'valor_nao_alocado' => round($valorTotalNota - $valorAlocadoTotal - $valorDesconsiderado, 2),
        ];
    }

    private function montarAlertas(int $lojaId, array $payload): array
    {
        $alertas = [];

        if (!Schema::hasTable('documentos_fiscais_entrada')) {
            $alertas[] = 'Estrutura de importacao NF-e pendente. Execute as migracoes antes de confirmar.';
            return $alertas;
        }

        $chaveNfe = (string) ($payload['chave_nfe'] ?? '');
        if ($chaveNfe === '') {
            $alertas[] = 'Nao foi possivel identificar a chave da NF-e no XML.';
        } else {
            $jaImportada = DocumentoFiscalEntrada::where('loja_id', $lojaId)
                ->where('chave_nfe', $chaveNfe)
                ->exists();

            if ($jaImportada) {
                $alertas[] = 'Esta NF-e ja esta importada para a loja atual.';
            }
        }

        if (empty($payload['itens'])) {
            $alertas[] = 'A NF-e nao possui itens para conciliacao.';
        }

        return $alertas;
    }

    private function assertXmlSeguro(string $xmlContent): void
    {
        if (stripos($xmlContent, '<!DOCTYPE') !== false || stripos($xmlContent, '<!ENTITY') !== false) {
            throw ValidationException::withMessages([
                'xml_file' => 'XML invalido por conter declaracoes nao permitidas.',
            ]);
        }
    }

    private function extrairImpostos(?\SimpleXMLElement $imposto): array
    {
        if (!$imposto) {
            return [];
        }

        return [
            'vTotTrib' => $this->toFloat((string) ($imposto->vTotTrib ?? '0')),
            'icms' => [
                'orig' => trim((string) ($imposto->ICMS->ICMS00->orig ?? $imposto->ICMS->ICMSSN102->orig ?? '')),
                'cst' => trim((string) ($imposto->ICMS->ICMS00->CST ?? $imposto->ICMS->ICMSSN102->CSOSN ?? '')),
                'vICMS' => $this->toFloat((string) ($imposto->ICMS->ICMS00->vICMS ?? '0')),
            ],
            'pis' => [
                'vPIS' => $this->toFloat((string) ($imposto->PIS->PISAliq->vPIS ?? $imposto->PIS->PISNT->vPIS ?? '0')),
            ],
            'cofins' => [
                'vCOFINS' => $this->toFloat((string) ($imposto->COFINS->COFINSAliq->vCOFINS ?? $imposto->COFINS->COFINSNT->vCOFINS ?? '0')),
            ],
            'ipi' => [
                'vIPI' => $this->toFloat((string) ($imposto->IPI->IPITrib->vIPI ?? '0')),
            ],
        ];
    }

    private function extrairChaveNfe(\SimpleXMLElement $infNfe): string
    {
        $id = (string) ($infNfe['Id'] ?? '');

        if (str_starts_with($id, 'NFe')) {
            return substr($id, 3);
        }

        return trim($id);
    }

    private function normalizarData(string $valor): ?string
    {
        $valor = trim($valor);
        if ($valor === '') {
            return null;
        }

        try {
            return Carbon::parse($valor)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function toFloat(string $value): float
    {
        $value = trim($value);
        if ($value === '') {
            return 0.0;
        }

        return (float) str_replace(',', '.', $value);
    }

    private function somenteDigitos(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function buscarFornecedorExistente(int $lojaId, array $fornecedorData): ?Fornecedor
    {
        $documento = $this->somenteDigitos((string) ($fornecedorData['cnpj_cpf'] ?? ''));

        if ($documento !== '') {
            $fornecedores = Fornecedor::where('loja_id', $lojaId)
                ->whereNotNull('cnpj_cpf')
                ->get(['id', 'cnpj_cpf', 'nome', 'razao_social', 'loja_id']);

            $fornecedor = $fornecedores->first(function (Fornecedor $candidate) use ($documento): bool {
                return $this->somenteDigitos((string) $candidate->cnpj_cpf) === $documento;
            });

            if ($fornecedor) {
                return $fornecedor;
            }
        }

        $nome = trim((string) ($fornecedorData['razao_social'] ?? $fornecedorData['nome'] ?? ''));
        if ($nome !== '') {
            return Fornecedor::where('loja_id', $lojaId)
                ->where(function ($query) use ($nome): void {
                    $query->where('nome', 'like', $nome)
                        ->orWhere('razao_social', 'like', $nome);
                })
                ->first();
        }

        return null;
    }

    private function resolverFornecedor(int $lojaId, array $fornecedorData): ?Fornecedor
    {
        $nome = trim((string) ($fornecedorData['nome'] ?? ''));
        $razao = trim((string) ($fornecedorData['razao_social'] ?? ''));
        $documento = $this->somenteDigitos((string) ($fornecedorData['cnpj_cpf'] ?? ''));

        if ($nome === '' && $razao === '' && $documento === '') {
            return null;
        }

        $fornecedor = $this->buscarFornecedorExistente($lojaId, [
            'nome' => $nome,
            'razao_social' => $razao,
            'cnpj_cpf' => $documento,
        ]);

        if ($fornecedor) {
            $fornecedor->update([
                'nome' => $nome !== '' ? $nome : $fornecedor->nome,
                'razao_social' => $razao !== '' ? $razao : $fornecedor->razao_social,
                'cnpj_cpf' => $documento !== '' ? $documento : $fornecedor->cnpj_cpf,
            ]);

            return $fornecedor;
        }

        return Fornecedor::create([
            'loja_id' => $lojaId,
            'nome' => $nome !== '' ? $nome : ($razao !== '' ? $razao : 'Fornecedor NF-e'),
            'razao_social' => $razao !== '' ? $razao : null,
            'cnpj_cpf' => $documento !== '' ? $documento : null,
            'ativo' => true,
        ]);
    }

    private function sugerirInsumo(int $lojaId, ?int $fornecedorId, array $item): array
    {
        $codigoFornecedor = trim((string) ($item['codigo_fornecedor'] ?? ''));
        $descricao = trim((string) ($item['descricao'] ?? ''));
        $unidade = trim((string) ($item['unidade'] ?? ''));

        if ($fornecedorId && $codigoFornecedor !== '') {
            $map = FornecedorProdutoMapeamento::where('loja_id', $lojaId)
                ->where('fornecedor_id', $fornecedorId)
                ->where('codigo_fornecedor', $codigoFornecedor)
                ->with('insumo:id,nome,unidade_medida')
                ->first();

            if ($map && $map->insumo) {
                return [
                    'insumo_id' => $map->insumo->id,
                    'nome' => $map->insumo->nome,
                    'tipo' => 'mapeamento',
                    'alerta_unidade' => $this->compararUnidade($unidade, (string) $map->insumo->unidade_medida),
                ];
            }
        }

        if ($codigoFornecedor !== '') {
            $insumo = Insumo::where('loja_id', $lojaId)
                ->where('codigo_interno', $codigoFornecedor)
                ->first();

            if ($insumo) {
                return [
                    'insumo_id' => $insumo->id,
                    'nome' => $insumo->nome,
                    'tipo' => 'codigo_interno',
                    'alerta_unidade' => $this->compararUnidade($unidade, (string) $insumo->unidade_medida),
                ];
            }
        }

        if ($descricao !== '') {
            $insumo = Insumo::where('loja_id', $lojaId)
                ->where('nome', 'like', '%' . $descricao . '%')
                ->first();

            if ($insumo) {
                return [
                    'insumo_id' => $insumo->id,
                    'nome' => $insumo->nome,
                    'tipo' => 'descricao',
                    'alerta_unidade' => $this->compararUnidade($unidade, (string) $insumo->unidade_medida),
                ];
            }
        }

        return [];
    }

    private function compararUnidade(string $xmlUnit, string $insumoUnit): ?string
    {
        if ($xmlUnit === '' || $insumoUnit === '') {
            return null;
        }

        if (mb_strtolower($xmlUnit) !== mb_strtolower($insumoUnit)) {
            return 'Unidade do XML (' . $xmlUnit . ') difere da unidade do insumo (' . $insumoUnit . ').';
        }

        return null;
    }

    private function atualizarMapeamentoFornecedor(int $lojaId, ?int $fornecedorId, array $item, int $insumoId): void
    {
        if (!$fornecedorId) {
            return;
        }

        $codigo = trim((string) ($item['codigo_fornecedor'] ?? ''));
        $descricao = trim((string) ($item['descricao'] ?? ''));

        if ($descricao === '') {
            return;
        }

        if ($codigo === '') {
            return;
        }

        FornecedorProdutoMapeamento::updateOrCreate(
            [
                'loja_id' => $lojaId,
                'fornecedor_id' => $fornecedorId,
                'codigo_fornecedor' => $codigo,
            ],
            [
                'descricao_fornecedor' => $descricao,
                'insumo_id' => $insumoId,
                'confianca' => 100,
            ]
        );
    }

    private function assertEstruturaImportacaoDisponivel(): void
    {
        $requiredTables = [
            'nfe_importacoes',
            'documentos_fiscais_entrada',
            'documentos_fiscais_entrada_itens',
            'fornecedor_produto_mapeamentos',
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                throw ValidationException::withMessages([
                    'xml_file' => 'Modulo de importacao NF-e ainda nao inicializado neste ambiente. Execute as migracoes pendentes.',
                ]);
            }
        }
    }
}
