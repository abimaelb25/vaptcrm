<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalogo;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 19:53 -03:00
*/

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\SaaS\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConsultaPedidoController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function formulario(): View
    {
        return view('publico.consulta-pedido');
    }

    public function consultar(Request $request): View|RedirectResponse
    {
        $dados = $request->validate([
            'codigo' => ['required', 'string', 'max:30'],
            'contato' => ['required', 'string', 'max:150'],
        ]);

        // SEGURANÇA: Obter loja do contexto atual (subdomínio/domínio)
        $lojaId = $this->tenantContext->getLojaId();
        if (empty($lojaId)) {
            return back()->withErrors([
                'codigo' => 'Não foi possível identificar a loja. Acesse pelo endereço correto.',
            ])->withInput();
        }

        $pedido = Pedido::query()
            ->withoutGlobalScope('loja') // Remove scope para aplicar manualmente com segurança
            ->where('loja_id', $lojaId)  // TENANT SAFETY: Filtro explícito
            ->with(['cliente', 'itens', 'pagamentos'])
            ->where(function ($query) use ($dados) {
                // Busca por codigo_pedido (novo) ou numero (legado)
                $query->where('codigo_pedido', $dados['codigo'])
                      ->orWhere('numero', $dados['codigo']);
            })
            ->whereHas('cliente', function ($query) use ($dados): void {
                $query
                    ->where('telefone', $dados['contato'])
                    ->orWhere('whatsapp', $dados['contato'])
                    ->orWhere('email', $dados['contato']);
            })
            ->first();

        if ($pedido === null) {
            return back()->withErrors([
                'codigo' => 'Não encontramos um pedido com os dados informados.',
            ])->withInput();
        }

        return view('publico.resultado-pedido', [
            'pedido' => $pedido,
        ]);
    }
}
