<?php

namespace App\Http\Controllers\Admin\Support;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
*/

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreTicketController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'abertos');
        
        $query = SupportTicket::with(['categoria', 'responsavelMaster'])
            ->orderBy('ultimo_evento_em', 'desc');

        if ($status === 'abertos') {
            $query->abertos();
        } else {
            $query->whereIn('status', ['resolvido', 'fechado']);
        }

        $tickets = $query->paginate(15);

        return view('painel.support.tickets.index', compact('tickets', 'status'));
    }

    public function create()
    {
        $categorias = SupportCategory::where('ativo', true)->orderBy('nome')->get();
        return view('painel.support.tickets.form', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'assunto' => 'required|string|max:255',
            'categoria_id' => 'required|exists:support_categories,id',
            'prioridade' => 'required|in:baixa,media,alta,urgente',
            'mensagem' => 'required|string',
            'anexo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // máximo 5MB
        ]);

        $anexoPath = null;
        if ($request->hasFile('anexo')) {
            $anexoPath = $request->file('anexo')->store('support/tickets', 'public');
        }

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'assunto' => $data['assunto'],
            'categoria_id' => $data['categoria_id'],
            'prioridade' => $data['prioridade'],
            'status' => 'aberto',
            'ultimo_evento_em' => now(),
        ]);

        $ticket->mensagens()->create([
            'loja_id' => $ticket->loja_id,
            'autor_tipo' => 'cliente',
            'autor_user_id' => Auth::id(),
            'mensagem' => $data['mensagem'],
            'anexo_path' => $anexoPath,
        ]);

        return redirect()->route('admin.support.meus-tickets.show', $ticket)
            ->with('success', 'Ticket aberto com sucesso! Nossa equipe responderá em breve.');
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['mensagens.autorUser', 'mensagens.autorMaster', 'responsavelMaster']);
        return view('painel.support.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'mensagem' => 'required|string',
            'anexo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $anexoPath = null;
        if ($request->hasFile('anexo')) {
            $anexoPath = $request->file('anexo')->store('support/tickets', 'public');
        }

        $ticket->mensagens()->create([
            'loja_id' => $ticket->loja_id,
            'autor_tipo' => 'cliente',
            'autor_user_id' => Auth::id(),
            'mensagem' => $data['mensagem'],
            'anexo_path' => $anexoPath,
        ]);

        if (in_array($ticket->status, ['resolvido', 'aguardando_cliente'])) {
            $ticket->status = 'aberto';
        }

        $ticket->ultimo_evento_em = now();
        $ticket->save();

        return back()->with('success', 'Mensagem enviada com sucesso.');
    }
}
