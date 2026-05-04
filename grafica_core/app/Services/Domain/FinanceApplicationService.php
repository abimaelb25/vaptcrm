<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\IntegracaoPagamento;
use App\Models\MovimentacaoFinanceira;
use App\Repositories\FinancialPaymentsConfigRepository;
use App\Repositories\FinancialTransactionRepository;
use App\Repositories\FinancialTitleRepository;
use App\Services\FreteService;
use App\Services\PaymentIntegrationService;
use App\Services\SaaS\FinancePlanService;
use App\Services\SaaS\TenantContext;

class FinanceApplicationService
{
    public function __construct(
        private readonly FinancialTitleRepository $financialTitleRepository,
        private readonly FinancialTransactionRepository $financialTransactionRepository,
        private readonly FinancialPaymentsConfigRepository $financialPaymentsConfigRepository,
        private readonly FreteService $freteService,
        private readonly PaymentIntegrationService $integrationService,
        private readonly FinancePlanService $planService,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * Prepara o payload da tela de pagamentos (somente leitura).
     */
    public function paymentsIndexPayload(): array
    {
        $usuario = auth()->user();
        $userId = $usuario?->id;
        $lojaId = $usuario?->loja_id;

        return [
            'freteConfig' => $this->freteService->getConfig($lojaId),
            'mercadoPago' => $this->integrationService->getActiveIntegration(
                $lojaId,
                IntegracaoPagamento::GATEWAY_MERCADO_PAGO,
                $userId
            ),
            'integrations' => $this->integrationService->getAllByTenant($lojaId, $userId),
            'pixConfig' => $this->financialPaymentsConfigRepository->getPixConfigByLojaWithFallback($lojaId),
            'cuponsAtivos' => $this->financialPaymentsConfigRepository->countActiveCouponsByLoja($lojaId),
            'cupons' => $this->financialPaymentsConfigRepository->getLatestCouponsByLoja($lojaId, 10),
        ];
    }

    /**
     * Prepara todos os dados necessários para a tela de contas a receber.
     */
    public function receivablePayload(?string $status): array
    {
        if (!$this->planService->canUsePro()) {
            return [
                'view' => 'painel.billing.upgrade_needed',
                'data' => ['feature' => 'Gestão de Contas a Receber'],
            ];
        }

        $lojaId = $this->resolveLojaId();

        return [
            'view' => 'painel.financeiro.titulos.index',
            'data' => [
                'tipo' => 'receber',
                'titulos' => $this->financialTitleRepository->paginateReceivablesByLoja($lojaId, $status, 30),
                'categorias' => $this->financialTitleRepository->getReceitaCategoriesByLoja($lojaId),
                'contas' => $this->financialTitleRepository->getActiveAccountsByLoja($lojaId),
            ],
        ];
    }

    /**
     * Prepara o payload do dashboard financeiro com os mesmos campos
     * consumidos pela view existente.
     */
    public function dashboardPayload(string $inicio, string $fim): array
    {
        $lojaId = $this->resolveLojaId();

        return [
            'entradas' => $this->financialTitleRepository->sumPaymentsByTitleTypeInPeriod($lojaId, 'receber', $inicio, $fim),
            'saidas' => $this->financialTitleRepository->sumPaymentsByTitleTypeInPeriod($lojaId, 'pagar', $inicio, $fim),
            'receberTotal' => $this->financialTitleRepository->sumOpenBalanceByType($lojaId, 'receber'),
            'receberVencido' => $this->financialTitleRepository->sumOverdueBalanceByType($lojaId, 'receber'),
            'pagarTotal' => $this->financialTitleRepository->sumOpenBalanceByType($lojaId, 'pagar'),
            'pagarVencido' => $this->financialTitleRepository->sumOverdueBalanceByType($lojaId, 'pagar'),
            'saldoContas' => $this->financialTitleRepository->sumAccountsRealBalance($lojaId),
            'inicio' => $inicio,
            'fim' => $fim,
            'ultimosTitulos' => $this->financialTitleRepository->getLatestTitlesWithCategoryByLoja($lojaId, 10),
        ];
    }

    /**
     * Prepara todos os dados necessários para a tela de contas a pagar.
     */
    public function payablePayload(?string $status): array
    {
        if (!$this->planService->canUsePro()) {
            return [
                'view' => 'painel.billing.upgrade_needed',
                'data' => ['feature' => 'Gestão de Contas a Pagar'],
            ];
        }

        $lojaId = $this->resolveLojaId();

        return [
            'view' => 'painel.financeiro.titulos.index',
            'data' => [
                'tipo' => 'pagar',
                'titulos' => $this->financialTitleRepository->paginatePayablesByLoja($lojaId, $status, 30),
                'categorias' => $this->financialTitleRepository->getDespesaCategoriesByLoja($lojaId),
                'contas' => $this->financialTitleRepository->getActiveAccountsByLoja($lojaId),
            ],
        ];
    }

    /**
     * Prepara o payload da listagem de movimentações financeiras.
     */
    public function transactionIndexPayload(string $inicio, string $fim, ?string $tipo): array
    {
        $lojaId = $this->resolveLojaId();

        return [
            'movimentacoes' => $this->financialTransactionRepository->paginateForIndexByLoja($lojaId, $inicio, $fim, $tipo, 50),
            'recentes' => $this->financialTransactionRepository->getRecentesByLoja($lojaId, 20),
            'saldoAtual' => $this->financialTransactionRepository->sumSaldoPorStatus($lojaId, MovimentacaoFinanceira::STATUS_PAGO),
            'entradasMes' => $this->financialTransactionRepository->sumByTipoStatusNoPeriodo(
                $lojaId,
                MovimentacaoFinanceira::TIPO_ENTRADA,
                MovimentacaoFinanceira::STATUS_PAGO,
                $inicio,
                $fim
            ),
            'saidasMes' => $this->financialTransactionRepository->sumByTipoStatusNoPeriodo(
                $lojaId,
                MovimentacaoFinanceira::TIPO_SAIDA,
                MovimentacaoFinanceira::STATUS_PAGO,
                $inicio,
                $fim
            ),
            'contasPendentes' => $this->financialTransactionRepository->sumSaldoPorStatus($lojaId, MovimentacaoFinanceira::STATUS_PENDENTE),
            'inicio' => $inicio,
            'fim' => $fim,
        ];
    }

    /**
     * Prepara o payload da tela de extrato financeiro.
     */
    public function transactionExtractPayload(?string $tipo, ?string $inicio, ?string $fim): array
    {
        $lojaId = $this->resolveLojaId();

        return [
            'movimentacoes' => $this->financialTransactionRepository->paginateExtratoByLoja(
                $lojaId,
                $tipo,
                $inicio,
                $fim,
                50
            ),
        ];
    }

    private function resolveLojaId(): int
    {
        $lojaId = $this->tenantContext->getLojaId() ?? auth()->user()?->loja_id;

        if (!$lojaId) {
            throw new \RuntimeException(
                'FinanceApplicationService: não foi possível determinar o tenant para o fluxo financeiro.'
            );
        }

        return (int) $lojaId;
    }
}
