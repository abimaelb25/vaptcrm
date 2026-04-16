<?php

declare(strict_types=1);

namespace App\Http\Controllers\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:31
*/

use App\Http\Controllers\Controller;
use App\Models\SaaS\Plano;
use App\Models\Produto;
use App\Models\Usuario;
use App\Services\SaaS\SaaSService;
use App\Services\StripeService;
use Illuminate\Http\Request;

class AssinaturaController extends Controller
{
    public function __construct(
        protected SaaSService $saasService,
        protected StripeService $stripeService
    ) {}

    /**
     * Tela principal de gerenciamento de assinatura.
     */
    public function index()
    {
        $assinatura = $this->saasService->getAssinatura();
        $planos = Plano::where('ativo', true)->orderBy('preco_mensal')->get();
        
        // Métricas atuais vs Limites
        $uso = [
            'produtos' => [
                'atual' => Produto::where('ativo', true)->count(),
                'limite' => $assinatura->plano->limite_produtos,
                'porcentagem' => $assinatura->plano->temLimiteProdutos() 
                    ? min(100, (Produto::where('ativo', true)->count() / $assinatura->plano->limite_produtos) * 100)
                    : 0
            ],
            'funcionarios' => [
                'atual' => Usuario::where('ativo', true)->count(),
                'limite' => $assinatura->plano->limite_funcionarios,
                'porcentagem' => $assinatura->plano->temLimiteFuncionarios()
                    ? min(100, (Usuario::where('ativo', true)->count() / $assinatura->plano->limite_funcionarios) * 100)
                    : 0
            ]
        ];

        return view('painel.assinatura.index', compact('assinatura', 'planos', 'uso'));
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
