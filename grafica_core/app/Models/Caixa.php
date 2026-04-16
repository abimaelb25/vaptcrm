<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 03:50 (adição: HasTenancy)
*/

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTenancy;

class Caixa extends Model
{
    use HasFactory, SoftDeletes, HasTenancy;

    protected $table = 'caixas';

    protected $fillable = [
        'loja_id',
        'usuario_id',
        'data_abertura',
        'data_fechamento',
        'valor_inicial',
        'valor_vendas',
        'valor_fechamento',
        'diferenca',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_abertura'    => 'datetime',
        'data_fechamento'  => 'datetime',
        'valor_inicial'    => 'decimal:2',
        'valor_vendas'     => 'decimal:2',
        'valor_fechamento' => 'decimal:2',
        'diferenca'        => 'decimal:2',
    ];

    /* 
    | RELACIONAMENTOS 
    */

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(MovimentacaoFinanceira::class, 'caixa_id');
    }

    /* 
    | SCOPES 
    */

    public function scopeAberto($query)
    {
        return $query->where('status', 'aberto');
    }

    /**
     * Busca o caixa aberto de um usuário específico.
     */
    public static function getAberto(int $usuarioId): ?self
    {
        return self::where('usuario_id', $usuarioId)
            ->where('status', 'aberto')
            ->first();
    }
}
