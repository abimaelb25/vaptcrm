<?php

namespace App\Http\Controllers\SuperAdmin\Support;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
*/

use App\Http\Controllers\Controller;
use App\Models\SupportCategory;
use Illuminate\Http\Request;

class SupportCategoryController extends Controller
{
    public function index()
    {
        $categorias = SupportCategory::withCount('tickets')->orderBy('nome')->get();
        return view('super-admin.support.categories.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:150|unique:support_categories,nome',
            'ativo' => 'boolean'
        ]);

        SupportCategory::create([
            'nome' => $data['nome'],
            'ativo' => $request->has('ativo')
        ]);

        return back()->with('success', 'Categoria de tickets criada com sucesso!');
    }

    public function update(Request $request, SupportCategory $categoria)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:150|unique:support_categories,nome,' . $categoria->id,
            'ativo' => 'boolean'
        ]);

        $categoria->update([
            'nome' => $data['nome'],
            'ativo' => $request->has('ativo')
        ]);

        return back()->with('success', 'Categoria atualizada.');
    }

    public function destroy(SupportCategory $categoria)
    {
        // Se a categoria tiver tickets vinculados, é mais seguro inativá-la ou soft delete, mas deixo bloquear aqui para não quebrar.
        if ($categoria->tickets()->count() > 0) {
            return back()->with('error', 'Não é possível excluir esta categoria porque já existem tickets usando ela. Desative-a em vez disso.');
        }

        $categoria->delete();
        return back()->with('success', 'Categoria removida com sucesso.');
    }
}
