<?php

declare(strict_types=1);

namespace App\Http\Controllers\CMS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 23:50
*/

use App\Http\Controllers\Controller;
use App\Models\PaginaLegal;
use App\Services\Domain\LegalPageRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaginaLegalController extends Controller
{
    private const MAX_PAGINAS_PERSONALIZADAS = 5;

    public function __construct(private LegalPageRenderService $legalService) {}

    public function index()
    {
        $paginas = PaginaLegal::orderBy('ordem_exibicao')->latest()->paginate(15);
        $totalPersonalizadas = PaginaLegal::where('tipo', 'personalizada')->count();
        $limiteAtingido = $totalPersonalizadas >= self::MAX_PAGINAS_PERSONALIZADAS;

        return view('painel.paginas.index', compact('paginas', 'totalPersonalizadas', 'limiteAtingido'));
    }

    public function create(Request $request)
    {
        $totalPersonalizadas = PaginaLegal::where('tipo', 'personalizada')->count();
        if ($totalPersonalizadas >= self::MAX_PAGINAS_PERSONALIZADAS && !$request->has('template')) {
            return redirect()->route('admin.system.paginas-legais.index')
                ->with('erro', 'Limite de 5 páginas personalizadas atingido pelo seu plano.');
        }

        $pagina = new PaginaLegal();
        $pagina->ativa = true;
        $pagina->exibir_no_rodape = true;
        
        $templateSelecionado = $request->get('template');
        $templates = $this->legalService->getTemplates();

        if ($templateSelecionado && isset($templates[$templateSelecionado])) {
            $pagina->titulo = $templates[$templateSelecionado]['titulo'];
            $pagina->conteudo = $templates[$templateSelecionado]['conteudo'];
            $pagina->tipo = $templateSelecionado;
        }

        return view('painel.paginas.form', compact('pagina', 'templates', 'templateSelecionado'));
    }

    public function store(Request $request)
    {
        $validados = $this->validarRequest($request);

        if (($validados['tipo'] ?? 'personalizada') === 'personalizada') {
            $totalPersonalizadas = PaginaLegal::where('tipo', 'personalizada')->count();
            if ($totalPersonalizadas >= self::MAX_PAGINAS_PERSONALIZADAS) {
                return back()->withInput()->with('erro', 'Limite de páginas personalizadas excedido.');
            }
        }

        // Garante slug unico
        $baseSlug = Str::slug($validados['titulo']);
        $slug = $baseSlug;
        $counter = 1;
        while (PaginaLegal::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }
        $validados['slug'] = $slug;

        PaginaLegal::create($validados);

        return redirect()->route('admin.system.paginas-legais.index')->with('sucesso', 'Página legal criada com sucesso.');
    }

    public function edit(PaginaLegal $pagina)
    {
        $templates = $this->legalService->getTemplates();
        return view('painel.paginas.form', compact('pagina', 'templates'));
    }

    public function update(Request $request, PaginaLegal $pagina)
    {
        $validados = $this->validarRequest($request);

        if ($validados['titulo'] !== $pagina->titulo) {
            $baseSlug = Str::slug($validados['titulo']);
            $slug = $baseSlug;
            $counter = 1;
            while (PaginaLegal::where('slug', $slug)->where('id', '!=', $pagina->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $validados['slug'] = $slug;
        }

        $pagina->update($validados);

        return redirect()->route('admin.system.paginas-legais.index')->with('sucesso', 'Página atualizada com sucesso.');
    }

    public function destroy(PaginaLegal $pagina)
    {
        if ($pagina->pagina_sistema) {
            return back()->with('erro', 'Você não pode excluir uma página essencial do sistema.');
        }

        $pagina->delete();
        return back()->with('sucesso', 'Página removida com sucesso.');
    }

    private function validarRequest(Request $request): array
    {
        $regras = [
            'titulo' => 'required|string|max:200',
            'tipo' => 'nullable|string|max:100',
            'resumo' => 'nullable|string|max:300',
            'conteudo' => 'required|string',
            'ordem_exibicao' => 'nullable|integer'
        ];

        $dados = $request->validate($regras);
        
        $dados['tipo'] = $dados['tipo'] ?? 'personalizada';
        $dados['ativa'] = $request->has('ativa');
        $dados['exibir_no_rodape'] = $request->has('exibir_no_rodape');
        $dados['ordem_exibicao'] = $dados['ordem_exibicao'] ?? 0;

        return $dados;
    }
}
