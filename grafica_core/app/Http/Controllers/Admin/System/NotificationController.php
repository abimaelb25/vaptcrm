<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\System;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Controller de notificações internas (CRM - sino).
|            Suporta renderização HTML (painel) e resposta JSON (AJAX/bell icon).
*/

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Lista todas as notificações do usuário logado.
     */
    public function index(Request $request)
    {
        $notificacoes = Auth::user()->notifications()->paginate(20);

        if ($request->expectsJson()) {
            return response()->json([
                'notificacoes'  => $notificacoes->items(),
                'total'         => $notificacoes->total(),
                'nao_lidas'     => Auth::user()->unreadNotifications()->count(),
            ]);
        }

        return view('painel.notificacoes.index', compact('notificacoes'));
    }

    /**
     * Retorna contagem de notificações não lidas (para badge do sino).
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Retorna as últimas notificações não lidas (dropdown do sino).
     */
    public function recent(): JsonResponse
    {
        $notificacoes = Auth::user()
            ->unreadNotifications()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id'        => $n->id,
                'tipo'      => $n->data['tipo'] ?? 'default',
                'mensagem'  => $n->data['mensagem'] ?? 'Notificação de sistema',
                'pedido_id' => $n->data['pedido_id'] ?? null,
                'tempo'     => $n->created_at->diffForHumans(),
                'lida'      => $n->read_at !== null,
            ]);

        return response()->json([
            'notificacoes' => $notificacoes,
            'total_nao_lidas' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Marca uma notificação específica como lida.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['sucesso' => true]);
        }

        return back()->with('sucesso', 'Notificação marcada como lida.');
    }

    /**
     * Marca todas as notificações como lidas.
     */
    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['sucesso' => true]);
        }

        return back()->with('sucesso', 'Todas as notificações foram lidas.');
    }

    /**
     * Remove uma notificação.
     */
    public function destroy(Request $request, string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        if ($request->expectsJson()) {
            return response()->json(['sucesso' => true]);
        }

        return back()->with('sucesso', 'Notificação removida.');
    }
}
