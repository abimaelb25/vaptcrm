<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Sales;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-11 01:42 -03:00
*/

use App\Http\Controllers\Controller;
use App\Models\Cupom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class CupomController extends Controller
{
    public function index(): View
    {
        $lojaId = auth()->user()->loja_id;

        $cupons = Cupom::where('loja_id', $lojaId)
            ->latest()
            ->paginate(15);

        return view('painel.cupons.index', compact('cupons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $lojaId = auth()->user()->loja_id;

        $dados = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cupons', 'codigo')->where(fn ($query) => $query->where('loja_id', $lojaId)),
            ],
            'tipo' => ['required', 'in:percentual,fixo'],
            'valor' => ['required', 'numeric', 'min:0'],
            'valor_minimo_pedido' => ['nullable', 'numeric', 'min:0'],
            'limite_uso' => ['nullable', 'integer', 'min:1'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'ativo' => ['boolean'],
        ]);

        Cupom::create([
            ...$dados,
            'loja_id' => $lojaId,
            'user_id' => auth()->id(),
            'quantidade_utilizada' => 0,
        ]);

        return redirect()->route('admin.sales.cupons.index')
            ->with('sucesso', 'Cupom criado com sucesso!');
    }

    public function update(Request $request, Cupom $cupom): RedirectResponse
    {
        $lojaId = auth()->user()->loja_id;

        if ($cupom->loja_id !== $lojaId) {
            abort(403);
        }

        $dados = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cupons', 'codigo')
                    ->ignore($cupom->id)
                    ->where(fn ($query) => $query->where('loja_id', $lojaId)),
            ],
            'tipo' => ['required', 'in:percentual,fixo'],
            'valor' => ['required', 'numeric', 'min:0'],
            'valor_minimo_pedido' => ['nullable', 'numeric', 'min:0'],
            'limite_uso' => ['nullable', 'integer', 'min:1'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'ativo' => ['boolean'],
        ]);

        $cupom->update($dados);

        return redirect()->route('admin.sales.cupons.index')
            ->with('sucesso', 'Cupom atualizado com sucesso!');
    }

    public function toggle(Cupom $cupom): RedirectResponse
    {
        if ($cupom->loja_id !== auth()->user()->loja_id) {
            abort(403);
        }

        $cupom->update(['ativo' => ! $cupom->ativo]);

        return back()->with('sucesso', 'Status do cupom alterado!');
    }

    public function destroy(Cupom $cupom): RedirectResponse
    {
        if ($cupom->loja_id !== auth()->user()->loja_id) {
            abort(403);
        }

        $cupom->delete();

        return back()->with('sucesso', 'Cupom removido com sucesso!');
    }
}
