<?php

declare(strict_types=1);

namespace App\Http\Controllers\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:31
*/

use App\Http\Controllers\Controller;
use App\Models\Loja;
use App\Models\SaaS\Plano;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\PagamentoSaaS;
use App\Services\SaaS\CommercialSubscriptionService;
use App\Services\SaaS\PlanService;
use App\Services\SaaS\SaaSService;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssinaturaController extends Controller
{
    public function __construct(
        protected SaaSService $saasService,
        protected PlanService $planService,
        protected StripeService $stripeService,
        protected CommercialSubscriptionService $commercialSubscriptionService,
    ) {}

    /**
     * Tela principal de gerenciamento de assinatura.
     */
    public function index()
    {
        $assinatura = $this->saasService->getAssinatura();
        $planos = Plano::where('ativo', true)->orderBy('preco_mensal')->get();

        $snapshots = $this->planService->usageDashboard();
        $usage = [
            'produtos' => $snapshots['produtos'],
            'funcionarios' => $snapshots['usuarios'],
            'pedidos_mes' => $snapshots['pedidos_mes'],
            'storage_mb' => $snapshots['storage_mb'],
            'storage_policy' => $snapshots['storage_policy'],
        ];

        $pagamentos = PagamentoSaaS::query()
            ->where('loja_id', Auth::user()?->loja_id)
            ->latest('vencimento_em')
            ->take(10)
            ->get();

        $alerts = collect([
            $usage['storage_policy']['message'] ?? null,
            ($usage['produtos']['percent'] ?? 0) >= 90 ? 'Você está perto do limite de produtos. Faça upgrade para continuar crescendo.' : null,
            ($usage['funcionarios']['percent'] ?? 0) >= 90 ? 'Sua equipe está no limite do plano. Considere upgrade para adicionar novos usuários.' : null,
            ($usage['pedidos_mes']['percent'] ?? 0) >= 90 ? 'Seu volume mensal de pedidos está próximo do teto do plano.' : null,
        ])->filter()->values()->all();

        $upgradeRecommended = ! empty($alerts);

        return view('painel.assinatura.index', compact('assinatura', 'planos', 'usage', 'alerts', 'upgradeRecommended', 'pagamentos'));
    }

    public function previewDowngrade(Plano $plano)
    {
        $loja = Loja::query()->findOrFail(Auth::user()->loja_id);
        $check = $this->commercialSubscriptionService->validateDowngrade($loja, $plano);

        return response()->json([
            'success' => true,
            'allowed' => $check['allowed'],
            'violations' => $check['violations'],
        ]);
    }

    public function changePlan(Request $request, Plano $plano): RedirectResponse
    {
        $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
            'strict_downgrade' => 'nullable|boolean',
        ]);

        $loja = Loja::query()->findOrFail(Auth::user()->loja_id);

        $result = $this->commercialSubscriptionService->changePlan(
            $loja,
            $plano,
            (string) $request->input('billing_cycle', Assinatura::BILLING_MONTHLY),
            (bool) $request->boolean('strict_downgrade', false)
        );

        $violations = $result['violations'];
        $prorata = $result['prorata'];

        if ($violations !== []) {
            return redirect()
                ->route('admin.billing.index')
                ->with('warning', 'Plano alterado com ajuste pendente. Reduza o consumo para se adequar aos novos limites.');
        }

        $msg = 'Plano alterado com sucesso.';
        if ($prorata > 0) {
            $msg .= ' Pró-rata gerado: R$ ' . number_format($prorata, 2, ',', '.');
        }

        return redirect()->route('admin.billing.index')->with('success', $msg);
    }

    /**
     * Inicia o checkout de um plano.
     */
    public function assinar(Plano $plano)
    {
        // SEGURANÇA: Verifica se o plano existe e está ativo
        if (! $plano->exists || ! $plano->ativo) {
            return redirect()->route('admin.billing.index')->with('erro', 'O plano selecionado é inválido ou não está mais disponível.');
        }

        $assinatura = $this->saasService->getAssinatura();
        
        // MUDANÇA DE PLANO (UPGRADE/DOWNGRADE)
        // Se o cliente já tem um faturamento ativo no Stripe, redirecionamos para o Portal
        // Lá ele pode trocar de plano com cálculo pro-rata automático pelo próprio Stripe.
        if ($assinatura->stripe_subscription_id) {
            return $this->portal();
        }

        // SEGURANÇA: Evita assinar o mesmo plano caso já esteja ativo (para Trial ou outros status)
        if ($assinatura->plano_id === $plano->id && $assinatura->status === 'active') {
            return redirect()->route('admin.billing.index')->with('erro', 'Você já possui uma assinatura ativa para este plano.');
        }

        // SEGURANÇA: Validação de ID do Stripe (prevenção de erros de API)
        if (empty($plano->stripe_price_id)) {
            return redirect()->route('admin.billing.index')->with('erro', 'Este plano ainda não foi configurado para pagamentos online. Entre em contato com o suporte.');
        }

        try {
            $stripeCustomerId = !empty($assinatura->stripe_customer_id) ? $assinatura->stripe_customer_id : null;
            $checkout = $this->stripeService->criarCheckoutAssinatura($plano, $stripeCustomerId);
            return redirect()->away($checkout['url']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[SaaS] Falha no checkout:', [
                'plano_id' => $plano->id,
                'error'    => $e->getMessage()
            ]);
            return back()->with('erro', 'Erro ao iniciar o processo de pagamento. Tente novamente em instantes.');
        }
    }

    /**
     * Redireciona para o Portal de Faturamento do Stripe.
     */
    public function portal()
    {
        $assinatura = $this->saasService->getAssinatura();

        if (! $assinatura->stripe_customer_id) {
            return back()->with('erro', 'Você ainda não possui um faturamento ativo no Stripe.');
        }

        try {
            $url = $this->stripeService->portalCliente($assinatura->stripe_customer_id);
            return redirect()->away($url);
        } catch (\Exception $e) {
            return back()->with('erro', 'Erro ao acessar o portal: ' . $e->getMessage());
        }
    }
}
