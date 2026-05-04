<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Finance;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-17
| Descrição: Controller para gestão de Contas Bancárias (Caixa, Bancos, Digitais).
*/

use App\Http\Controllers\Controller;
use App\Models\FinancialAccount;
use App\Models\FinancialPayment;
use App\Services\SaaS\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialAccountController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
    ) {}

    /**
     * Lista todas as contas bancárias da loja.
     */
    public function index(): View
    {
        $contas = FinancialAccount::query()
            ->withCount('pagamentos')
            ->orderBy('ativo', 'desc')
            ->orderBy('nome')
            ->get();

        // Calcular saldo de cada conta
        $contasComSaldo = $contas->map(function ($conta) {
            $entradas = FinancialPayment::query()
                ->where('financial_account_id', $conta->id)
                ->whereHas('titulo', fn($q) => $q->where('tipo', 'receber'))
                ->sum('valor');

            $saidas = FinancialPayment::query()
                ->where('financial_account_id', $conta->id)
                ->whereHas('titulo', fn($q) => $q->where('tipo', 'pagar'))
                ->sum('valor');

            $conta->saldo_calculado = (float) $conta->saldo_inicial + (float) $entradas - (float) $saidas;
            return $conta;
        });

        return view('painel.financeiro.contas.index', [
            'contas' => $contasComSaldo,
        ]);
    }

    /**
     * Cria uma nova conta bancária.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'string', 'in:caixa,banco,digital,carteira'],
            'saldo_inicial' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['loja_id'] = $this->tenantContext->getLojaId() ?? auth()->user()->loja_id;
        $data['saldo_inicial'] = $data['saldo_inicial'] ?? 0;
        $data['ativo'] = true;

        FinancialAccount::create($data);

        return back()->with('sucesso', 'Conta bancária cadastrada com sucesso.');
    }

    /**
     * Atualiza uma conta bancária.
     */
    public function update(Request $request, FinancialAccount $conta): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'string', 'in:caixa,banco,digital,carteira'],
            'saldo_inicial' => ['nullable', 'numeric', 'min:0'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $data['ativo'] = $request->boolean('ativo', true);

        $conta->update($data);

        return back()->with('sucesso', 'Conta bancária atualizada.');
    }

    /**
     * Exclui uma conta bancária (soft delete).
     * Não permite exclusão se houver pagamentos vinculados.
     */
    public function destroy(FinancialAccount $conta): RedirectResponse
    {
        // Verificar se há pagamentos vinculados
        $pagamentosCount = FinancialPayment::where('financial_account_id', $conta->id)->count();

        if ($pagamentosCount > 0) {
            return back()->with('erro', "Não é possível excluir esta conta. Existem {$pagamentosCount} pagamentos vinculados. Desative a conta ao invés de excluí-la.");
        }

        $conta->delete();

        return back()->with('sucesso', 'Conta bancária removida.');
    }

    /**
     * Alterna o status ativo/inativo da conta.
     */
    public function toggle(FinancialAccount $conta): RedirectResponse
    {
        $conta->update(['ativo' => !$conta->ativo]);

        $status = $conta->ativo ? 'ativada' : 'desativada';
        return back()->with('sucesso', "Conta {$status} com sucesso.");
    }
}
