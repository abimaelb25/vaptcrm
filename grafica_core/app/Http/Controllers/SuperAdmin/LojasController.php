<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:25
*/

use App\Http\Controllers\Controller;
use App\Models\Loja;
use Illuminate\Http\Request;

class LojasController extends Controller
{
    public function index(Request $request)
    {
        $query = Loja::with(['plano', 'assinaturas' => function($q) {
            $q->latest()->limit(1);
        }]);

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('nome_fantasia', 'like', "%{$busca}%")
                  ->orWhere('responsavel_email', 'like', "%{$busca}%")
                  ->orWhere('subdominio', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'bloqueada') {
                $query->whereNotNull('bloqueada_em');
            } else {
                $query->where('status', $request->status)->whereNull('bloqueada_em');
            }
        }

        $lojas = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('super-admin.lojas.index', compact('lojas'));
    }

    public function show(Loja $loja)
    {
        $loja->load([
            'plano', 
            'assinaturas.plano',
            'pagamentosSaaS' => function($q) { $q->latest()->limit(10); },
            'consumoMetricas' => function($q) { $q->latest()->limit(12); },
            'notificacoesInadimplencia' => function($q) { $q->latest()->limit(5); }
        ]);

        $assinaturaAtual = $loja->assinaturas->first();

        // Quantidades
        $totalUsuarios = $loja->usuarios()->count();
        $totalProdutos = $loja->produtos()->count();
        $totalPedidos = $loja->pedidos()->count();

        return view('super-admin.lojas.show', compact('loja', 'assinaturaAtual', 'totalUsuarios', 'totalProdutos', 'totalPedidos'));
    }

    public function bloquear(Request $request, Loja $loja)
    {
        $request->validate(['motivo' => 'required|string|max:255']);
        $loja->bloquear($request->motivo);

        return back()->with('success', 'Loja bloqueada com sucesso.');
    }

    public function desbloquear(Loja $loja)
    {
        $loja->desbloquear();

        return back()->with('success', 'Loja desbloqueada com sucesso.');
    }
}
