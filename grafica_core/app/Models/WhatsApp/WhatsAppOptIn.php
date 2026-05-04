<?php

declare(strict_types=1);

namespace App\Models\WhatsApp;

use App\Models\Cliente;
use App\Models\Loja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppOptIn extends Model
{
    protected $table = 'whatsapp_optins';

    public const STATUS_OPTED_IN  = 'opted_in';
    public const STATUS_OPTED_OUT = 'opted_out';

    protected $fillable = [
        'loja_id',
        'cliente_id',
        'phone',
        'status',
        'source',
        'ip_address',
        'user_agent',
        'opted_in_at',
        'opted_out_at',
    ];

    protected $casts = [
        'opted_in_at'  => 'datetime',
        'opted_out_at' => 'datetime',
    ];

    public function hasOptedIn(): bool
    {
        return $this->status === self::STATUS_OPTED_IN;
    }

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function scopeOptedIn($query)
    {
        return $query->where('status', self::STATUS_OPTED_IN);
    }

    public function scopeForPhone($query, string $phone)
    {
        return $query->where('phone', $phone);
    }
}
