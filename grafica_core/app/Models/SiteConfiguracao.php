<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-06 01:40 -03:00
*/

use Illuminate\Database\Eloquent\Model;

class SiteConfiguracao extends Model
{
    use \App\Traits\HasTenancy;

    protected $table = 'site_configuracoes';

    protected $fillable = [
        'loja_id',
        'chave',
        'valor',
        'tipo',
    ];
}
