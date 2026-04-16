<?php

declare(strict_types=1);

namespace App\Modules\Produtos\Repositories;

use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductRepository
{
    public function paginateIndex(int $perPage = 20): LengthAwarePaginator
    {
        return Produto::query()
            ->with(['imagens', 'categoriaRel'])
            ->latest()
            ->paginate($perPage);
    }

    public function activeCategories(): Collection
    {
        return Categoria::query()
            ->where('ativo', true)
            ->orderBy('ordem_exibicao')
            ->orderBy('nome')
            ->get();
    }

    public function loadForEdit(Produto $produto): Produto
    {
        return $produto->load([
            'gruposVariacao.opcoes',
            'materiais',
            'acabamentos',
            'faixasQuantidade',
            'imagens',
            'categoriaRel',
        ]);
    }

    public function duplicate(Produto $produto): Produto
    {
        return DB::transaction(function () use ($produto): Produto {
            $newProduct = $produto->replicate();
            $newProduct->nome = $produto->nome . ' (Cópia)';
            $newProduct->slug = Str::slug($newProduct->nome) . '-' . Str::random(5);
            $newProduct->save();

            foreach ($produto->materiais as $mat) {
                $m = $mat->replicate();
                $m->produto_id = $newProduct->id;
                $m->save();
            }

            foreach ($produto->acabamentos as $acab) {
                $a = $acab->replicate();
                $a->produto_id = $newProduct->id;
                $a->save();
            }

            foreach ($produto->gruposVariacao as $grupo) {
                $g = $grupo->replicate();
                $g->produto_id = $newProduct->id;
                $g->save();

                foreach ($grupo->opcoes as $opt) {
                    $o = $opt->replicate();
                    $o->grupo_id = $g->id;
                    $o->save();
                }
            }

            return $newProduct;
        });
    }

    public function toggleActive(Produto $produto): void
    {
        $produto->update(['ativo' => !$produto->ativo]);
    }

    public function delete(Produto $produto): void
    {
        $produto->delete();
    }
}
