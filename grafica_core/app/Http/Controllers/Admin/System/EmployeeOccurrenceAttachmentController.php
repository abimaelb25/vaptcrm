<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\System;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 11:15
| Descrição: Controller para Anexos de Ocorrências RH
*/

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeOccurrence;
use App\Models\EmployeeOccurrenceAttachment;
use App\Services\RH\EmployeeOccurrenceAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployeeOccurrenceAttachmentController extends Controller
{
    public function __construct(
        protected EmployeeOccurrenceAttachmentService $attachmentService,
    ) {}

    /**
     * Upload de anexo para ocorrência
     * POST /painel/sistema/equipe/{equipe}/ocorrencias/{ocorrencia}/anexos
     */
    public function store(Employee $equipe, EmployeeOccurrence $ocorrencia, Request $request): RedirectResponse
    {
        // Validação: ocorrência pertence ao colaborador e tenant corretos
        if ($ocorrencia->employee_id !== $equipe->id || $ocorrencia->loja_id !== $equipe->loja_id) {
            abort(403, 'Ocorrência não pertence a este colaborador.');
        }

        $request->validate([
            'arquivo' => ['required', 'file', 'max:10240'], // 10MB
            'titulo' => ['required', 'string', 'max:255'],
            'tipo_comprovacao' => ['nullable', 'string'],
            'descricao' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->attachmentService->upload(
                $ocorrencia,
                $request->file('arquivo'),
                $request->titulo,
                $request->tipo_comprovacao,
                $request->descricao
            );

            return back()->with('sucesso', 'Anexo enviado com sucesso.');

        } catch (\Exception $e) {
            return back()
                ->with('erro', 'Erro ao enviar anexo: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Visualizar/Download de anexo
     * GET /painel/sistema/equipe/{equipe}/ocorrencias/{ocorrencia}/anexos/{anexo}/download
     */
    public function download(Employee $equipe, EmployeeOccurrence $ocorrencia, EmployeeOccurrenceAttachment $anexo): Response
    {
        // Validações de segurança
        if ($ocorrencia->employee_id !== $equipe->id || $ocorrencia->loja_id !== $equipe->loja_id) {
            abort(403);
        }

        if ($anexo->employee_occurrence_id !== $ocorrencia->id || $anexo->loja_id !== $equipe->loja_id) {
            abort(403, 'Anexo não pertence a esta ocorrência.');
        }

        $path = storage_path('app/public/' . $anexo->arquivo_path);
        
        if (!file_exists($path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return response()->download($path, $anexo->titulo);
    }

    /**
     * Deleta anexo
     * DELETE /painel/sistema/equipe/{equipe}/ocorrencias/{ocorrencia}/anexos/{anexo}
     */
    public function destroy(Employee $equipe, EmployeeOccurrence $ocorrencia, EmployeeOccurrenceAttachment $anexo): RedirectResponse
    {
        // Validações de segurança
        if ($ocorrencia->employee_id !== $equipe->id || $ocorrencia->loja_id !== $equipe->loja_id) {
            abort(403);
        }

        if ($anexo->employee_occurrence_id !== $ocorrencia->id || $anexo->loja_id !== $equipe->loja_id) {
            abort(403, 'Anexo não pertence a esta ocorrência.');
        }

        try {
            $this->attachmentService->deletar($anexo);
            
            return back()->with('sucesso', 'Anexo removido com sucesso.');

        } catch (\Exception $e) {
            return back()->with('erro', 'Erro ao remover anexo: ' . $e->getMessage());
        }
    }
}
