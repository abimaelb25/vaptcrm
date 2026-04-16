<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Inventory;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:35
*/

use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FornecedorController extends Controller
{
    public function index(): View
    {
        return view('painel.estoque.fornecedores.index', [
            'fornecedores' => Fornecedor::latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('painel.estoque.fornecedores.form', [
            'fornecedor' => new Fornecedor(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'razao_social' => ['nullable', 'string', 'max:255'],
            'cnpj_cpf' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'telefone' => ['nullable', 'string'],
            'whatsapp' => ['nullable', 'string'],
            'cidade' => ['nullable', 'string'],
            'uf' => ['nullable', 'string', 'size:2'],
            'observacao' => ['nullable', 'string'],
        ]);

        Fornecedor::create($data);

        return redirect()->route('admin.inventory.fornecedores.index')->with('sucesso', 'Fornecedor cadastrado.');
    }

    public function edit(Fornecedor $fornecedor): View
    {
        return view('painel.estoque.fornecedores.form', compact('fornecedor'));
    }

    public function update(Request $request, Fornecedor $fornecedor): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'razao_social' => ['nullable', 'string', 'max:255'],
            'cnpj_cpf' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'telefone' => ['nullable', 'string'],
            'whatsapp' => ['nullable', 'string'],
            'cidade' => ['nullable', 'string'],
            'uf' => ['nullable', 'string', 'size:2'],
            'observacao' => ['nullable', 'string'],
            'ativo' => ['required', 'boolean'],
        ]);

        $fornecedor->update($data);

        return redirect()->route('admin.inventory.fornecedores.index')->with('sucesso', 'Fornecedor atualizado.');
    }
}
