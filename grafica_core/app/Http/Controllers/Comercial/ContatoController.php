<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 20:13 -03:00
*/

use App\Http\Controllers\Controller;
use App\Models\Contato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\AuditLogService;

class ContatoController extends Controller
{
    public function index()
    {
        return view('painel.contatos.index', [
            'contatos' => Contato::query()->with(['cliente', 'pedido'])->latest()->paginate(20),
        ]);
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $dados = $request->validate([
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'pedido_id' => ['nullable', 'integer', 'exists:pedidos,id'],
            'tipo_contato' => ['required', 'in:whatsapp,ligacao,email,presencial'],
            'resumo' => ['required', 'string'],
            'proximo_passo' => ['nullable', 'string', 'max:255'],
            'data_retorno' => ['nullable', 'date'],
            'usuario_id' => ['required', 'integer', 'exists:usuarios,id'],
        ]);

        $novoContato = Contato::query()->create($dados);
        
        $audit->log('contatos', 'criacao', $novoContato->id, null, $novoContato->toArray());

        return back()->with('sucesso', 'Contato registrado com sucesso.');
    }
}
