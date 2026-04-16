<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:25
*/

use App\Http\Controllers\Controller;
use App\Models\SaaS\Plano;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanosController extends Controller
{
    public function index()
    {
        $planos = Plano::withCount(['assinaturas' => function($q) {
            $q->whereIn('status', ['active', 'trial', 'past_due']);
        }])->orderBy('preco_mensal')->get();

        return view('super-admin.planos.index', compact('planos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'preco_mensal' => 'required|numeric|min:0',
            'stripe_price_id' => 'nullable|string|max:255',
            'limite_produtos' => 'nullable|integer|min:1',
            'limite_funcionarios' => 'nullable|integer|min:1',
        ]);

        $data['slug'] = Str::slug($data['nome']);
        $data['ativo'] = true;

        Plano::create($data);

        return back()->with('success', 'Plano criado com sucesso.');
    }

    public function update(Request $request, Plano $plano)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'preco_mensal' => 'required|numeric|min:0',
            'stripe_price_id' => 'nullable|string|max:255',
            'limite_produtos' => 'nullable|integer|min:1',
            'limite_funcionarios' => 'nullable|integer|min:1',
            'ativo' => 'boolean',
        ]);

        if ($request->nome !== $plano->nome) {
            $data['slug'] = Str::slug($data['nome']);
        }

        $plano->update($data);

        return back()->with('success', 'Plano atualizado com sucesso.');
    }

    public function destroy(Plano $plano)
    {
        if ($plano->totalAssinantesAtivos() > 0) {
            return back()->with('error', 'Não é possível excluir um plano com assinaturas ativas.');
        }

        $plano->delete();
        return back()->with('success', 'Plano removido com sucesso.');
    }
}
