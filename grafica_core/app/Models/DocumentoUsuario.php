<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoUsuario extends Model
{
    use \App\Traits\HasTenancy;

    protected $table = 'documentos_usuarios';

    protected $fillable = [
        'loja_id',
        'usuario_id',
        'tipo_documento',
        'caminho_arquivo',
        'nome_original',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function nomeLegivelApresentacao(): string
    {
        return match ($this->tipo_documento) {
            'rg' => 'RG',
            'cpf' => 'CPF',
            'certidao_nascimento' => 'Certidão de Nascimento',
            'certidao_casamento' => 'Certidão de Casamento',
            default => 'Outro Documento',
        };
    }
}
