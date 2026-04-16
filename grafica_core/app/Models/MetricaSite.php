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
use Illuminate\Support\Str;
use App\Traits\HasTenancy;

class MetricaSite extends Model
{
    use HasFactory, HasTenancy;

    protected $table = 'metricas_site';

    protected $fillable = [
        'loja_id',
        'uuid',
        'tipo',
        'entidade_tipo',
        'entidade_id',
        'origem',
        'dispositivo',
        'navegador',
        'ip',
    ];

    protected static function booted(): void
    {
        static::creating(function (MetricaSite $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /* 
    | SCOPES 
    */

    public function scopeViews($query)
    {
        return $query->where('tipo', 'view');
    }

    public function scopeClicks($query)
    {
        return $query->where('tipo', 'click');
    }

    public function scopeNoPeriodo($query, $inicio, $fim)
    {
        return $query->whereBetween('created_at', [$inicio, $fim]);
    }
}
