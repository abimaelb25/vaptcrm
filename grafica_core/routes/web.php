<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-16
| Descrição: Carregamento modular de rotas Web para preservar coesão por contexto.
*/

require __DIR__ . '/web/public.php';
require __DIR__ . '/web/auth.php';
require __DIR__ . '/web/compat.php';
require __DIR__ . '/web/admin.php';
require __DIR__ . '/web/superadmin.php';
