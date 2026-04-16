<?php

namespace App\Http\Controllers\SuperAdmin\Support;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
*/

use App\Http\Controllers\Controller;
use App\Models\HelpContent;
use Illuminate\Http\Request;

class HelpContentController extends Controller
{
    public function index()
    {
        $contents = HelpContent::orderBy('ordem')->get();
        return view('super-admin.support.help-contents.index', compact('contents'));
    }

    public function create()
    {
        return view('super-admin.support.help-contents.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'youtube_url' => 'required|url',
            'thumbnail' => 'nullable|url',
            'ordem' => 'integer',
            'destaque' => 'boolean',
            'publicado' => 'boolean',
        ]);

        HelpContent::create($data);
        return redirect()->route('superadmin.support.central-de-ajuda.index')->with('success', 'Vídeo de ajuda cadastrado com sucesso.');
    }

    public function edit(HelpContent $helpContent)
    {
        return view('super-admin.support.help-contents.form', compact('helpContent'));
    }

    public function update(Request $request, HelpContent $helpContent)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'youtube_url' => 'required|url',
            'thumbnail' => 'nullable|url',
            'ordem' => 'integer',
            'destaque' => 'boolean',
            'publicado' => 'boolean',
        ]);

        $helpContent->update($data);
        return redirect()->route('superadmin.support.central-de-ajuda.index')->with('success', 'Vídeo atualizado com sucesso.');
    }

    public function destroy(HelpContent $helpContent)
    {
        $helpContent->delete();
        return redirect()->route('superadmin.support.central-de-ajuda.index')->with('success', 'Vídeo removido com sucesso.');
    }
}
