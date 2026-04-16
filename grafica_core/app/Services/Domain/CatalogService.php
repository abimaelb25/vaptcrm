<?php

declare(strict_types=1);

namespace App\Services\Domain;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\ProdutoImagem;
use App\Services\Core\MediaService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogService
{
    public function __construct(
        protected MediaService $mediaService,
        protected AuditLogService $auditLog
    ) {}

    /**
     * Cria ou atualiza um produto com suas variações e imagens.
     */
    public function saveProduct(array $data, ?Produto $product = null): Produto
    {
        return DB::transaction(function () use ($data, $product) {
            $isUpdate = $product !== null;
            $oldData = $isUpdate ? $product->toArray() : null;

            $productData = $data;
            // Remove arrays de relacionamento para salvar o modelo base
            $toUnset = [
                'variacoes', 'grupos_variacao', 'materiais', 'acabamentos', 
                'faixas', 'imagem_destaque', 'imagens_adicionais'
            ];
            foreach ($toUnset as $key) unset($productData[$key]);

            // Compatibilidade legado: coluna string "categoria"
            $categoriaNome = null;
            if (!empty($productData['categoria_id'])) {
                $categoriaNome = Categoria::query()
                    ->whereKey($productData['categoria_id'])
                    ->value('nome');
            }
            $productData['categoria'] = $categoriaNome ?: ($productData['categoria'] ?? 'Sem Categoria');

            if (!$isUpdate) {
                if (empty($productData['slug'])) {
                    $productData['slug'] = Str::slug($productData['nome']) . '-' . Str::lower(Str::random(6));
                }
                $product = Produto::create($productData);
            } else {
                $product->update($productData);
            }

            // 1. Processamento de Imagens
            $this->processProductImages($product, $data);

            // 2. Sincronização de Materiais (Nível 2/3)
            if (isset($data['materiais'])) {
                $product->materiais()->delete();
                foreach ($data['materiais'] as $mat) {
                    $product->materiais()->create([
                        'loja_id' => $product->loja_id,
                        'nome' => strip_tags((string) $mat['nome']),
                        'preco_ajuste' => (float) $mat['preco_ajuste'],
                    ]);
                }
            }

            // 3. Sincronização de Acabamentos (Nível 2/3)
            if (isset($data['acabamentos'])) {
                $product->acabamentos()->delete();
                foreach ($data['acabamentos'] as $acab) {
                    $product->acabamentos()->create([
                        'loja_id' => $product->loja_id,
                        'nome' => strip_tags((string) $acab['nome']),
                        'preco_ajuste' => (float) $acab['preco_ajuste'],
                        'prazo_ajuste' => (int) ($acab['prazo_ajuste'] ?? 0),
                    ]);
                }
            }

            // 4. Sincronização de Faixas de Quantidade (Nível 3)
            if (isset($data['faixas'])) {
                $product->faixasQuantidade()->delete();
                foreach ($data['faixas'] as $faixa) {
                    $product->faixasQuantidade()->create([
                        'loja_id' => $product->loja_id,
                        'quantidade_minima' => (int) $faixa['quantidade_minima'],
                        'preco_unitario' => (float) $faixa['preco_unitario'],
                        'custo_unitario' => (float) ($faixa['custo_unitario'] ?? 0),
                    ]);
                }
            }

            // 5. Novo Sistema de Variações Técnicas (Grupos e Opções)
            if (isset($data['grupos_variacao'])) {
                $product->gruposVariacao()->delete(); // Cascade deleta opções automaticamente
                foreach ($data['grupos_variacao'] as $index => $grupoData) {
                    $grupo = $product->gruposVariacao()->create([
                        'loja_id' => $product->loja_id,
                        'nome_grupo' => strip_tags((string) $grupoData['nome_grupo']),
                        'tipo_exibicao' => $grupoData['tipo_exibicao'] ?? 'select',
                        'obrigatorio' => (bool) ($grupoData['obrigatorio'] ?? true),
                        'ordem' => $index,
                    ]);

                    foreach ($grupoData['opcoes'] as $idxOpt => $opcaoData) {
                        $grupo->opcoes()->create([
                            'nome_opcao' => strip_tags((string) $opcaoData['nome_opcao']),
                            'acrescimo_preco' => (float) $opcaoData['acrescimo_preco'],
                            'acrescimo_custo' => (float) ($opcaoData['acrescimo_custo'] ?? 0),
                            'acrescimo_prazo' => (int) ($opcaoData['acrescimo_prazo'] ?? 0),
                            'ordem' => $idxOpt,
                        ]);
                    }
                }
            }

            // Mantém compatibilidade com Legado se enviado "variacoes" simples
            if (isset($data['variacoes']) && !isset($data['grupos_variacao'])) {
                $product->variacoes()->delete();
                foreach ($data['variacoes'] as $variation) {
                    $product->variacoes()->create([
                        'tipo_variacao' => strip_tags((string) $variation['tipo_variacao']),
                        'nome_opcao' => strip_tags((string) $variation['nome_opcao']),
                        'acrescimo_venda' => (float) $variation['acrescimo_venda'],
                    ]);
                }
            }

            // 6. Processamento de Imagens
            $this->processProductImages($product, $data);

            $this->auditLog->log(
                'produtos',
                $isUpdate ? 'atualizacao' : 'criacao',
                $product->id,
                $oldData,
                $product->fresh()->toArray()
            );

            return $product;
        });
    }

    /**
     * Processa uploads de imagem principal e galeria.
     */
    private function processProductImages(Produto $product, array $data): void
    {
        // Imagem Destaque
        if (isset($data['imagem_destaque'])) {
            $path = $this->mediaService->saveWithSquareCrop(
                $data['imagem_destaque'],
                "produtos/{$product->slug}",
                'destaque'
            );
            $product->update(['imagem_principal' => $path]);
        }

        // Galeria
        if (isset($data['imagens_adicionais'])) {
            $orderBase = $product->imagens()->max('ordem') ?? 0;
            foreach ($data['imagens_adicionais'] as $index => $file) {
                $path = $this->mediaService->saveWithSquareCrop(
                    $file,
                    "produtos/{$product->slug}",
                    'galeria-' . ($orderBase + $index + 1)
                );

                \App\Models\ProdutoImagem::create([
                    'produto_id' => $product->id,
                    'caminho' => $path,
                    'ordem' => $orderBase + $index + 1,
                ]);
            }
        }
    }


    /**
     * Cria ou atualiza uma categoria.
     */
    public function saveCategory(array $data, ?Categoria $category = null): Categoria
    {
        $isUpdate = $category !== null;
        $oldData = $isUpdate ? $category->toArray() : null;

        // Processamento do Banner
        if (isset($data['banner'])) {
            if ($isUpdate && $category->banner) {
                $this->mediaService->delete($category->banner);
            }
            $data['banner'] = $this->mediaService->saveWithSquareCrop(
                $data['banner'], 
                'categorias', 
                'banner-' . Str::slug($data['nome'])
            );
        }

        if (!$isUpdate) {
            $data['slug'] = Str::slug($data['nome']);
            $category = Categoria::create($data);
        } else {
            $category->update($data);
        }

        $this->auditLog->log(
            'categorias',
            $isUpdate ? 'atualizacao' : 'criacao',
            $category->id,
            $oldData,
            $category->fresh()->toArray()
        );

        return $category;
    }
}
