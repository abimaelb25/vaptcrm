<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\System;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 10:15
| Descrição: Controller para Ocorrências RH (leve, delegando lógica ao Service)
*/

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeOccurrence;
use App\Services\RH\EmployeeOccurrenceService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class EmployeeOccurrenceController extends Controller
{
    public function __construct(
        protected EmployeeOccurrenceService $occurrenceService,
    ) {}

    /**
     * Lista ocorrências de um colaborador (GET /painel/sistema/equipe/{equipe}/ocorrencias)
     */
    public function index(Employee $equipe, Request $request): View
    {
        $equipe->load('usuario');
        $permissoes = $this->occurrenceService->obterPermissoes($equipe);
        
        // Proteção: verifica se a tabela existe (migrações não rodaram)
        if (!Schema::hasTable('employee_occurrences')) {
            return view('painel.funcionarios.ocorrencias.index', [
                'funcionario' => $equipe,
                'ocorrencias' => new Paginator([], 15, 1),
                'tipoFiltro' => $request->tipo,
                'tabelaNaoExiste' => true,
                'permissoesOcorrencia' => $permissoes,
            ]);
        }

        $ocorrencias = $this->occurrenceService->obterPaginado(
            $equipe,
            $request->filled('tipo') ? (string) $request->tipo : null,
            15
        );

        return view('painel.funcionarios.ocorrencias.index', [
            'funcionario' => $equipe,
            'ocorrencias' => $ocorrencias,
            'tipoFiltro' => $request->tipo,
            'tabelaNaoExiste' => false,
            'permissoesOcorrencia' => $permissoes,
        ]);
    }

    /**
     * Formulário de criação (GET /painel/sistema/equipe/{equipe}/ocorrencias/create)
     */
    public function create(Employee $equipe): View|RedirectResponse
    {
        // Proteção: verifica se a tabela existe
        if (!Schema::hasTable('employee_occurrences')) {
            return redirect()
                ->route('admin.system.equipe.ocorrencias.index', $equipe->id)
                ->with('erro', 'O módulo de Ocorrências RH ainda não foi configurado. Execute: php artisan migrate');
        }

        $this->occurrenceService->criarPermissaoApenasValidacao($equipe);

        return view('painel.funcionarios.ocorrencias.form', [
            'funcionario' => $equipe,
            'ocorrencia' => new EmployeeOccurrence(),
        ]);
    }

    /**
     * Armazena nova ocorrência (POST /painel/sistema/equipe/{equipe}/ocorrencias)
     */
    public function store(Employee $equipe, Request $request): RedirectResponse
    {
        // Proteção: verifica se a tabela existe
        if (!Schema::hasTable('employee_occurrences')) {
            return redirect()
                ->route('admin.system.equipe.ocorrencias.index', $equipe->id)
                ->with('erro', 'O módulo de Ocorrências RH ainda não foi configurado. Execute: php artisan migrate');
        }

        $validData = $request->validate([
            'tipo' => ['required', 'string', 'in:advertencia,suspensao,falta,atestado,desligamento'],
            'subtipo' => ['nullable', 'string'],
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'data_ocorrencia' => ['required', 'date', 'before_or_equal:today'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:registrada,em_analise,resolvida,contestada,arquivada'],
            'referencia_documento' => ['nullable', 'string', 'max:100'],
            'revogar_acesso' => ['nullable', 'boolean'],
        ]);

        try {
            $this->occurrenceService->criar($equipe, $validData);

            return redirect()
                ->route('admin.system.equipe.ocorrencias.index', $equipe->id)
                ->with('sucesso', 'Ocorrência registrada com sucesso.');

        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->with('erro', 'Erro ao registrar ocorrência: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Formulário de edição (GET /painel/sistema/equipe/{equipe}/ocorrencias/{ocorrencia}/edit)
     */
    public function edit(Employee $equipe, EmployeeOccurrence $ocorrencia): View
    {
        // Validação: ocorrência pertence ao colaborador
        if ($ocorrencia->employee_id !== $equipe->id || $ocorrencia->loja_id !== $equipe->loja_id) {
            abort(403, 'Ocorrência não pertence a este colaborador.');
        }

        $this->occurrenceService->editarPermissaoApenasValidacao($ocorrencia);

        return view('painel.funcionarios.ocorrencias.form', [
            'funcionario' => $equipe,
            'ocorrencia' => $ocorrencia,
        ]);
    }

    /**
     * Atualiza ocorrência (PUT/PATCH /painel/sistema/equipe/{equipe}/ocorrencias/{ocorrencia})
     */
    public function update(Employee $equipe, EmployeeOccurrence $ocorrencia, Request $request): RedirectResponse
    {
        // Validação: ocorrência pertence ao colaborador
        if ($ocorrencia->employee_id !== $equipe->id || $ocorrencia->loja_id !== $equipe->loja_id) {
            abort(403, 'Ocorrência não pertence a este colaborador.');
        }

        $validData = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', 'in:registrada,em_analise,resolvida,contestada,arquivada'],
            'referencia_documento' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $this->occurrenceService->atualizar($ocorrencia, $validData);

            return redirect()
                ->route('admin.system.equipe.ocorrencias.index', $equipe->id)
                ->with('sucesso', 'Ocorrência atualizada com sucesso.');

        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->with('erro', 'Erro ao atualizar ocorrência: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Deleta ocorrência (DELETE /painel/sistema/equipe/{equipe}/ocorrencias/{ocorrencia})
     */
    public function destroy(Employee $equipe, EmployeeOccurrence $ocorrencia): RedirectResponse
    {
        // Validação: ocorrência pertence ao colaborador
        if ($ocorrencia->employee_id !== $equipe->id || $ocorrencia->loja_id !== $equipe->loja_id) {
            abort(403, 'Ocorrência não pertence a este colaborador.');
        }

        try {
            $this->occurrenceService->deletar($ocorrencia);

            return redirect()
                ->route('admin.system.equipe.ocorrencias.index', $equipe->id)
                ->with('sucesso', 'Ocorrência removida com sucesso.');

        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('erro', 'Erro ao remover ocorrência: ' . $e->getMessage());
        }
    }
}
