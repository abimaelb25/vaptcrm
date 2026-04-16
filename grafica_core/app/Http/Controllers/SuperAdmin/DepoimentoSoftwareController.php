<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16 01:00 BRT
*/

use App\Http\Controllers\Controller;
use App\Models\Depoimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DepoimentoSoftwareController extends Controller
{
    public function index(): View
    {
        // Pega apenas depoimentos da plataforma (sem escopo de loja)
        $depoimentos = Depoimento::daPlataforma()->orderBy('ordem_exibicao')->get();
        return view('super-admin.depoimentos.index', compact('depoimentos'));
    }

    public function create(): View
    {
        return view('super-admin.depoimentos.form', ['depoimento' => new Depoimento()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome_autor' => 'required|string|max:200',
            'cargo_autor' => 'nullable|string|max:100',
            'empresa_autor' => 'nullable|string|max:100',
            'cidade_autor' => 'nullable|string|max:100',
            'depoimento_texto' => 'required|string',
            'titulo' => 'nullable|string|max:200',
            'nota' => 'nullable|integer|min:1|max:5',
            'avatar_path' => 'nullable|image|max:1024',
            'ordem_exibicao' => 'nullable|integer',
            'publicado' => 'nullable|boolean',
            'destaque' => 'nullable|boolean',
        ]);

        if ($request->hasFile('avatar_path')) {
            $dados['avatar_path'] = $request->file('avatar_path')->store('depoimentos/plataforma', 'public');
        }

        $dados['contexto'] = 'plataforma';
        $dados['loja_id'] = null; // Depoimento da plataforma não tem loja_id
        $dados['publicado'] = $request->has('publicado');
        $dados['destaque'] = $request->has('destaque');
        $dados['ordem_exibicao'] = $dados['ordem_exibicao'] ?? 0;

        Depoimento::create($dados);

        return redirect()->route('superadmin.depoimentos.index')->with('success', 'Depoimento sobre o VaptCRM salvo com sucesso.');
    }

    public function edit(int $id): View
    {
        $depoimento = Depoimento::daPlataforma()->findOrFail($id);
        return view('super-admin.depoimentos.form', compact('depoimento'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $depoimento = Depoimento::daPlataforma()->findOrFail($id);

        $dados = $request->validate([
            'nome_autor' => 'required|string|max:200',
            'cargo_autor' => 'nullable|string|max:100',
            'empresa_autor' => 'nullable|string|max:100',
            'cidade_autor' => 'nullable|string|max:100',
            'depoimento_texto' => 'required|string',
            'titulo' => 'nullable|string|max:200',
            'nota' => 'nullable|integer|min:1|max:5',
            'avatar_path' => 'nullable|image|max:1024',
            'ordem_exibicao' => 'nullable|integer',
            'publicado' => 'nullable|boolean',
            'destaque' => 'nullable|boolean',
        ]);

        if ($request->hasFile('avatar_path')) {
            if ($depoimento->avatar_path) Storage::disk('public')->delete($depoimento->avatar_path);
            $dados['avatar_path'] = $request->file('avatar_path')->store('depoimentos/plataforma', 'public');
        }

        $dados['publicado'] = $request->has('publicado');
        $dados['destaque'] = $request->has('destaque');
        $dados['ordem_exibicao'] = $dados['ordem_exibicao'] ?? 0;

        $depoimento->update($dados);

        return redirect()->route('superadmin.depoimentos.index')->with('success', 'Depoimento atualizado.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $depoimento = Depoimento::daPlataforma()->findOrFail($id);
        if ($depoimento->avatar_path) Storage::disk('public')->delete($depoimento->avatar_path);
        $depoimento->delete();
        return back()->with('success', 'Depoimento excluído da plataforma.');
    }
}
