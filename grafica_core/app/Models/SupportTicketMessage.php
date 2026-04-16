<?php

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
*/

use App\Traits\HasTenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketMessage extends Model
{
    use HasTenancy;

    protected $fillable = [
        'ticket_id',
        'loja_id',
        'autor_tipo',
        'autor_user_id',
        'autor_master_id',
        'mensagem',
        'anexo_path',
        'interno',
    ];

    protected function casts(): array
    {
        return [
            'interno' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function autorUser(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'autor_user_id');
    }

    public function autorMaster(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'autor_master_id');
    }

    /**
     * Retorna o nome do autor da mensagem, dependendo se foi da loja ou do painel master.
     */
    public function getNomeAutorAttribute(): string
    {
        if ($this->autor_tipo === 'cliente') {
            return $this->autorUser->nome ?? 'Usuário Removido';
        }
        
        if ($this->autor_tipo === 'suporte') {
            return 'Suporte VaptCRM'; // Ou $this->autorMaster->nome para expor o nome do atendente
        }

        return 'Sistema / Interno';
    }
}
