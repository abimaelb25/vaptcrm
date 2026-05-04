<?php

namespace App\Http\Controllers\SuperAdmin\Support;

use App\Http\Controllers\Controller;
use App\Models\AcademyTrack;
use App\Models\AcademyCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AcademyCourseController extends Controller
{
    public function index()
    {
        $courses = AcademyCourse::query()
            ->with('track')
            ->withCount('conteudos')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        $tracks = AcademyTrack::query()
            ->where('publicado', true)
            ->orderBy('ordem')
            ->orderBy('titulo')
            ->get();

        return view('super-admin.support.academy-courses.index', compact('courses', 'tracks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'track_id' => 'nullable|exists:academy_tracks,id',
            'nome' => 'required|string|max:160',
            'descricao' => 'nullable|string|max:1000',
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'nullable|boolean',
        ]);

        $baseSlug = Str::slug($data['nome']);
        $slug = $baseSlug;
        $counter = 1;

        while (AcademyCourse::where('slug', $slug)->exists()) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        AcademyCourse::create([
            'track_id' => $data['track_id'] ?? null,
            'nome' => $data['nome'],
            'slug' => $slug,
            'descricao' => $data['descricao'] ?? null,
            'ordem' => (int) ($data['ordem'] ?? 0),
            'ativo' => $request->boolean('ativo', true),
        ]);

        return back()->with('success', 'Módulo criado com sucesso.');
    }

    public function update(Request $request, AcademyCourse $academyCurso)
    {
        $data = $request->validate([
            'track_id' => 'nullable|exists:academy_tracks,id',
            'nome' => 'required|string|max:160',
            'descricao' => 'nullable|string|max:1000',
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'nullable|boolean',
        ]);

        $academyCurso->update([
            'track_id' => $data['track_id'] ?? null,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'ordem' => (int) ($data['ordem'] ?? 0),
            'ativo' => $request->boolean('ativo'),
        ]);

        return back()->with('success', 'Módulo atualizado com sucesso.');
    }

    public function destroy(AcademyCourse $academyCurso)
    {
        if ($academyCurso->conteudos()->count() > 0) {
            return back()->with('error', 'Não é possível excluir um curso com aulas vinculadas.');
        }

        $academyCurso->delete();

        return back()->with('success', 'Módulo removido com sucesso.');
    }
}
