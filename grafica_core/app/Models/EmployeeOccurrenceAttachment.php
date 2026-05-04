<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 11:05
| Descrição: Model para Anexos de Ocorrências RH
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenancy;

class EmployeeOccurrenceAttachment extends Model
{
    use SoftDeletes, HasTenancy;

    protected $table = 'employee_occurrence_attachments';

    protected $fillable = [
        'loja_id',
        'employee_occurrence_id',
        'titulo',
        'arquivo_path',
        'mime_type',
        'tamanho_bytes',
        'tipo_comprovacao',
        'descricao',
        'uploaded_by',
    ];

    protected $casts = [
        'tamanho_bytes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ========== RELACIONAMENTOS ==========

    public function ocorrencia(): BelongsTo
    {
        return $this->belongsTo(EmployeeOccurrence::class, 'employee_occurrence_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'uploaded_by');
    }

    // ========== MÉTODOS AUXILIARES ==========

    /**
     * Retorna tamanho formatado (KB, MB)
     */
    public function getTamanhoFormatado(): string
    {
        if ($this->tamanho_bytes < 1024 * 1024) {
            return round($this->tamanho_bytes / 1024, 2) . ' KB';
        }
        return round($this->tamanho_bytes / (1024 * 1024), 2) . ' MB';
    }

    /**
     * Retorna ícone por MIME type
     */
    public function getIcone(): string
    {
        return match ($this->mime_type) {
            'application/pdf' => 'fa-file-pdf',
            'image/jpeg', 'image/jpg' => 'fa-file-image',
            'image/png' => 'fa-file-image',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
            default => 'fa-file',
        };
    }

    /**
     * Retorna cor por MIME type (Tailwind)
     */
    public function getCor(): string
    {
        return match ($this->mime_type) {
            'application/pdf' => 'red',
            'image/jpeg', 'image/jpg', 'image/png' => 'blue',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'blue',
            default => 'slate',
        };
    }

    /**
     * Scope: apenas anexos ativo (não deletados)
     */
    public function scopeAtivos($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope: filtra por ocorrência
     */
    public function scopeDaOcorrencia($query, EmployeeOccurrence $ocorrencia)
    {
        return $query->where('employee_occurrence_id', $ocorrencia->id)
                     ->where('loja_id', $ocorrencia->loja_id);
    }
}
