<?php

declare(strict_types=1);

namespace App\Services\RH;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 10:10
| Descrição: Service para gerenciamento de Ocorrências RH
*/

use App\Models\Employee;
use App\Models\EmployeeOccurrence;
use App\Models\EmployeeHistory;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeOccurrenceService
{
    private const PERFIS_GESTAO_OCORRENCIA = ['super_admin', 'administrador', 'gerente'];

    private const PERMISSOES_RH_OCORRENCIA = [
        'rh_ocorrencias_criar',
        'rh_ocorrencias_editar',
        'rh_ocorrencias_visualizar_todas',
        'rh_ocorrencias_gerenciar',
        'gerenciar_rh',
    ];

    /**
     * Cria nova ocorrência RH com validação e auditoria
     * 
     * @param Employee $employee Colaborador ao qual a ocorrência pertence
     * @param array $dados Dados da ocorrência (tipo, subtipo, titulo, descricao, etc.)
     * @return EmployeeOccurrence
     */
    public function criar(Employee $employee, array $dados): EmployeeOccurrence
    {
        $this->autorizarCriacao($employee);

        // Validação de segurança multi-tenant
        $this->validarTenant($employee, $dados);
        
        // Validação de consistência por tipo
        $this->validarPorTipo($dados);

        DB::beginTransaction();
        try {
            // Cria ocorrência com auditoria automática
            $ocorrencia = EmployeeOccurrence::create([
                'loja_id' => $employee->loja_id,
                'employee_id' => $employee->id,
                'tipo' => $dados['tipo'],
                'subtipo' => $dados['subtipo'] ?? null,
                'titulo' => $dados['titulo'],
                'descricao' => $dados['descricao'] ?? null,
                'data_ocorrencia' => $dados['data_ocorrencia'],
                'data_inicio' => $dados['data_inicio'] ?? null,
                'data_fim' => $dados['data_fim'] ?? null,
                'status' => $dados['status'] ?? EmployeeOccurrence::STATUS_REGISTRADA,
                'referencia_documento' => $dados['referencia_documento'] ?? null,
                'metadados' => $dados['metadados'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Registra na história do colaborador
            $this->registrarNoHistorico($employee, $ocorrencia);

            // Tratamento especial para desligamento
            if ($dados['tipo'] === EmployeeOccurrence::TIPO_DESLIGAMENTO) {
                $this->processarDesligamento($employee, $ocorrencia, $dados);
            }

            DB::commit();
            return $ocorrencia;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Atualiza ocorrência existente com auditoria
     */
    public function atualizar(EmployeeOccurrence $ocorrencia, array $dados): EmployeeOccurrence
    {
        $this->autorizarEdicao($ocorrencia);

        // Validação de segurança: impedir alteração de employee_id, loja_id, created_by
        if (isset($dados['employee_id']) || isset($dados['loja_id']) || isset($dados['created_by'])) {
            throw new \InvalidArgumentException('Não é permitido alterar campos de relacionamento ou criador da ocorrência.');
        }

        DB::beginTransaction();
        try {
            $mudancas = [];
            
            // Detecta campos que foram alterados para auditoria
            foreach (['titulo', 'descricao', 'status', 'metadados'] as $campo) {
                if (isset($dados[$campo]) && $ocorrencia->{$campo} !== $dados[$campo]) {
                    $mudancas[$campo] = [
                        'antes' => $ocorrencia->{$campo},
                        'depois' => $dados[$campo],
                    ];
                }
            }

            // Atualiza ocorrência
            $ocorrencia->update(array_merge($dados, [
                'updated_by' => Auth::id(),
            ]));

            // Registra mudança no histórico se houver alterações
            if (!empty($mudancas)) {
                EmployeeHistory::create([
                    'loja_id' => $ocorrencia->loja_id,
                    'employee_id' => $ocorrencia->employee_id,
                    'tipo_evento' => 'ocorrencia_atualizada',
                    'titulo' => "Ocorrência atualizada: {$ocorrencia->titulo}",
                    'descricao' => json_encode($mudancas, JSON_UNESCAPED_UNICODE),
                    'data_evento' => now(),
                    'criado_por' => Auth::id(),
                ]);
            }

            DB::commit();
            return $ocorrencia;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deleta (soft delete) uma ocorrência
     */
    public function deletar(EmployeeOccurrence $ocorrencia): void
    {
        $this->autorizarExclusao($ocorrencia);

        EmployeeHistory::create([
            'loja_id' => $ocorrencia->loja_id,
            'employee_id' => $ocorrencia->employee_id,
            'tipo_evento' => 'ocorrencia_deletada',
            'titulo' => "Ocorrência removida: {$ocorrencia->titulo}",
            'data_evento' => now(),
            'criado_por' => Auth::id(),
        ]);

        $ocorrencia->delete();
    }

    /**
     * Retorna ocorrências ativas de um colaborador, ordenadas cronologicamente
     */
    public function obterOcorrenciasDoColaborador(Employee $employee): Collection
    {
        $this->autorizarVisualizacao($employee);

        return EmployeeOccurrence::doColaborador($employee)
            ->ordenadoRecente()
            ->get();
    }

    /**
     * Retorna ocorrências de um tipo específico
     */
    public function obterPorTipo(Employee $employee, string $tipo): Collection
    {
        $this->autorizarVisualizacao($employee);

        return EmployeeOccurrence::doColaborador($employee)
            ->where('tipo', $tipo)
            ->ordenadoRecente()
            ->get();
    }

    /**
     * Lista ocorrências paginadas respeitando regras de autorização.
     */
    public function obterPaginado(Employee $employee, ?string $tipo = null, int $porPagina = 15): LengthAwarePaginator
    {
        $this->autorizarVisualizacao($employee);

        $query = EmployeeOccurrence::doColaborador($employee);

        if (!empty($tipo)) {
            $query->where('tipo', $tipo);
        }

        return $query->ordenadoRecente()->paginate($porPagina);
    }

    /**
     * Retorna permissões da tela de ocorrências para a view.
     */
    public function obterPermissoes(Employee $employee): array
    {
        $usuario = $this->usuarioAutenticado();

        return [
            'pode_visualizar' => $this->podeVisualizar($employee, $usuario),
            'pode_criar' => $this->podeGerenciarOcorrencias($employee, $usuario),
            'pode_editar' => $this->podeGerenciarOcorrencias($employee, $usuario),
            'pode_excluir' => $this->podeGerenciarOcorrencias($employee, $usuario),
            'pode_responder' => $this->isFuncionarioDoProprioPerfil($employee, $usuario),
            'is_funcionario' => $this->isFuncionarioDoProprioPerfil($employee, $usuario),
        ];
    }

    /**
     * Valida permissão de criação sem persistir dados.
     */
    public function criarPermissaoApenasValidacao(Employee $employee): void
    {
        $this->autorizarCriacao($employee);
    }

    /**
     * Valida permissão de edição sem alterar dados.
     */
    public function editarPermissaoApenasValidacao(EmployeeOccurrence $ocorrencia): void
    {
        $this->autorizarEdicao($ocorrencia);
    }

    /**
     * Valida dados de entrada contra segurança multi-tenant
     */
    private function validarTenant(Employee $employee, array &$dados): void
    {
        $usuario = $this->usuarioAutenticado();

        if (!$usuario->isSuperAdmin() && (int) $usuario->loja_id !== (int) $employee->loja_id) {
            throw new AuthorizationException('Você não pode registrar ocorrências fora da sua loja.');
        }

        // Força loja_id do employee (não aceita do input)
        $dados['loja_id'] = $employee->loja_id;

        // Valida se tipos são aceitos
        $tiposAceitos = [
            EmployeeOccurrence::TIPO_ADVERTENCIA,
            EmployeeOccurrence::TIPO_SUSPENSAO,
            EmployeeOccurrence::TIPO_FALTA,
            EmployeeOccurrence::TIPO_ATESTADO,
            EmployeeOccurrence::TIPO_DESLIGAMENTO,
        ];

        if (!in_array($dados['tipo'], $tiposAceitos)) {
            throw new \InvalidArgumentException("Tipo de ocorrência '{$dados['tipo']}' não permitido.");
        }
    }

    private function autorizarVisualizacao(Employee $employee): void
    {
        $usuario = $this->usuarioAutenticado();

        if (!$this->podeVisualizar($employee, $usuario)) {
            throw new AuthorizationException('Você não tem permissão para visualizar estas ocorrências.');
        }
    }

    private function autorizarCriacao(Employee $employee): void
    {
        $usuario = $this->usuarioAutenticado();

        if (!$this->podeGerenciarOcorrencias($employee, $usuario)) {
            throw new AuthorizationException('Somente gestor, dono da loja ou usuário com permissão de RH pode registrar ocorrências.');
        }
    }

    private function autorizarEdicao(EmployeeOccurrence $ocorrencia): void
    {
        $usuario = $this->usuarioAutenticado();
        $employee = $ocorrencia->employee;

        if (!$employee || !$this->podeGerenciarOcorrencias($employee, $usuario)) {
            throw new AuthorizationException('Você não tem permissão para editar esta ocorrência.');
        }
    }

    private function autorizarExclusao(EmployeeOccurrence $ocorrencia): void
    {
        $usuario = $this->usuarioAutenticado();
        $employee = $ocorrencia->employee;

        if (!$employee || !$this->podeGerenciarOcorrencias($employee, $usuario)) {
            throw new AuthorizationException('Você não tem permissão para remover esta ocorrência.');
        }
    }

    private function podeVisualizar(Employee $employee, Usuario $usuario): bool
    {
        if (!$this->mesmaLoja($employee, $usuario)) {
            return false;
        }

        if ($this->podeGerenciarOcorrencias($employee, $usuario)) {
            return true;
        }

        return $this->isFuncionarioDoProprioPerfil($employee, $usuario);
    }

    private function podeGerenciarOcorrencias(Employee $employee, Usuario $usuario): bool
    {
        if (!$this->mesmaLoja($employee, $usuario)) {
            return false;
        }

        $perfil = strtolower((string) $usuario->perfil);

        if (in_array($perfil, self::PERFIS_GESTAO_OCORRENCIA, true) || $perfil === 'rh') {
            return true;
        }

        foreach (self::PERMISSOES_RH_OCORRENCIA as $permissao) {
            if ($usuario->temPermissao($permissao)) {
                return true;
            }
        }

        return false;
    }

    private function isFuncionarioDoProprioPerfil(Employee $employee, Usuario $usuario): bool
    {
        if (!$this->mesmaLoja($employee, $usuario)) {
            return false;
        }

        return (int) $employee->user_id === (int) $usuario->id;
    }

    private function mesmaLoja(Employee $employee, Usuario $usuario): bool
    {
        return $usuario->isSuperAdmin() || (int) $employee->loja_id === (int) $usuario->loja_id;
    }

    private function usuarioAutenticado(): Usuario
    {
        $usuario = Auth::user();

        if (!$usuario instanceof Usuario) {
            throw new AuthorizationException('Usuário autenticado inválido para operação de RH.');
        }

        return $usuario;
    }

    /**
     * Valida consistência de dados conforme o tipo de ocorrência
     */
    private function validarPorTipo(array $dados): void
    {
        $tipo = $dados['tipo'];

        // Advertência: deve ter subtipo
        if ($tipo === EmployeeOccurrence::TIPO_ADVERTENCIA) {
            if (empty($dados['subtipo'])) {
                throw new \InvalidArgumentException('Advertência deve informar subtipo (verbal/escrita).');
            }
            if (!in_array($dados['subtipo'], [
                EmployeeOccurrence::SUBTIPO_ADVERTENCIA_VERBAL,
                EmployeeOccurrence::SUBTIPO_ADVERTENCIA_ESCRITA,
            ])) {
                throw new \InvalidArgumentException('Subtipo de advertência inválido.');
            }
        }

        // Suspensão: deve ter datas início/fim
        if ($tipo === EmployeeOccurrence::TIPO_SUSPENSAO) {
            if (empty($dados['data_inicio']) || empty($dados['data_fim'])) {
                throw new \InvalidArgumentException('Suspensão deve informar período (data_inicio e data_fim).');
            }
            if ($dados['data_fim'] <= $dados['data_inicio']) {
                throw new \InvalidArgumentException('Data final da suspensão deve ser após data inicial.');
            }
        }

        // Falta: pode ter subtipo
        if ($tipo === EmployeeOccurrence::TIPO_FALTA) {
            if (!empty($dados['subtipo']) && !in_array($dados['subtipo'], [
                EmployeeOccurrence::SUBTIPO_FALTA_INJUSTIFICADA,
                EmployeeOccurrence::SUBTIPO_FALTA_JUSTIFICADA,
            ])) {
                throw new \InvalidArgumentException('Subtipo de falta inválido.');
            }
        }

        // Atestado: pode ter data fim
        if ($tipo === EmployeeOccurrence::TIPO_ATESTADO) {
            // Pode ter metadados com arquivo_anexado, dias_afastamento, etc.
        }

        // Desligamento: deve ter subtipo
        if ($tipo === EmployeeOccurrence::TIPO_DESLIGAMENTO) {
            if (empty($dados['subtipo'])) {
                throw new \InvalidArgumentException('Desligamento deve informar subtipo.');
            }
            if (!in_array($dados['subtipo'], [
                EmployeeOccurrence::SUBTIPO_DESLIGAMENTO_PEDIDO,
                EmployeeOccurrence::SUBTIPO_DESLIGAMENTO_SEM_JUSTA_CAUSA,
                EmployeeOccurrence::SUBTIPO_DESLIGAMENTO_JUSTA_CAUSA,
                EmployeeOccurrence::SUBTIPO_DESLIGAMENTO_TERMINO_CONTRATO,
            ])) {
                throw new \InvalidArgumentException('Subtipo de desligamento inválido.');
            }
        }
    }

    /**
     * Registra ocorrência no histórico do colaborador
     */
    private function registrarNoHistorico(Employee $employee, EmployeeOccurrence $ocorrencia): void
    {
        EmployeeHistory::create([
            'loja_id' => $employee->loja_id,
            'employee_id' => $employee->id,
            'tipo_evento' => 'ocorrencia_rh',
            'titulo' => "Nova ocorrência RH: {$ocorrencia->getTipoLabel()}",
            'descricao' => $ocorrencia->titulo . ($ocorrencia->descricao ? "\n\n{$ocorrencia->descricao}" : ''),
            'data_evento' => $ocorrencia->data_ocorrencia,
            'criado_por' => Auth::id(),
        ]);
    }

    /**
     * Processa impactos funcionais do desligamento
     * Atualiza status do colaborador e opcionalmente revoga acesso sistêmico
     */
    private function processarDesligamento(Employee $employee, EmployeeOccurrence $ocorrencia, array $dados): void
    {
        // Atualiza status funcional do colaborador
        $employee->update([
            'status_funcional' => 'desligado',
            'data_desligamento' => $ocorrencia->data_ocorrencia,
        ]);

        // Governança de Acesso: revoga acesso ao sistema se solicitado
        if (!empty($dados['revogar_acesso']) && $employee->user_id) {
            $this->revogarAcesso($employee, $ocorrencia);
        }
    }

    /**
     * Revoga acesso sistêmico de um colaborador desligado
     * Desativa usuário preservando histórico completo
     */
    private function revogarAcesso(Employee $employee, EmployeeOccurrence $ocorrencia): void
    {
        $usuario = $employee->usuario;
        
        if (!$usuario) {
            return;
        }

        // Desativa usuário (não deleta, preserva auditoria)
        $usuario->update([
            'ativo' => false,
        ]);

        // Registra ação no histórico
        EmployeeHistory::create([
            'loja_id' => $employee->loja_id,
            'employee_id' => $employee->id,
            'tipo_evento' => 'acesso_revogado',
            'titulo' => 'Acesso ao sistema revogado',
            'descricao' => "Acesso revogado em decorrência de desligamento (ocorrência #{$ocorrencia->id}, subtipo: {$ocorrencia->getSubtipoLabel()})",
            'data_evento' => now(),
            'criado_por' => Auth::id(),
        ]);
    }
}
