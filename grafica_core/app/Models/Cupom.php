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
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTenancy;

class Cupom extends Model
{
    use HasFactory, SoftDeletes, HasTenancy;

    protected $table = 'cupons';

    const TIPO_PERCENTUAL = 'percentual';
    const TIPO_FIXO = 'fixo';

    protected $fillable = [
        'loja_id',
        'user_id',
        'codigo',
        'tipo',
        'valor',
        'valor_minimo_pedido',
        'data_inicio',
        'data_fim',
        'quantidade_utilizada',
        'validade_inicio',
        'validade_fim',
        'limite_uso',
        'usos_atuais',
        'ativo',
    ];

    protected $casts = [
        'valor'           => 'decimal:2',
        'valor_minimo_pedido' => 'decimal:2',
        'data_inicio'     => 'datetime',
        'data_fim'        => 'datetime',
        'validade_inicio' => 'datetime',
        'validade_fim'    => 'datetime',
        'ativo'           => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    /**
     * Verifica se o cupom pode ser utilizado agora.
     */
    public function isValid(): bool
    {
        if (!$this->ativo) {
            return false;
        }

        $agora = now();
        $inicio = $this->data_inicio ?? $this->validade_inicio;
        $fim = $this->data_fim ?? $this->validade_fim;
        $usados = (int) ($this->quantidade_utilizada ?? $this->usos_atuais ?? 0);

        // Verifica validade temporal
        if ($inicio && $agora->lt($inicio)) {
            return false;
        }

        if ($fim && $agora->gt($fim)) {
            return false;
        }

        // Verifica limite de uso
        if ($this->limite_uso !== null && $usados >= $this->limite_uso) {
            return false;
        }

        return true;
    }

    /**
     * Calcula o valor do desconto com base em um subtotal.
     */
    public function calcularDesconto(float $subtotal): float
    {
        if (($this->valor_minimo_pedido ?? 0) > 0 && $subtotal < (float) $this->valor_minimo_pedido) {
            return 0;
        }

        if ($this->tipo === self::TIPO_PERCENTUAL) {
            return round($subtotal * ($this->valor / 100), 2);
        }

        // Para valor fixo, o desconto não pode ser maior que o subtotal
        return min($subtotal, (float) $this->valor);
    }
}
