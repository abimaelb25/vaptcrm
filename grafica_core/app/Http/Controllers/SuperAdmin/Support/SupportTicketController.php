<?php

namespace App\Http\Controllers\SuperAdmin\Support;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
*/

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $lojaId = $request->query('loja_id');
        $status = $request->query('status', 'abertos');

        // Bypassa HasTenancy no painel master (o scope global se chama 'loja', não 'tenant')
        $query = SupportTicket::withoutGlobalScope('loja')
            ->with(['categoria', 'responsavelMaster', 'user'])
            ->orderBy('ultimo_evento_em', 'desc');

        if ($lojaId) {
            $query->where('loja_id', $lojaId);
        }

        if ($status === 'abertos') {
            $query->whereIn('status', ['aberto', 'aguardando_suporte', 'aguardando_cliente']);
        } else {
            $query->whereIn('status', ['resolvido', 'fechado']);
        }

        $tickets = $query->paginate(20);
        $lojas = Loja::orderBy('nome_fantasia')->get();

        return view('super-admin.support.tickets.index', compact('tickets', 'status', 'lojaId', 'lojas'));
    }

    public function show(SupportTicket $ticket)
    {
        // Garante leitura de tickets globais (scope 'loja' do trait HasTenancy)
        $ticket = SupportTicket::withoutGlobalScope('loja')
            ->with(['mensagens' => function($q) {
                // Remove o scope nas mensagens para o Admin Master ver as do tenant
                $q->withoutGlobalScope('loja')->orderBy('created_at', 'asc');
            }, 'mensagens.autorUser', 'mensagens.autorMaster', 'responsavelMaster'])
            ->findOrFail($ticket->id);
            
        return view('super-admin.support.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $ticket = SupportTicket::withoutGlobalScope('loja')->findOrFail($ticket->id);

        $data = $request->validate([
            'mensagem' => 'required|string',
            'interno' => 'boolean',
            'status' => 'required|in:aberto,aguardando_suporte,aguardando_cliente,resolvido,fechado',
            'anexo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $anexoPath = null;
        if ($request->hasFile('anexo')) {
            $anexoPath = $request->file('anexo')->store('support/tickets', 'public');
        }

        $interno = $data['interno'] ?? false;

        $ticket->mensagens()->withoutGlobalScope('loja')->create([
            'loja_id' => $ticket->loja_id,
            'autor_tipo' => $interno ? 'interno' : 'suporte',
            'autor_master_id' => Auth::id(),
            'mensagem' => $data['mensagem'],
            'interno' => $interno,
            'anexo_path' => $anexoPath,
        ]);

        $ticket->status = $data['status'];
        $ticket->ultimo_evento_em = now();
        
        // Auto-atribui ao atendente que respondeu se não tiver ninguém
        if (!$ticket->responsavel_master_id) {
            $ticket->responsavel_master_id = Auth::id();
        }

        $ticket->save();

        return back()->with('success', 'Resposta enviada com sucesso.');
    }
}
