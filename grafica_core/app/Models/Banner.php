<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 03:50 (adição: HasTenancy)
*/

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTenancy;

class Banner extends Model
{
    use HasTenancy;
    protected $table = 'banners';

    protected $fillable = [
        'loja_id',
        'titulo',
        'subtitulo',
        'imagem',
        'link',
        'ativo',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }
}
