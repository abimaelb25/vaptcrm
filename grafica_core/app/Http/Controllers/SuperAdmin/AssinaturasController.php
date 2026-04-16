<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:25
*/

use App\Http\Controllers\Controller;
use App\Models\SaaS\Assinatura;
use Illuminate\Http\Request;

class AssinaturasController extends Controller
{
    public function index(Request $request)
    {
        $query = Assinatura::with(['loja', 'plano']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plano_id')) {
            $query->where('plano_id', $request->plano_id);
        }

        $assinaturas = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('super-admin.assinaturas.index', compact('assinaturas'));
    }

    public function show(Assinatura $assinatura)
    {
        $assinatura->load(['loja', 'plano']);
        $pagamentos = $assinatura->pagamentos()->orderBy('vencimento_em', 'desc')->paginate(10);

        return view('super-admin.assinaturas.show', compact('assinatura', 'pagamentos'));
    }
}
