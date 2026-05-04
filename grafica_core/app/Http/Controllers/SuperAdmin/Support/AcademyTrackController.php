<?php

namespace App\Http\Controllers\SuperAdmin\Support;

use App\Http\Controllers\Controller;
use App\Models\AcademyTrack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AcademyTrackController extends Controller
{
    public function index(): View
    {
        $tracks = AcademyTrack::query()
            ->withCount('modulos')
            ->with(['modulos' => fn ($query) => $query->withCount('conteudos')->orderBy('ordem')->orderBy('nome')])
            ->orderBy('ordem')
            ->orderBy('titulo')
            ->get();

        return view('super-admin.support.academy-tracks.index', compact('tracks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:160',
            'descricao' => 'nullable|string|max:2000',
            'ordem' => 'nullable|integer|min:0',
            'publicado' => 'nullable|boolean',
            'destaque' => 'nullable|boolean',
        ]);

        AcademyTrack::create([
            'titulo' => $data['titulo'],
            'slug' => $this->gerarSlugUnico($data['titulo']),
            'descricao' => $data['descricao'] ?? null,
            'ordem' => (int) ($data['ordem'] ?? 0),
            'publicado' => $request->boolean('publicado', true),
            'destaque' => $request->boolean('destaque'),
        ]);

        return back()->with('success', 'Trilha criada com sucesso.');
    }

    public function update(Request $request, AcademyTrack $academyTrilha): RedirectResponse
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:160',
            'descricao' => 'nullable|string|max:2000',
            'ordem' => 'nullable|integer|min:0',
            'publicado' => 'nullable|boolean',
            'destaque' => 'nullable|boolean',
        ]);

        $academyTrilha->update([
            'titulo' => $data['titulo'],
            'slug' => $academyTrilha->titulo !== $data['titulo'] ? $this->gerarSlugUnico($data['titulo'], $academyTrilha->id) : $academyTrilha->slug,
            'descricao' => $data['descricao'] ?? null,
            'ordem' => (int) ($data['ordem'] ?? 0),
            'publicado' => $request->boolean('publicado'),
            'destaque' => $request->boolean('destaque'),
        ]);

        return back()->with('success', 'Trilha atualizada com sucesso.');
    }

    public function destroy(AcademyTrack $academyTrilha): RedirectResponse
    {
        if ($academyTrilha->modulos()->count() > 0) {
            return back()->with('error', 'Não é possível excluir uma trilha com módulos vinculados.');
        }

        $academyTrilha->delete();

        return back()->with('success', 'Trilha removida com sucesso.');
    }

    private function gerarSlugUnico(string $titulo, ?int $ignorarId = null): string
    {
        $baseSlug = Str::slug($titulo);
        $slug = $baseSlug;
        $counter = 2;

        while (AcademyTrack::query()
            ->when($ignorarId, fn ($query) => $query->where('id', '!=', $ignorarId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
