<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitacaoAtualizacao extends Model
{
    use \App\Traits\HasTenancy;

    protected $table = 'solicitacao_atualizacoes';

    protected $fillable = [
        'loja_id',
        'usuario_id',
        'dados_antigos',
        'dados_novos',
        'status',
        'revisado_por',
        'motivo_rejeicao',
    ];

    protected $casts = [
        'dados_antigos' => 'array',
        'dados_novos' => 'array',
    ];

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'revisado_por');
    }
}
