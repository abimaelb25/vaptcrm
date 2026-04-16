<?php

declare(strict_types=1);

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Depoimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-16 00:50 BRT
*/

class DepoimentoController extends Controller
{
    public function index()
    {
        $depoimentos = Depoimento::daLoja()->orderBy('ordem_exibicao')->get();
        return view('painel.depoimentos.index', compact('depoimentos'));
    }

    public function create()
    {
        return view('painel.depoimentos.form', ['depoimento' => new Depoimento()]);
    }

    public function store(Request $request)
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
            $dados['avatar_path'] = $request->file('avatar_path')->store('depoimentos/loja', 'public');
        }

        $dados['contexto'] = 'loja';
        $dados['publicado'] = $request->has('publicado');
        $dados['destaque'] = $request->has('destaque');
        $dados['ordem_exibicao'] = $dados['ordem_exibicao'] ?? 0;

        Depoimento::create($dados);

        return redirect()->route('admin.system.depoimentos.index')->with('sucesso', 'Depoimento do cliente salvo com sucesso.');
    }

    public function edit(Depoimento $depoimento)
    {
        return view('painel.depoimentos.form', compact('depoimento'));
    }

    public function update(Request $request, Depoimento $depoimento)
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
            if ($depoimento->avatar_path) Storage::disk('public')->delete($depoimento->avatar_path);
            $dados['avatar_path'] = $request->file('avatar_path')->store('depoimentos/loja', 'public');
        }

        $dados['publicado'] = $request->has('publicado');
        $dados['destaque'] = $request->has('destaque');
        $dados['ordem_exibicao'] = $dados['ordem_exibicao'] ?? 0;

        $depoimento->update($dados);

        return redirect()->route('admin.system.depoimentos.index')->with('sucesso', 'Depoimento do cliente atualizado.');
    }

    public function destroy(Depoimento $depoimento)
    {
        if ($depoimento->avatar_path) Storage::disk('public')->delete($depoimento->avatar_path);
        $depoimento->delete();
        return back()->with('sucesso', 'Depoimento excluído definitivamente.');
    }
}
