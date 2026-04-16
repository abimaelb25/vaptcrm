<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Finance;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-14T18:20:00-03:00
*/

use App\Http\Controllers\Controller;
use App\Models\IntegracaoPagamento;
use App\Models\Cupom;
use App\Models\SiteConfiguracao;
use App\Services\FreteService;
use App\Services\MercadoPagoConfigService;
use App\Services\PaymentIntegrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PagamentosController extends Controller
{
    public function __construct(
        protected FreteService $freteService,
        protected PaymentIntegrationService $integrationService,
        protected MercadoPagoConfigService $mercadoPagoService
    ) {}

    public function index(): View
    {
        $usuario = auth()->user();
        $userId = $usuario?->id;
        $lojaId = $usuario?->loja_id;

        $freteConfig = $this->freteService->getConfig($lojaId);
        $mercadoPago = $this->integrationService->getActiveIntegration(
            $lojaId,
            IntegracaoPagamento::GATEWAY_MERCADO_PAGO,
            $userId
        );
        $integrations = $this->integrationService->getAllByTenant($lojaId, $userId);

        $pixConfig = $this->buscarPixConfig($lojaId);

        $cuponsAtivos = Cupom::query()
            ->when($lojaId, fn ($query) => $query->where('loja_id', $lojaId))
            ->where('ativo', true)
            ->count();

        $cupons = Cupom::query()
            ->when($lojaId, fn ($query) => $query->where('loja_id', $lojaId))
            ->latest()
            ->take(10)
            ->get();

        return view('painel.pagamentos.index', compact(
            'freteConfig',
            'mercadoPago',
            'integrations',
            'pixConfig',
            'cuponsAtivos',
            'cupons'
        ));
    }

    public function updateFrete(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'ativo' => ['required', 'boolean'],
            'valor' => ['required_if:ativo,1', 'numeric', 'min:0'],
            'obrigatorio' => ['required', 'boolean'],
        ]);

        $this->freteService->saveConfig(auth()->user()->loja_id, [
            'ativo' => $request->boolean('ativo'),
            'valor' => (float) $request->input('valor', 0),
            'obrigatorio' => $request->boolean('obrigatorio'),
        ]);

        return back()->with('sucesso', 'Configurações de frete atualizadas!');
    }

    public function updateMercadoPago(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'ativo' => ['required', 'boolean'],
            'ambiente' => ['required', 'in:sandbox,producao'],
            'public_key' => ['nullable', 'string', 'max:255'],
            'access_token' => ['nullable', 'string', 'max:255'],
            'pix' => ['boolean'],
            'cartao' => ['boolean'],
            'boleto' => ['boolean'],
        ]);

        $this->integrationService->saveOrUpdate([
            'gateway' => IntegracaoPagamento::GATEWAY_MERCADO_PAGO,
            'ativo' => $request->boolean('ativo'),
            'ambiente' => $request->input('ambiente'),
            'credenciais' => [
                'public_key' => $request->input('public_key'),
                'access_token' => $request->input('access_token'),
            ],
            'config_json' => [
                'pix' => $request->boolean('pix', true),
                'cartao' => $request->boolean('cartao', true),
                'boleto' => $request->boolean('boleto', true),
            ],
        ], auth()->user()->loja_id, auth()->id());

        return back()->with('sucesso', 'Credenciais Mercado Pago salvas com sucesso!');
    }

    public function testarMercadoPago(): RedirectResponse
    {
        $resultado = $this->mercadoPagoService->testConnection(auth()->user()->loja_id, auth()->id());

        if ($resultado['sucesso']) {
            return back()->with('sucesso', $resultado['mensagem']);
        }

        return back()->with('erro', $resultado['mensagem']);
    }

    public function updatePix(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'pix_chave'        => ['nullable', 'string', 'max:150'],
            'pix_tipo'         => ['nullable', 'in:cpf,cnpj,email,telefone,aleatoria'],
            'pix_beneficiario' => ['nullable', 'string', 'max:100'],
            'pix_cidade'       => ['nullable', 'string', 'max:50'],
        ]);

        $lojaId = auth()->user()->loja_id;

        // Grava nos mesmos campos usados pelo módulo de Aparência
        SiteConfiguracao::updateOrCreate(
            ['loja_id' => $lojaId, 'chave' => 'empresa_pix_chave'],
            ['valor' => $dados['pix_chave'] ?? '', 'tipo' => 'texto']
        );

        SiteConfiguracao::updateOrCreate(
            ['loja_id' => $lojaId, 'chave' => 'empresa_pix_tipo'],
            ['valor' => $dados['pix_tipo'] ?? '', 'tipo' => 'texto']
        );

        SiteConfiguracao::updateOrCreate(
            ['loja_id' => $lojaId, 'chave' => 'empresa_beneficiario'],
            ['valor' => $dados['pix_beneficiario'] ?? '', 'tipo' => 'texto']
        );

        SiteConfiguracao::updateOrCreate(
            ['loja_id' => $lojaId, 'chave' => 'empresa_cidade'],
            ['valor' => $dados['pix_cidade'] ?? '', 'tipo' => 'texto']
        );

        Cache::forget('site_configuracoes_todas');

        return back()->with('sucesso', 'Configurações de PIX atualizadas com sucesso! O PDV agora gerará QR Codes com estes dados.');
    }

    private function buscarPixConfig(?int $lojaId): array
    {
        $pix = SiteConfiguracao::query()
            ->when($lojaId, fn ($query) => $query->where('loja_id', $lojaId))
            ->whereIn('chave', ['empresa_pix_chave', 'empresa_pix_tipo', 'empresa_beneficiario', 'empresa_cidade'])
            ->pluck('valor', 'chave')
            ->toArray();

        if (empty($pix)) {
            $pix = SiteConfiguracao::query()
                ->whereNull('loja_id')
                ->whereIn('chave', ['empresa_pix_chave', 'empresa_pix_tipo', 'empresa_beneficiario', 'empresa_cidade'])
                ->pluck('valor', 'chave')
                ->toArray();
        }

        return [
            'chave' => $pix['empresa_pix_chave'] ?? '',
            'tipo' => $pix['empresa_pix_tipo'] ?? '',
            'beneficiario' => $pix['empresa_beneficiario'] ?? '',
            'cidade' => $pix['empresa_cidade'] ?? '',
        ];
    }
}
