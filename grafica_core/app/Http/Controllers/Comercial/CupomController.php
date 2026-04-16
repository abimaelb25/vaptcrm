<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 20:41 -03:00
*/

use App\Http\Controllers\Controller;
use App\Models\Cupom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CupomController extends Controller
{
    public function index(): View
    {
        $cupons = Cupom::latest()->paginate(15);
        return view('painel.comercial.cupons.index', compact('cupons'));
    }

    public function create(): View
    {
        return view('painel.comercial.cupons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'codigo' => ['required', 'string', 'unique:cupons,codigo'],
            'tipo' => ['required', 'in:percentual,valor_fixo'],
            'valor' => ['required', 'numeric', 'min:0'],
            'validade_inicio' => ['nullable', 'date'],
            'validade_fim' => ['nullable', 'date'],
            'limite_uso' => ['nullable', 'integer', 'min:1'],
            'ativo' => ['boolean'],
        ]);

        Cupom::create($dados);

        return redirect()->route('admin.sales.cupons.index')
            ->with('sucesso', 'Cupom criado com sucesso!');
    }

    public function edit(Cupom $cupom): View
    {
        return view('painel.comercial.cupons.edit', compact('cupom'));
    }

    public function update(Request $request, Cupom $cupom): RedirectResponse
    {
        $dados = $request->validate([
            'codigo' => ['required', 'string', 'unique:cupons,codigo,' . $cupom->id],
            'tipo' => ['required', 'in:percentual,valor_fixo'],
            'valor' => ['required', 'numeric', 'min:0'],
            'validade_inicio' => ['nullable', 'date'],
            'validade_fim' => ['nullable', 'date'],
            'limite_uso' => ['nullable', 'integer', 'min:1'],
            'ativo' => ['boolean'],
        ]);

        $cupom->update($dados);

        return redirect()->route('admin.sales.cupons.index')
            ->with('sucesso', 'Cupom atualizado!');
    }

    public function destroy(Cupom $cupom): RedirectResponse
    {
        $cupom->delete();
        return redirect()->route('admin.sales.cupons.index')
            ->with('sucesso', 'Cupom removido.');
    }
}
