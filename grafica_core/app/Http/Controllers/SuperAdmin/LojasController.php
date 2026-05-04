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
use App\Services\SaaS\SaaSService;

class LojasController extends Controller
{
    public function __construct(
        private readonly SaaSService $saasService
    ) {}

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

    /**
     * Renovação manual do período trial de uma loja.
     * Autoria: Abimael Borges | https://abimaelborges.adv.br | 2026-04-18
     * 
     * Fluxo de desbloqueio:
     * 1. Busca assinatura ativa ou mais recente (fallback)
     * 2. Atualiza status e trial_ends_at
     * 3. Sincroniza com modelo Loja
     * 4. Invalida TODOS os caches relacionados
     */
    public function renovarTrial(Request $request, Loja $loja)
    {
        $request->validate(['dias' => 'required|integer|min:1|max:90']);
        
        // Tenta buscar assinatura ativa primeiro, depois fallback para a mais recente
        $assinatura = $loja->assinaturaAtiva();
        
        if (!$assinatura) {
            // Fallback: busca a assinatura mais recente independente do status
            $assinatura = $loja->assinaturas()->latest()->first();
        }
        
        if (!$assinatura) {
            return back()->with('error', 'Esta loja não possui nenhuma assinatura vinculada. Crie uma assinatura primeiro.');
        }

        $novaData = now()->addDays((int) $request->dias);

        $assinatura->update([
            'status' => 'trial',
            'trial_ends_at' => $novaData,
        ]);

        // Sincroniza com o modelo Loja para redundância e performance em queries diretas
        $loja->update([
            'trial_ends_at' => $novaData,
        ]);

        // Invalida TODOS os caches relacionados à loja (assinatura, tenant, branding)
        $loja->invalidarTodosOsCaches();

        return back()->with('success', "Trial renovado com sucesso até {$novaData->format('d/m/Y')}. Todos os caches foram invalidados.");
    }

    /**
     * Desbloqueio temporário de todos os limites (SaaS bypass).
     * Autoria: Abimael Borges | https://abimaelborges.adv.br | 2026-04-18
     */
    public function desbloquearLimites(Request $request, Loja $loja)
    {
        $request->validate(['dias' => 'required|integer|min:1|max:365']);
        
        $novaData = now()->addDays((int) $request->dias);

        $loja->update([
            'limites_desbloqueados_ate' => $novaData,
        ]);

        // Invalida TODOS os caches relacionados à loja
        $loja->invalidarTodosOsCaches();

        return back()->with('success', "Limites desbloqueados com sucesso até {$novaData->format('d/m/Y')}.");
    }
}
