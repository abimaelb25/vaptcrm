<?php

declare(strict_types=1);

namespace App\Services\RH;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 11:10
| Descrição: Service para gerenciar Anexos de Ocorrências RH
*/

use App\Models\Employee;
use App\Models\EmployeeOccurrence;
use App\Models\EmployeeOccurrenceAttachment;
use App\Services\Core\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeOccurrenceAttachmentService
{
    // Tipos de arquivo aceitos (MIME types)
    const MIME_TYPES_ACEITOS = [
        'application/pdf',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    // Tamanho máximo: 10MB
    const TAMANHO_MAXIMO_BYTES = 10 * 1024 * 1024;

    public function __construct(
        protected MediaService $mediaService,
    ) {}

    /**
     * Faz upload de anexo para uma ocorrência
     * 
     * @param EmployeeOccurrence $ocorrencia Ocorrência ao qual o anexo pertence
     * @param UploadedFile $arquivo Arquivo para upload
     * @param string $titulo Título/identificação do anexo
     * @param string|null $tipoComprovacao Tipo de comprovação (atestado_medico, etc)
     * @param string|null $descricao Descrição adicional
     * @return EmployeeOccurrenceAttachment
     */
    public function upload(
        EmployeeOccurrence $ocorrencia,
        UploadedFile $arquivo,
        string $titulo,
        ?string $tipoComprovacao = null,
        ?string $descricao = null
    ): EmployeeOccurrenceAttachment {
        
        // Validações
        $this->validar($arquivo);

        // Salva arquivo no storage
        $caminhoArquivo = $this->salvarArquivo($ocorrencia, $arquivo);

        // Cria registro no banco
        $anexo = EmployeeOccurrenceAttachment::create([
            'loja_id' => $ocorrencia->loja_id,
            'employee_occurrence_id' => $ocorrencia->id,
            'titulo' => $titulo,
            'arquivo_path' => $caminhoArquivo,
            'mime_type' => $arquivo->getClientMimeType(),
            'tamanho_bytes' => $arquivo->getSize(),
            'tipo_comprovacao' => $tipoComprovacao,
            'descricao' => $descricao,
            'uploaded_by' => Auth::id(),
        ]);

        // Registra delta de storage
        $this->registrarStorageDelta($ocorrencia->loja_id, $arquivo->getSize(), +1);

        return $anexo;
    }

    /**
     * Deleta um anexo
     */
    public function deletar(EmployeeOccurrenceAttachment $anexo): void
    {
        // Remove arquivo do storage
        if (Storage::disk('public')->exists($anexo->arquivo_path)) {
            Storage::disk('public')->delete($anexo->arquivo_path);
        }

        // Registra delta negativo
        $this->registrarStorageDelta($anexo->loja_id, $anexo->tamanho_bytes, -1);

        // Soft delete no banco
        $anexo->delete();
    }

    /**
     * Retorna anexos de uma ocorrência
     */
    public function obterAnexosDaOcorrencia(EmployeeOccurrence $ocorrencia)
    {
        return EmployeeOccurrenceAttachment::daOcorrencia($ocorrencia)
            ->ativos()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Verifica se ocorrência tem anexos
     */
    public function temAnexos(EmployeeOccurrence $ocorrencia): bool
    {
        return EmployeeOccurrenceAttachment::daOcorrencia($ocorrencia)
            ->ativos()
            ->exists();
    }

    /**
     * Valida arquivo (tipo, tamanho)
     */
    private function validar(UploadedFile $arquivo): void
    {
        // Validar MIME type
        $mimeType = $arquivo->getClientMimeType();
        if (!in_array($mimeType, self::MIME_TYPES_ACEITOS)) {
            throw new \InvalidArgumentException(
                "Tipo de arquivo não permitido. Aceitos: PDF, PNG, JPG, DOCX. Recebido: {$mimeType}"
            );
        }

        // Validar tamanho
        $tamanho = $arquivo->getSize();
        if ($tamanho > self::TAMANHO_MAXIMO_BYTES) {
            $maxMB = self::TAMANHO_MAXIMO_BYTES / (1024 * 1024);
            throw new \InvalidArgumentException(
                "Arquivo muito grande. Máximo: {$maxMB}MB. Recebido: " . ($tamanho / (1024 * 1024)) . "MB"
            );
        }
    }

    /**
     * Salva arquivo no storage (sem crop, preserva originalidade)
     */
    private function salvarArquivo(EmployeeOccurrence $ocorrencia, UploadedFile $arquivo): string
    {
        $caminho = "ocorrencias/{$ocorrencia->loja_id}/{$ocorrencia->id}";
        
        // Gera nome único
        $extensao = $arquivo->getClientOriginalExtension();
        $nomeUnico = Str::random(12) . '.' . strtolower($extensao);

        // Cria diretório se não existir
        Storage::disk('public')->makeDirectory($caminho);

        // Armazena arquivo
        $caminhoRelativo = $caminho . '/' . $nomeUnico;
        $arquivo->storeAs($caminho, $nomeUnico, 'public');

        return $caminhoRelativo;
    }

    /**
     * Registra delta de storage na loja (integração com MediaService pattern)
     */
    private function registrarStorageDelta(int $lojaId, int $bytes, int $direction): void
    {
        // Implementação simples similar ao MediaService
        // Em produção, integraria com PlanService como MediaService faz
        // Por enquanto, apenas registra para auditoria
        // TODO: integrar com PlanService.recordUsage() quando necessário limitações rígidas
    }
}
