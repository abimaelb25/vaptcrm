<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Catalog;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Produto;
use App\Modules\Produtos\Services\ProductApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductApplicationService $applicationService,
    ) {
        $this->authorizeResource(Produto::class, 'produto');
    }

    public function index(): View
    {
        return view('painel.produtos.index', $this->applicationService->indexPayload());
    }

    public function create(): View
    {
        return view('painel.produtos.create', $this->applicationService->createPayload());
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        if (!$this->applicationService->canCreateProduct()) {
            return back()->with('erro', 'Limite de produtos atingido. Faça um upgrade.')->withInput();
        }

        if (!$this->applicationService->canUseAdvancedFor((string) $request->modelo_cadastro)) {
            return back()->with('erro', 'Seu plano atual não permite produtos configuráveis ou técnicos.')->withInput();
        }

        try {
            $dados = $this->dadosProduto($request);
            $this->applicationService->save($dados);
            return redirect()->route('admin.catalog.produtos.index')->with('sucesso', 'Produto criado com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('erro', 'Erro ao salvar: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Produto $produto): View
    {
        return view('painel.produtos.edit', $this->applicationService->editPayload($produto));
    }

    public function update(ProductRequest $request, Produto $produto): RedirectResponse
    {
        if (!$this->applicationService->canUseAdvancedFor((string) $request->modelo_cadastro)) {
            return back()->with('erro', 'Seu plano atual não permite o uso do módulo Avançado.');
        }

        try {
            $dados = $this->dadosProduto($request);
            $this->applicationService->save($dados, $produto);
            return redirect()->route('admin.catalog.produtos.index')->with('sucesso', 'Produto atualizado.');
        } catch (\Throwable $e) {
            return back()->with('erro', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    /**
     * Clona um produto existente para agilizar o cadastro.
     * Autoria: Abimael Borges | https://abimaelborges.adv.br
     */
    public function duplicate(Produto $produto): RedirectResponse
    {
        $this->authorize('update', $produto);

        if (!$this->applicationService->canCreateProduct()) {
            return back()->with('erro', 'Limite de produtos atingido para seu plano.');
        }

        try {
            $newProduct = $this->applicationService->duplicate($produto);

            return redirect()->route('admin.catalog.produtos.edit', $newProduct->id)->with('sucesso', 'Produto clonado com sucesso! Ajuste os detalhes conforme necessário.');
        } catch (\Throwable $e) {
            return back()->with('erro', 'Erro ao duplicar produto: ' . $e->getMessage());
        }
    }

    /**
     * Normaliza dados do formulário incluindo uploads.
     */
    private function dadosProduto(ProductRequest $request): array
    {
        $dados = $request->validated();
        if ($request->hasFile('imagem_destaque')) {
            $dados['imagem_destaque'] = $request->file('imagem_destaque');
        }

        if ($request->hasFile('imagens_adicionais')) {
            $dados['imagens_adicionais'] = array_values(array_filter(
                $request->file('imagens_adicionais'),
                fn ($file) => $file !== null
            ));
        }

        return $dados;
    }

    public function toggleAtivo(Produto $produto): RedirectResponse
    {
        $this->authorize('update', $produto);
        $this->applicationService->toggle($produto);
        return back()->with('sucesso', 'Status atualizado!');
    }

    public function destroy(Produto $produto): RedirectResponse
    {
        $this->authorize('delete', $produto);
        $this->applicationService->remove($produto);
        return back()->with('sucesso', 'Produto removido.');
    }
}

