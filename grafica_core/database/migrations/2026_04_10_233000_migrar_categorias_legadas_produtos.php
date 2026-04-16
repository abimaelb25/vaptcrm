<?php

declare(strict_types=1);

use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 20:28 -03:00
*/

return new class extends Migration
{
    public function up(): void
    {
        // Pega todos os produtos que ainda não possuem categoria_id mas possuem categoria (string)
        $produtos = Produto::whereNull('categoria_id')->whereNotNull('categoria')->get();

        foreach ($produtos as $produto) {
            $nomeCategoria = trim($produto->categoria);
            
            if (empty($nomeCategoria)) {
                continue;
            }

            // Busca ou cria a categoria pelo nome
            $categoria = Categoria::firstOrCreate(
                ['nome' => $nomeCategoria],
                [
                    'slug' => Str::slug($nomeCategoria),
                    'ativo' => true,
                    'ordem_exibicao' => 0,
                ]
            );

            // Garante que o slug seja único caso tenha sido criado agora
            if ($categoria->wasRecentlyCreated) {
                $slugBase = $categoria->slug;
                $count = 1;
                while (Categoria::where('slug', $categoria->slug)->where('id', '!=', $categoria->id)->exists()) {
                    $categoria->slug = $slugBase . '-' . $count++;
                }
                $categoria->save();
            }

            // Vincula o produto
            $produto->update(['categoria_id' => $categoria->id]);
        }
    }

    public function down(): void
    {
        // Opcional: Desvincular categorias baseadas no nome se necessário.
        // Como o campo categoria_id é novo, não há muito o que reverter além de anular.
        // Produto::whereNotNull('categoria_id')->update(['categoria_id' => null]);
    }
};
