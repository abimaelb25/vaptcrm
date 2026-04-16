<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Sales;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Cliente::class, 'cliente');
    }

    public function index(Request $request): View
    {
        $query = Cliente::query()->withCount('pedidos');

        if ($request->filled('busca')) {
            $query->where(function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->busca . '%')
                  ->orWhere('email', 'like', '%' . $request->busca . '%')
                  ->orWhere('cpf_cnpj', 'like', '%' . $request->busca . '%');
            });
        }

        return view('painel.clientes.index', [
            'clientes' => $query->latest()->paginate(20),
            'busca' => $request->busca,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:25'],
            'tipo_pessoa' => ['required', 'in:fisica,juridica'],
            'cpf_cnpj' => ['nullable', 'string', 'max:25'],
            'empresa' => ['nullable', 'string', 'max:150'],
        ]);

        Cliente::create($data);
        return back()->with('sucesso', 'Cliente cadastrado com sucesso.');
    }

    public function show(Cliente $cliente): View
    {
        $cliente->load(['pedidos' => fn($q) => $q->latest()->take(10)]);
        return view('painel.clientes.show', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:25'],
            'tipo_pessoa' => ['required', 'in:fisica,juridica'],
            'cpf_cnpj' => ['nullable', 'string', 'max:25'],
            'empresa' => ['nullable', 'string', 'max:150'],
        ]);

        $cliente->update($data);
        return back()->with('sucesso', 'Dados do cliente atualizados.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        if ($cliente->pedidos()->count() > 0) {
            return back()->with('erro', 'Este cliente possui pedidos vinculados e não pode ser removido.');
        }

        $cliente->delete();
        return back()->with('sucesso', 'Cliente removido.');
    }
}
