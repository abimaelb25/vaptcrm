<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Catalog;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Services\Domain\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected CatalogService $catalogService
    ) {}

    public function index(): View
    {
        return view('painel.categorias.index', [
            'categorias' => Categoria::withCount('produtos')->orderBy('ordem_exibicao')->orderBy('nome')->paginate(20)
        ]);
    }

    public function create(): View
    {
        return view('painel.categorias.create', [
            'categoria' => new Categoria()
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:80'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'texto_destaque' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
            'ordem_exibicao' => ['nullable', 'integer'],
            'banner' => ['nullable', 'image', 'max:2048'],
        ]);

        $this->catalogService->saveCategory($data);
        return redirect()->route('admin.catalog.categorias.index')->with('sucesso', 'Categoria criada com sucesso.');
    }

    public function edit(Categoria $categoria): View
    {
        return view('painel.categorias.edit', [
            'categoria' => $categoria
        ]);
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:80'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'texto_destaque' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
            'ordem_exibicao' => ['nullable', 'integer'],
            'banner' => ['nullable', 'image', 'max:2048'],
        ]);

        $this->catalogService->saveCategory($data, $categoria);
        return redirect()->route('admin.catalog.categorias.index')->with('sucesso', 'Categoria atualizada com sucesso.');
    }

    public function ordenar(Request $request): RedirectResponse
    {
        $ordem = $request->input('ordem', []);
        foreach ($ordem as $index => $id) {
            Categoria::where('id', $id)->update(['ordem_exibicao' => $index]);
        }
        return back()->with('sucesso', 'Ordenação atualizada.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        if (!auth()->user()->temPermissao('apagar_categoria')) {
            abort(403, 'Você não tem permissão para apagar categorias.');
        }

        if ($categoria->produtos()->count() > 0) {
            return back()->with('erro', 'Não é possível excluir categoria com produtos vinculados.');
        }

        $categoria->delete();
        return back()->with('sucesso', 'Categoria removida.');
    }
}
