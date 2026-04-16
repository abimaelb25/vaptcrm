<?php

declare(strict_types=1);

namespace App\Services\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 23:08
*/

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\ProdutoVariacao;
use App\Models\ProdutoImagem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DataMaintenanceService
{
    /**
     * Exporta o catálogo completo em formato de array estruturado.
     */
    public function export(): array
    {
        return [
            'versao' => '1.0',
            'exportado_em' => now()->toDateTimeString(),
            'origem' => config('app.url'),
            'dados' => [
                'categorias' => Categoria::all()->toArray(),
                'clientes'   => Cliente::all()->toArray(),
                'produtos'   => Produto::with(['variacoes', 'imagens'])->get()->map(function ($p) {
                    $item = $p->toArray();
                    // Garante que o slug da categoria seja exportado para facilitar o remapeamento
                    $item['categoria_slug'] = $p->categoria ? $p->categoria->slug : null;
                    return $item;
                })->toArray(),
            ]
        ];
    }

    /**
     * Importa dados de um array (JSON decodificado).
     */
    public function import(array $json): array
    {
        $stats = ['categorias' => 0, 'produtos' => 0, 'clientes' => 0, 'erros' => []];
        $dados = $json['dados'] ?? [];
        $lojaId = \Illuminate\Support\Facades\Auth::user()?->loja_id;

        try {
            DB::beginTransaction();

            // 1. Importar Categorias
            $catMap = []; // Para mapear IDs antigos -> novos
            foreach ($dados['categorias'] ?? [] as $catData) {
                unset($catData['id']); // Deixa o auto-increment lidar
                $slug = $catData['slug'] ?? Str::slug($catData['nome']);
                
                $categoria = Categoria::updateOrCreate(
                    ['loja_id' => $lojaId, 'slug' => $slug],
                    array_merge($catData, ['loja_id' => $lojaId])
                );
                
                $catMap[$slug] = $categoria->id;
                $stats['categorias']++;
            }

            // 2. Importar Clientes
            foreach ($dados['clientes'] ?? [] as $cliData) {
                unset($cliData['id']);
                // Identificamos cliente por email ou cpf/cnpj para evitar duplicidade
                $match = !empty($cliData['cpf_cnpj']) ? ['cpf_cnpj' => $cliData['cpf_cnpj']] : ['email' => $cliData['email']];
                
                Cliente::updateOrCreate($match, $cliData);
                $stats['clientes']++;
            }

            // 3. Importar Produtos
            foreach ($dados['produtos'] ?? [] as $prodData) {
                $variacoes = $prodData['variacoes'] ?? [];
                $imagens = $prodData['imagens'] ?? [];
                $catSlug = $prodData['categoria_slug'] ?? null;
                
                unset($prodData['id'], $prodData['variacoes'], $prodData['imagens'], $prodData['categoria_slug']);
                
                // Remapeia o categoria_id baseado no slug (garante consistência mesmo com IDs diferentes)
                if ($catSlug && isset($catMap[$catSlug])) {
                    $prodData['categoria_id'] = $catMap[$catSlug];
                }

                $produto = Produto::updateOrCreate(
                    ['loja_id' => $lojaId, 'slug' => $prodData['slug']],
                    array_merge($prodData, ['loja_id' => $lojaId])
                );

                // Sincronizar Variações
                $produto->variacoes()->delete();
                foreach ($variacoes as $var) {
                    unset($var['id'], $var['produto_id']);
                    $produto->variacoes()->create($var);
                }

                // Sincronizar Imagens (Apenas caminhos, assume-se que os arquivos foram movidos manualmente)
                $produto->imagens()->delete();
                foreach ($imagens as $img) {
                    unset($img['id'], $img['produto_id']);
                    $produto->imagens()->create($img);
                }

                $stats['produtos']++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $stats['erros'][] = $e->getMessage();
        }

        return $stats;
    }
}
