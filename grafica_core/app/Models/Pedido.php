<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-05 00:16 -03:00
*/

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Authorable, \App\Traits\HasTenancy;

    protected $table = 'pedidos';

    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_AGUARDANDO = 'aguardando_aprovacao';
    public const STATUS_APROVADO = 'aprovado';
    public const STATUS_EM_PRODUCAO = 'em_producao';
    public const STATUS_PRONTO = 'pronto';
    public const STATUS_EM_TRANSPORTE = 'em_transporte';
    public const STATUS_ENTREGUE = 'entregue';
    public const STATUS_CANCELADO = 'cancelado';
    public const STATUS_AGUARDANDO_PAGAMENTO = 'aguardando_pagamento';
    
    public const ORIGEM_PDV = 'pdv';

    protected $fillable = [
        'loja_id',
        'numero',
        'numero_sequencial',
        'codigo_pedido',
        'numero_acompanhamento',
        'origem',
        'tipo_atendimento',
        'cliente_id',
        'status',
        'subtotal',
        'total',
        'valor_recebido',
        'troco',
        'tipo_total',
        'tipo_entrega',
        'valor_frete',
        'taxas_adicionais',
        'desconto',
        'forma_pagamento',
        'gateway_pagamento',
        'prazo_entrega',
        'responsavel_id',
        'atendente_id',
        'cupom_id',
        'valor_desconto_cupom',
        'observacoes',
        'observacoes_internas',
        'observacoes_cliente',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'subtotal'             => 'decimal:2',
        'total'                => 'decimal:2',
        'valor_frete'          => 'decimal:2',
        'taxas_adicionais'     => 'decimal:2',
        'desconto'             => 'decimal:2',
        'valor_desconto_cupom' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemPedido::class, 'pedido_id');
    }

    public function historico(): HasMany
    {
        return $this->hasMany(HistoricoPedido::class, 'pedido_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class, 'pedido_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsavel_id');
    }

    public function atendente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'atendente_id');
    }

    public function cupom(): BelongsTo
    {
        return $this->belongsTo(Cupom::class, 'cupom_id');
    }

    /**
     * Verifica se o pedido está totalmente pago.
     * Considera a soma dos pagamentos com status 'pago' em relação ao total do pedido.
     */
    public function estaPago(): bool
    {
        $totalPago = $this->pagamentos()
            ->where('status', 'pago')
            ->sum('valor');

        return (float) $totalPago >= (float) $this->total;
    }

    /**
     * Retorna o valor total já pago no pedido.
     */
    public function valorPago(): float
    {
        return (float) $this->pagamentos()
            ->where('status', 'pago')
            ->sum('valor');
    }

    /**
     * Retorna o valor pendente de pagamento.
     */
    public function valorPendente(): float
    {
        return max(0, (float) $this->total - $this->valorPago());
    }

    /**
     * Retorna o próximo número sequencial para uma loja.
     * ATENÇÃO: Este método deve ser chamado dentro de uma transação com lock na loja.
     */
    public static function proximoSequencial(int $lojaId): int
    {
        return (int) static::where('loja_id', $lojaId)->max('numero_sequencial') + 1;
    }

    /**
     * Gera o próximo número sequencial de forma segura contra concorrência.
     *
     * ESTRATÉGIA: Lock pessimista na LINHA DA LOJA (não nos pedidos).
     * Isso serializa a geração de sequenciais por loja, evitando race conditions.
     *
     * IMPORTANTE: Este método DEVE ser chamado dentro de uma DB::transaction().
     * O lock na loja garante que apenas uma transação por vez pode gerar sequencial.
     *
     * @param int $lojaId ID da loja
     * @return int Próximo número sequencial
     * @throws \RuntimeException Se a loja não for encontrada
     */
    public static function gerarSequencialSeguro(int $lojaId): int
    {
        // Lock exclusivo na linha da loja (funciona corretamente em InnoDB)
        // Isso bloqueia outras transações que tentarem o mesmo lock até esta transacao finalizar
        $loja = Loja::where('id', $lojaId)->lockForUpdate()->first();

        if (!$loja) {
            throw new \RuntimeException("Loja ID {$lojaId} não encontrada para geração de sequencial.");
        }

        // Agora é seguro ler o MAX porque temos lock exclusivo na loja
        return (int) static::where('loja_id', $lojaId)->max('numero_sequencial') + 1;
    }

    /**
     * Gera o código do pedido no formato: LOJA-AA-XXXXX
     */
    public static function gerarCodigoPedido(string $codigoLoja, int $sequencial, ?\DateTimeInterface $data = null): string
    {
        $ano = ($data ?? now())->format('y');
        return sprintf('%s-%s-%05d', $codigoLoja, $ano, $sequencial);
    }

    /**
     * Retorna o número de exibição preferencial.
     * PDV: usa numero_sequencial. Cliente: usa codigo_pedido.
     */
    public function getNumeroExibicaoAttribute(): string
    {
        return $this->codigo_pedido ?? $this->numero ?? (string) $this->id;
    }

    /**
     * Retorna apenas o número sequencial formatado (para PDV).
     */
    public function getNumeroBalcaoAttribute(): string
    {
        return $this->numero_sequencial ? '#' . $this->numero_sequencial : '#' . $this->id;
    }
}
