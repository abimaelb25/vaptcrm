<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operacao;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-06 01:10 -03:00
*/

use App\Http\Controllers\Controller;
use App\Models\Tarefa;
use App\Models\Usuario;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TarefaController extends Controller
{
    public function quadroGeral()
    {
        $perfil = strtolower(auth()->user()->perfil ?? '');
        if (!in_array($perfil, ['administrador', 'gerente'], true)) {
            abort(403, 'Acesso negado. Apenas administradores e gerentes podem acessar o Quadro Geral.');
        }

        $tarefas = Tarefa::query()
            ->with(['responsavel', 'solicitante'])
            ->latest()
            ->get()
            ->groupBy('status');

        $statusList = ['backlog', 'a_fazer', 'em_andamento', 'bloqueada', 'concluida', 'cancelada'];
        
        foreach ($statusList as $status) {
            if (!$tarefas->has($status)) {
                $tarefas->put($status, collect());
            }
        }

        return view('painel.tarefas.geral', [
            'tarefas' => $tarefas,
            'usuarios' => Usuario::where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $dados = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'responsavel_id' => ['nullable', 'integer', 'exists:usuarios,id'],
            'status' => ['required', 'string', 'in:backlog,a_fazer,em_andamento,bloqueada,concluida,cancelada'],
            'prioridade' => ['required', 'string', 'in:baixa,media,alta,urgente'],
            'prazo' => ['nullable', 'date'],
            'setor' => ['nullable', 'string', 'max:100'],
        ]);

        $dados['solicitante_id'] = auth()->id();

        // Se o usuário atual não for admin/gerente, ele só pode criar tarefa para si mesmo (opcional).
        // Mas vamos apenas garantir que crie.
        $perfil = strtolower(auth()->user()->perfil ?? '');
        if (!in_array($perfil, ['administrador', 'gerente'], true)) {
            $dados['responsavel_id'] = auth()->id();
        }

        $tarefa = Tarefa::query()->create($dados);
        
        $audit->log('tarefas', 'criacao', $tarefa->id, null, $tarefa->toArray());

        return back()->with('sucesso', 'Tarefa criada com sucesso!');
    }

    public function atualizarStatus(Request $request, Tarefa $tarefa, AuditLogService $audit): RedirectResponse
    {
        $dados = $request->validate([
            'status' => ['required', 'string', 'in:backlog,a_fazer,em_andamento,bloqueada,concluida,cancelada'],
        ]);

        // Verifica permissão. Apenas admin/gerente ou o próprio responsável podem mover.
        $perfil = strtolower(auth()->user()->perfil ?? '');
        if (!in_array($perfil, ['administrador', 'gerente'], true) && $tarefa->responsavel_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }

        $valoresAntigos = $tarefa->toArray();
        $tarefa->update(['status' => $dados['status']]);

        $audit->log('tarefas', 'atualizacao_status', $tarefa->id, $valoresAntigos, $tarefa->fresh()->toArray());

        return back()->with('sucesso', 'Status da tarefa atualizado!');
    }

    public function destroy(Tarefa $tarefa, AuditLogService $audit): RedirectResponse
    {
        // Apenas admin/gerente ou o próprio solicitante
        $perfil = strtolower(auth()->user()->perfil ?? '');
        if (!in_array($perfil, ['administrador', 'gerente'], true) && $tarefa->solicitante_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }

        $valoresAntigos = $tarefa->toArray();
        $tarefa->delete();

        $audit->log('tarefas', 'exclusao', $tarefa->id, $valoresAntigos, null);

        return back()->with('sucesso', 'Tarefa excluída.');
    }
}
