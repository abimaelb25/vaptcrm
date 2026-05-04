<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-17 00:25
*/

use App\Http\Controllers\Controller;
use App\Models\PaginaLegal;
use App\Services\Domain\LegalPageRenderService;
use Illuminate\Contracts\View\View;

class LegalPageController extends Controller
{
    public function __construct(
        private readonly LegalPageRenderService $renderService,
    ) {}

    public function show(string $slug): View
    {
        $pagina = PaginaLegal::query()
            ->where('slug', $slug)
            ->where('ativa', true)
            ->firstOrFail();

        $pagina->conteudo = $this->renderService->render($pagina);

        return view('publico.pagina-legal', compact('pagina'));
    }
}
