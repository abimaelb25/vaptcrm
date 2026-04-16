<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-06 01:40 -03:00
*/

use Illuminate\Database\Eloquent\Model;

class PaginaLegal extends Model
{
    use \App\Traits\HasTenancy;

    protected $table = 'paginas_legais';

    protected $fillable = [
        'titulo',
        'slug',
        'tipo',
        'conteudo',
        'resumo',
        'ativa',
        'exibir_no_rodape',
        'ordem_exibicao',
        'pagina_sistema'
    ];

    protected function casts(): array
    {
        return [
            'ativa' => 'boolean',
            'exibir_no_rodape' => 'boolean',
            'pagina_sistema' => 'boolean',
            'ordem_exibicao' => 'integer',
        ];
    }
}
