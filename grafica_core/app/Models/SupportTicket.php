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
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasTenancy;

    protected $fillable = [
        'loja_id',
        'user_id',
        'numero_ticket',
        'assunto',
        'categoria_id',
        'prioridade',
        'status',
        'responsavel_master_id',
        'ultimo_evento_em',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_evento_em' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'categoria_id');
    }

    public function responsavelMaster(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsavel_master_id');
    }

    public function mensagens(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    /**
     * Scopes para status.
     */
    public function scopeAbertos($query)
    {
        return $query->whereIn('status', ['aberto', 'aguardando_suporte', 'aguardando_cliente']);
    }

    /**
     * Define o número único do ticket na criação.
     */
    protected static function booted()
    {
        static::creating(function ($ticket) {
            if (empty($ticket->numero_ticket)) {
                $ticket->numero_ticket = 'TK-' . strtoupper(uniqid());
            }
        });
    }
}
