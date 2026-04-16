<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalogo;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-10 23:33 -03:00
*/

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Categoria;
use App\Models\Depoimento;
use App\Models\Produto;
use App\Models\SaaS\Plano;
use App\Services\MetricaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function __construct(
        private readonly MetricaService $metricaService
    ) {}
    private function categoriasParaMenu(): \Illuminate\Database\Eloquent\Collection
    {
        return Categoria::where('ativo', true)
            ->withCount(['produtos as total_publico' => function ($q) {
                $q->where('ativo', true)->whereIn('visibilidade', ['publico', 'ambos']);
            }])
            ->orderBy('ordem_exibicao')
            ->orderBy('nome')
            ->get();
    }

    public function inicio(Request $request): mixed
    {
        $tenantContext = app(\App\Services\SaaS\TenantContext::class);

        // Se estivermos no contexto de uma Loja específica, mostramos o catálogo dela.
        if ($tenantContext->hasTenant()) {
            return $this->catalogo($request);
        }

        // Caso contrário, mostramos a Landing Page da PLATAFORMA (vaptCRM)
        $this->metricaService->registrarView($request, 'home_plataforma');

        // Banners Institucionais (sem loja_id filtrado automaticamente ou filtrado por nulo)
        $banners = Banner::where('ativo', true)->orderBy('ordem')->get();
        
        // Depoimentos da PLATAFORMA (Prova Social do Software)
        $depoimentos = Depoimento::daPlataforma()->publicados()->orderBy('ordem_exibicao')->get();
        
        // Planos SaaS
        $planos = Plano::where('ativo', true)->get();

        return view('publico.landing-vapt', [
            'banners'     => $banners,
            'depoimentos' => $depoimentos,
            'planos'      => $planos
        ]);
    }

    public function catalogo(Request $request): View
    {
        // Proteção: o catálogo só deve ser acessado no contexto de uma Loja/Tenant
        if (!app(\App\Services\SaaS\TenantContext::class)->hasTenant()) {
            abort(404);
        }

        $this->metricaService->registrarView($request, 'catalogo');

        $query = Produto::query()
            ->where('ativo', true)
            ->whereIn('visibilidade', ['publico', 'ambos']);

        // Busca por termo
        if ($request->filled('busca')) {
            $query->where('nome', 'like', '%' . $request->busca . '%')
                  ->orWhere('descricao_curta', 'like', '%' . $request->busca . '%');
        }

        // Filtro por categoria via query string ?categoria=id
        $categoriaAtiva = null;
        if ($request->filled('categoria')) {
            $categoriaAtiva = Categoria::where('id', $request->categoria)->first();
            if ($categoriaAtiva) {
                $query->where('categoria_id', $categoriaAtiva->id);
            }
        }

        // Ordenação Profissional
        $sort = $request->get('sort', 'destaque');
        match ($sort) {
            'preco_min' => $query->orderBy('preco_base', 'asc'),
            'preco_max' => $query->orderBy('preco_base', 'desc'),
            'novidades' => $query->latest(),
            'nome'      => $query->orderBy('nome', 'asc'),
            default     => $query->orderBy('destaque', 'desc')
                             ->orderByRaw('(SELECT ordem_exibicao FROM categorias WHERE categorias.id = produtos.categoria_id) ASC')
                             ->orderBy('ordem_exibicao', 'asc')
        };

        $produtos = $query->with(['categoriaRel', 'variacoes'])
            ->paginate(18)
            ->withQueryString();

        // Depoimentos da LOJA (Prova Social da Gráfica)
        $depoimentos = Depoimento::daLoja()->publicados()
            ->orderBy('destaque', 'desc')
            ->orderBy('ordem_exibicao')
            ->latest()
            ->take(6)
            ->get();

        return view('publico.catalogo', [
            'produtos'       => $produtos,
            'categorias'     => $this->categoriasParaMenu(),
            'categoriaAtiva' => $categoriaAtiva,
            'depoimentos'    => $depoimentos,
        ]);
    }

    public function categoriaLista(Request $request, string $slug): View
    {
        if (!app(\App\Services\SaaS\TenantContext::class)->hasTenant()) {
            abort(404);
        }

        $categoria = Categoria::where('slug', $slug)->where('ativo', true)->firstOrFail();

        $this->metricaService->registrarView($request, 'categoria', $categoria->id);

        $produtos = Produto::query()
            ->where('ativo', true)
            ->whereIn('visibilidade', ['publico', 'ambos'])
            ->where('categoria_id', $categoria->id)
            ->orderBy('nome')
            ->paginate(18);

        return view('publico.categoria', [
            'categoria'  => $categoria,
            'produtos'   => $produtos,
            'categorias' => $this->categoriasParaMenu(),
        ]);
    }

    public function produto(Request $request, Produto $produto): View
    {
        if (!app(\App\Services\SaaS\TenantContext::class)->hasTenant()) {
            abort(404);
        }

        abort_unless($produto->ativo && in_array($produto->visibilidade, ['publico', 'ambos']), 404);

        $this->metricaService->registrarView($request, 'produto', $produto->id);

        $produto->load(['variacoes', 'categoriaRel']);

        $relacionados = Produto::query()
            ->where('ativo', true)
            ->whereIn('visibilidade', ['publico', 'ambos'])
            ->where('categoria_id', $produto->categoria_id)
            ->where('id', '!=', $produto->id)
            ->take(4)
            ->get();

        return view('publico.produto', [
            'produto'     => $produto,
            'relacionados' => $relacionados,
        ]);
    }
}

