<?php

declare(strict_types=1);

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('ordem')->get();
        return view('painel.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('painel.banners.form', ['banner' => new Banner()]);
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'titulo' => 'required|string',
            'subtitulo' => 'nullable|string',
            'link' => 'nullable|url',
            'imagem' => 'required|image',
            'ordem' => 'nullable|integer',
            'ativo' => 'nullable|boolean'
        ]);

        $dados['imagem'] = $request->file('imagem')->store('banners', 'public');
        $dados['ativo'] = $request->has('ativo');
        $dados['ordem'] = $dados['ordem'] ?? 0;

        Banner::create($dados);

        return redirect()->route('admin.system.banners.index')->with('sucesso', 'Banner cadastrado.');
    }

    public function edit(Banner $banner)
    {
        return view('painel.banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $dados = $request->validate([
            'titulo' => 'required|string',
            'subtitulo' => 'nullable|string',
            'link' => 'nullable|url',
            'imagem' => 'nullable|image',
            'ordem' => 'nullable|integer',
            'ativo' => 'nullable|boolean'
        ]);

        if ($request->hasFile('imagem')) {
            if ($banner->imagem) Storage::disk('public')->delete($banner->imagem);
            $dados['imagem'] = $request->file('imagem')->store('banners', 'public');
        }

        $dados['ativo'] = $request->has('ativo');
        $dados['ordem'] = $dados['ordem'] ?? 0;

        $banner->update($dados);

        return redirect()->route('admin.system.banners.index')->with('sucesso', 'Banner atualizado.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->imagem) Storage::disk('public')->delete($banner->imagem);
        $banner->delete();
        return back()->with('sucesso', 'Excluído.');
    }
}
