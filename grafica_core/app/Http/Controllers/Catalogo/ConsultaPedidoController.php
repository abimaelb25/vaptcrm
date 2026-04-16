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
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConsultaPedidoController extends Controller
{
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

        $pedido = Pedido::query()
            ->with(['cliente', 'itens', 'pagamentos'])
            ->where('numero', $dados['codigo'])
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
