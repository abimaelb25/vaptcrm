<?php

namespace App\Http\Controllers\Admin\Support;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
*/

use App\Http\Controllers\Controller;
use App\Models\HelpContent;

class HelpCenterController extends Controller
{
    public function index()
    {
        $destaques = HelpContent::publicados()->where('destaque', true)->orderBy('ordem')->get();
        $videos = HelpContent::publicados()->where('destaque', false)->orderBy('ordem')->get();
        
        return view('painel.support.help-center.index', compact('destaques', 'videos'));
    }

    public function show(HelpContent $helpContent)
    {
        if (!$helpContent->publicado) {
            abort(404);
        }
        
        $sugeridos = HelpContent::publicados()
            ->where('id', '!=', $helpContent->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('painel.support.help-center.show', compact('helpContent', 'sugeridos'));
    }
}
