<?php

declare(strict_types=1);

namespace App\Services;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-06 01:10 -03:00
*/

use App\Models\Auditoria;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Registra uma ação no Audit Log.
     *
     * @param string $modulo
     * @param string $acao (ex: 'criacao', 'atualizacao', 'inativacao', 'exclusao')
     * @param int|null $registroId
     * @param array|null $valoresAntigos
     * @param array|null $valoresNovos
     * @return Auditoria
     */
    public function log(
        string $modulo,
        string $acao,
        ?int $registroId = null,
        ?array $valoresAntigos = null,
        ?array $valoresNovos = null
    ): Auditoria {
        $request = request();
        $usuarioId = auth()->id();

        return Auditoria::create([
            'usuario_id' => $usuarioId,
            'modulo' => $modulo,
            'acao' => $acao,
            'registro_id' => $registroId,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'valores_antigos' => $valoresAntigos,
            'valores_novos' => $valoresNovos,
        ]);
    }
}
