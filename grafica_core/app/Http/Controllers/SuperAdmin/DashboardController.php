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
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\Plano;

class DashboardController extends Controller
{
    public function index()
    {
        // Estatísticas Básicas
        $totalLojas = Loja::count();
        $lojasTrial = Assinatura::where('status', 'trial')->count();
        $lojasAtivas = Assinatura::where('status', 'active')->count();
        $lojasInadimplentes = Loja::whereNotNull('bloqueada_em')->count();

        // MRR Estimado (Soma mensal das ativas)
        $mrrAtual = Assinatura::where('status', 'active')
            ->join('saas_planos', 'saas_assinaturas.plano_id', '=', 'saas_planos.id')
            ->sum('saas_planos.preco_mensal');

        // Novas Lojas 30 dias
        $novasLojas30Dias = Loja::where('created_at', '>=', now()->subDays(30))->count();

        // Dados para gráfico de planos
        $assinaturasPorPlano = Plano::withCount(['assinaturas' => function($query) {
            $query->whereIn('status', ['active', 'trial', 'past_due']);
        }])->get();

        // Lojas recentes para tabela
        $lojasRecentes = Loja::with('plano')->orderBy('created_at', 'desc')->take(5)->get();

        return view('super-admin.dashboard', compact(
            'totalLojas', 'lojasTrial', 'lojasAtivas', 'lojasInadimplentes',
            'mrrAtual', 'novasLojas30Dias', 'assinaturasPorPlano', 'lojasRecentes'
        ));
    }
}
