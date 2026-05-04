<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 10:05
| Descrição: Model para ocorrências RH estruturadas (advertências, suspensões, faltas, etc)
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class EmployeeOccurrence extends Model
{
    use SoftDeletes, HasTenancy;

    protected $table = 'employee_occurrences';

    protected $fillable = [
        'loja_id',
        'employee_id',
        'tipo',
        'subtipo',
        'titulo',
        'descricao',
        'data_ocorrencia',
        'data_inicio',
        'data_fim',
        'status',
        'referencia_documento',
        'metadados',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data_ocorrencia' => 'date',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'metadados' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ========== CONSTANTES (Tipos e Subtipos) ==========
    
    const TIPO_ADVERTENCIA = 'advertencia';
    const TIPO_SUSPENSAO = 'suspensao';
    const TIPO_FALTA = 'falta';
    const TIPO_ATESTADO = 'atestado';
    const TIPO_DESLIGAMENTO = 'desligamento';

    const SUBTIPO_ADVERTENCIA_VERBAL = 'verbal';
    const SUBTIPO_ADVERTENCIA_ESCRITA = 'escrita';
    
    const SUBTIPO_FALTA_INJUSTIFICADA = 'injustificada';
    const SUBTIPO_FALTA_JUSTIFICADA = 'justificada';
    
    const SUBTIPO_DESLIGAMENTO_PEDIDO = 'pedido_demissao';
    const SUBTIPO_DESLIGAMENTO_SEM_JUSTA_CAUSA = 'sem_justa_causa';
    const SUBTIPO_DESLIGAMENTO_JUSTA_CAUSA = 'justa_causa';
    const SUBTIPO_DESLIGAMENTO_TERMINO_CONTRATO = 'termino_contrato';

    const STATUS_REGISTRADA = 'registrada';
    const STATUS_EM_ANALISE = 'em_analise';
    const STATUS_RESOLVIDA = 'resolvida';
    const STATUS_CONTESTADA = 'contestada';
    const STATUS_ARQUIVADA = 'arquivada';

    // ========== RELACIONAMENTOS ==========

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'created_by');
    }

    public function atualizador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'updated_by');
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(EmployeeOccurrenceAttachment::class, 'employee_occurrence_id');
    }

    // ========== MÉTODOS AUXILIARES ==========

    /**
     * Retorna rótulo legível do tipo de ocorrência (PT-BR)
     */
    public function getTipoLabel(): string
    {
        return match ($this->tipo) {
            self::TIPO_ADVERTENCIA => 'Advertência',
            self::TIPO_SUSPENSAO => 'Suspensão',
            self::TIPO_FALTA => 'Falta / Ausência',
            self::TIPO_ATESTADO => 'Atestado',
            self::TIPO_DESLIGAMENTO => 'Desligamento',
            default => $this->tipo,
        };
    }

    /**
     * Retorna rótulo legível do subtipo de ocorrência (PT-BR)
     */
    public function getSubtipoLabel(): string
    {
        return match ($this->subtipo) {
            self::SUBTIPO_ADVERTENCIA_VERBAL => 'Verbal',
            self::SUBTIPO_ADVERTENCIA_ESCRITA => 'Escrita',
            self::SUBTIPO_FALTA_INJUSTIFICADA => 'Injustificada',
            self::SUBTIPO_FALTA_JUSTIFICADA => 'Justificada',
            self::SUBTIPO_DESLIGAMENTO_PEDIDO => 'Pedido de Demissão',
            self::SUBTIPO_DESLIGAMENTO_SEM_JUSTA_CAUSA => 'Sem Justa Causa',
            self::SUBTIPO_DESLIGAMENTO_JUSTA_CAUSA => 'Justa Causa',
            self::SUBTIPO_DESLIGAMENTO_TERMINO_CONTRATO => 'Término de Contrato',
            default => $this->subtipo ?? '-',
        };
    }

    /**
     * Retorna rótulo legível do status (PT-BR)
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_REGISTRADA => 'Registrada',
            self::STATUS_EM_ANALISE => 'Em Análise',
            self::STATUS_RESOLVIDA => 'Resolvida',
            self::STATUS_CONTESTADA => 'Contestada',
            self::STATUS_ARQUIVADA => 'Arquivada',
            default => $this->status,
        };
    }

    /**
     * Retorna cor do status para UI (Tailwind)
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_REGISTRADA => 'slate',
            self::STATUS_EM_ANALISE => 'amber',
            self::STATUS_RESOLVIDA => 'emerald',
            self::STATUS_CONTESTADA => 'red',
            self::STATUS_ARQUIVADA => 'gray',
            default => 'slate',
        };
    }

    /**
     * Calcula duração em dias (para suspensão, afastamento)
     */
    public function getDuracao(): ?int
    {
        if ($this->data_inicio && $this->data_fim) {
            return $this->data_fim->diffInDays($this->data_inicio);
        }
        return null;
    }

    /**
     * Verifica se é ocorrência ativa (não resolvida/arquivada)
     */
    public function isAtiva(): bool
    {
        return !in_array($this->status, [self::STATUS_RESOLVIDA, self::STATUS_ARQUIVADA]);
    }

    /**
     * Scope: filtra ocorrências de um colaborador específico no tenant correto
     */
    public function scopeDoColaborador($query, Employee $employee)
    {
        return $query->where('loja_id', $employee->loja_id)
                     ->where('employee_id', $employee->id);
    }

    /**
     * Scope: ordena cronologicamente (mais recente primeiro)
     */
    public function scopeOrdenadoRecente($query)
    {
        return $query->orderBy('data_ocorrencia', 'desc')
                     ->orderBy('created_at', 'desc');
    }
}
