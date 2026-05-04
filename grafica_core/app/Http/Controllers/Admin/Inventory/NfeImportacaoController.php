<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\ConfirmNfeImportacaoRequest;
use App\Http\Requests\Admin\Inventory\UploadNfeXmlRequest;
use App\Models\Insumo;
use App\Models\NfeImportacao;
use App\Services\Domain\NfeXmlImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NfeImportacaoController extends Controller
{
    public function __construct(
        private readonly NfeXmlImportService $nfeXmlImportService,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Insumo::class);

        $lojaId = (int) auth()->user()->loja_id;

        $importacoes = NfeImportacao::where('loja_id', $lojaId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('painel.estoque.nfe.index', compact('importacoes'));
    }

    public function create(): View
    {
        $this->authorize('viewAny', Insumo::class);

        return view('painel.estoque.nfe.importar');
    }

    public function preview(UploadNfeXmlRequest $request): RedirectResponse
    {
        $this->authorize('create', Insumo::class);

        $lojaId = (int) auth()->user()->loja_id;
        $usuarioId = (int) auth()->id();

        $importacao = $this->nfeXmlImportService->criarPreview(
            $request->file('xml_file'),
            $lojaId,
            $usuarioId
        );

        return redirect()->route('admin.inventory.nfe-importacao.show', $importacao)
            ->with('sucesso', 'XML lido com sucesso. Revise os itens antes de confirmar.');
    }

    public function show(NfeImportacao $nfeImportacao): View
    {
        $this->authorize('viewAny', Insumo::class);

        $dados = $this->nfeXmlImportService->montarDadosPreview($nfeImportacao, (int) auth()->user()->loja_id);

        return view('painel.estoque.nfe.preview', [
            'importacao' => $nfeImportacao,
            'payload' => $dados['payload'],
            'itens' => $dados['itens'],
            'insumosAtivos' => $dados['insumos_ativos'],
            'fornecedorExistente' => $dados['fornecedor_existente'],
            'alertas' => $dados['alertas'],
        ]);
    }

    public function confirmar(ConfirmNfeImportacaoRequest $request, NfeImportacao $nfeImportacao): RedirectResponse
    {
        $this->authorize('create', Insumo::class);

        $this->nfeXmlImportService->confirmarImportacao(
            $nfeImportacao,
            (array) $request->validated('items'),
            (int) auth()->user()->loja_id,
            (int) auth()->id(),
        );

        return redirect()->route('admin.inventory.movimentacoes.index')
            ->with('sucesso', 'Importacao confirmada. Estoque e custos foram atualizados.');
    }

    public function reabrir(NfeImportacao $nfeImportacao): RedirectResponse
    {
        $this->authorize('create', Insumo::class);

        $this->nfeXmlImportService->reabrirImportacao($nfeImportacao);

        return redirect()->route('admin.inventory.nfe-importacao.show', $nfeImportacao)
            ->with('sucesso', 'Nota reaberta. Revise os itens e confirme novamente para atualizar estoque e custos.');
    }
}
