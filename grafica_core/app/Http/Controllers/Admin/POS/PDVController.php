<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\POS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-13T14:35:00-03:00
*/

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Pagamento;
use App\Models\Categoria;
use App\Models\MovimentacaoFinanceira;
use App\Models\SiteConfiguracao;
use App\Services\Domain\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Caixa;
use Illuminate\View\View;

class PDVController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Tela principal do PDV.
     */
    public function index(): View
    {
        $categorias = Categoria::where('ativo', true)->orderBy('ordem_exibicao')->get(['id', 'nome']);
        
        // Dados para PIX BR Code
        $pixConfig = [
            'chave'        => SiteConfiguracao::where('chave', 'empresa_pix_chave')->value('valor'),
            'beneficiario' => SiteConfiguracao::where('chave', 'empresa_beneficiario')->value('valor') ?? 'Grafica Vapt Vupt',
            'cidade'       => SiteConfiguracao::where('chave', 'empresa_cidade')->value('valor') ?? 'SAO PAULO',
        ];

        // IDs dos produtos mais vendidos (últimos 90 dias)
        $topProductIds = ItemPedido::select('produto_id', DB::raw('SUM(quantidade) as total_vendido'))
            ->whereNotNull('produto_id')
            ->where('created_at', '>=', now()->subDays(90))
            ->groupBy('produto_id')
            ->orderByDesc('total_vendido')
            ->limit(50)
            ->pluck('total_vendido', 'produto_id')
            ->toArray();

        $funcionarios = \App\Models\Usuario::where('ativo', true)
            ->whereIn('perfil', ['atendente', 'administrador'])
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $caixaAtivo = $this->getCaixaAtivo();

        return view('painel.pdv.index', [
            'pixConfig'        => $pixConfig,
            'categorias'       => $categorias,
            'topProductIds'    => $topProductIds,
            'caixaAberto'      => $caixaAtivo,
            'funcionarios'     => $funcionarios,
            'statusIniciais'   => [
                Pedido::STATUS_EM_PRODUCAO => 'Enviar para Produção',
                Pedido::STATUS_RASCUNHO => 'Salvar como Rascunho',
                Pedido::STATUS_AGUARDANDO => 'Aguardar Aprovação',
            ]
        ]);
    }

    /**
     * Verifica status do caixa via AJAX.
     */
    public function statusCaixa(): JsonResponse
    {
        $caixa = $this->getCaixaAtivo();
        return response()->json([
            'aberto' => !!$caixa,
            'caixa'  => $caixa
        ]);
    }

    /**
     * Abre o caixa com valor inicial.
     */
    public function abrirCaixa(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('usuario_id', auth()->id());
            
            // Verifica se o funcionário selecionado já tem um aberto
            if (Caixa::getAberto((int)$userId)) {
                $targetName = \App\Models\Usuario::find($userId)->nome ?? 'funcionário';
                return response()->json(['success' => false, 'message' => "O funcionário {$targetName} já possui um caixa aberto."], 400);
            }

            $caixa = Caixa::create([
                'usuario_id'    => $userId,
                'valor_inicial' => (float) $request->input('valor_inicial', 0),
                'status'        => 'aberto',
                'data_abertura' => now(),
            ]);

            // Grava na sessão para persistência no terminal
            session(['pos_active_caixa_id' => $caixa->id]);

            return response()->json([
                'success' => true,
                'caixa'   => $caixa
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fecha o caixa calculando totais.
     */
    public function fecharCaixa(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $caixa = Caixa::getAberto($userId);

            if (!$caixa) {
                return response()->json(['success' => false, 'message' => 'Nenhum caixa aberto encontrado.'], 404);
            }

            // Soma movimentações deste caixa
            $totalVendas = $caixa->movimentacoes()->sum('valor');
            $valorInformado = (float) $request->input('valor_fechamento', 0);
            $diferenca = $valorInformado - ($caixa->valor_inicial + $totalVendas);

            $caixa->update([
                'data_fechamento'  => now(),
                'valor_vendas'     => $totalVendas,
                'valor_fechamento' => $valorInformado,
                'diferenca'        => $diferenca,
                'status'           => 'fechado',
                'observacoes'      => $request->input('observacoes'),
            ]);

            // Limpa sessão ao fechar
            session()->forget('pos_active_caixa_id');

            return response()->json([
                'success' => true,
                'resumo'  => [
                    'vendas' => $totalVendas,
                    'diferenca' => $diferenca
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Busca AJAX de clientes com debounce server-side.
     */
    public function buscarClientes(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $clientes = Cliente::query()
            ->where(function ($q) use ($term) {
                // Remove caracteres não numéricos para busca por WhatsApp/CPF
                $onlyNumbers = preg_replace('/\D/', '', $term);
                
                $q->where('nome', 'like', "%{$term}%")
                  ->orWhere('whatsapp', 'like', "%{$term}%");

                if (!empty($onlyNumbers)) {
                    $q->orWhere('whatsapp', 'like', "%{$onlyNumbers}%")
                      ->orWhere('cpf_cnpj', 'like', "%{$onlyNumbers}%");
                }

                $q->orWhere('email', 'like', "%{$term}%");
            })
            ->where('status', 'ativo')
            ->orderBy('nome')
            ->limit(12)
            ->get(['id', 'nome', 'whatsapp', 'email', 'cpf_cnpj']);

        return response()->json($clientes);
    }

    /**
     * Busca AJAX de produtos com ranking de mais vendidos.
     */
    public function buscarProdutos(Request $request): JsonResponse
    {
        try {
            $term = trim((string) $request->query('q', ''));

            $query = Produto::query()
                ->where('ativo', true)
                ->select(['id', 'nome', 'preco_base', 'prazo_estimado', 'imagem_principal', 'categoria_id']);

            if (mb_strlen($term) >= 2) {
                $query->where(function ($q) use ($term) {
                    $q->where('nome', 'like', "%{$term}%")
                      ->orWhere('slug', 'like', "%{$term}%");
                });
            }

            // Adiciona contagem de vendas para ordenação
            $query->withCount(['itensPedido as total_vendido' => function ($q) {
                $q->select(DB::raw('COALESCE(SUM(quantidade), 0)'));
            }]);

            $produtos = $query
                ->orderByDesc('total_vendido')
                ->orderBy('nome')
                ->limit(50)
                ->get();

            return response()->json($produtos);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cadastro rápido de cliente via PDV.
     */
    public function clienteRapido(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'whatsapp' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            $cliente = Cliente::create(array_merge($validated, [
                'status' => 'ativo',
                'origem_lead' => 'pdv'
            ]));

            return response()->json([
                'success' => true,
                'cliente' => $cliente
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cadastro rápido de produto/serviço via PDV.
     */
    public function produtoRapido(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'preco' => 'required|numeric|min:0',
                'tipo' => 'required|in:produto,servico',
                'categoria_id' => 'nullable|exists:categorias,id',
            ]);

            $slug = Str::slug($validated['nome']) . '-' . rand(100, 999);

            $categoriaNome = 'Diversos';
            if (!empty($validated['categoria_id'])) {
                $categoriaNome = Categoria::find($validated['categoria_id'])->nome ?? 'Diversos';
            }

            $produto = Produto::create([
                'nome' => $validated['nome'],
                'slug' => $slug,
                'preco_base' => $validated['preco'],
                'categoria_id' => $validated['categoria_id'] ?? null,
                'categoria' => $categoriaNome,
                'descricao_curta' => "Cadastro rápido via PDV ({$validated['tipo']})",
                'ativo' => true,
                'visibilidade' => 'interno'
            ]);

            return response()->json([
                'success' => true,
                'produto' => $produto
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Finalização da venda no PDV.
     */
    public function finalizar(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $userId = auth()->id();

                // 1. Preparar dados para o OrderService
                $items = array_map(function ($item) {
                    return [
                        'produto_id'    => $item['produto_id'] ?? null,
                        'quantity'      => (int) $item['quantidade'],
                        'unitary_value' => (float) $item['valor_unitario'],
                        'description'   => $item['nome'],
                    ];
                }, $request->input('itens'));

                // Buscar caixa ativo (obrigatório) - agora via lógica centralizada
                $caixa = $this->getCaixaAtivo();
                if (!$caixa) {
                    throw new \Exception('É necessário abrir o caixa antes de finalizar uma venda.');
                }

                $data = [
                    'cliente_id'        => $request->input('cliente_id'),
                    'origin'            => Pedido::ORIGEM_PDV,
                    'status'            => $request->input('status_inicial', Pedido::STATUS_EM_PRODUCAO),
                    'delivery_type'     => $request->input('tipo_entrega', 'retirada'),
                    'delivery_deadline' => $request->input('prazo_entrega'),
                    'discount'          => (float) $request->input('desconto', 0),
                    'additional_fees'   => (float) $request->input('acrescimo', 0),
                    'observations'      => $request->input('observacao_geral', ''),
                    'items'             => $items,
                ];

                // 2. Criar o Pedido via Service
                $pedido = $this->orderService->create($data, $userId);

                // 3. Atualizar campos específicos do PDV
                $numeroAcompanhamento = Str::upper(Str::random(10));
                $pedido->update([
                    'tipo_atendimento'      => 'presencial',
                    'valor_recebido'        => (float) $request->input('valor_recebido'),
                    'troco'                 => (float) $request->input('troco'),
                    'atendente_id'          => $userId,
                    'numero_acompanhamento' => $numeroAcompanhamento,
                    'observacoes_internas'  => $request->input('observacao_interna', ''),
                    'observacoes_cliente'   => $request->input('observacao_geral', ''),
                ]);

                // 4. Registrar Pagamentos
                $pagamentos = $request->input('pagamentos', []);
                foreach ($pagamentos as $pg) {
                    $metodo = $pg['metodo'];
                    $valor = (float) $pg['valor'];

                    Pagamento::create([
                        'pedido_id'     => $pedido->id,
                        'tipo_cobranca' => 'presencial',
                        'gateway'       => 'balcao',
                        'metodo'        => $metodo,
                        'valor'         => $valor,
                        'status'        => 'pago',
                        'payload_original' => ['origem' => 'pdv_manual']
                    ]);

                    // Registrar no financeiro
                    MovimentacaoFinanceira::create([
                        'tipo'              => MovimentacaoFinanceira::TIPO_ENTRADA,
                        'categoria'         => 'Venda PDV',
                        'valor'             => $valor,
                        'data_movimentacao' => now(),
                        'forma_pagamento'   => $metodo,
                        'status'            => MovimentacaoFinanceira::STATUS_PAGO,
                        'pedido_id'         => $pedido->id,
                        'usuario_id'        => $userId,
                        'caixa_id'          => $caixa->id,
                        'descricao'         => "Recebimento PDV - Pedido #{$pedido->numero} ({$metodo})",
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'pedido_id' => $pedido->id,
                    'numero' => $pedido->numero,
                    'acompanhamento' => $numeroAcompanhamento,
                    'os_url' => route('admin.pos.os', $pedido->id)
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar venda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gera visualização da Ordem de Serviço para impressão.
     */
    public function gerarOS(Pedido $pedido): View
    {
        $pedido->load(['cliente', 'atendente', 'itens.produto', 'pagamentos']);

        $config = [
            'nome_fantasia' => SiteConfiguracao::where('chave', 'empresa_nome')->value('valor') ?? 'vaptCRM Gráfica',
            'whatsapp' => SiteConfiguracao::where('chave', 'empresa_whatsapp')->value('valor') ?? '',
            'endereco' => SiteConfiguracao::where('chave', 'empresa_endereco')->value('valor') ?? '',
        ];

        return view('painel.pdv.os', [
            'pedido' => $pedido,
            'config' => $config
        ]);
    }

    /**
     * Obtém o caixa ativo para a sessão ou usuário logado.
     */
    private function getCaixaAtivo(): ?Caixa
    {
        $sessionCaixaId = session('pos_active_caixa_id');
        if ($sessionCaixaId) {
            $caixa = Caixa::find($sessionCaixaId);
            if ($caixa && $caixa->status === 'aberto') {
                return $caixa;
            }
            session()->forget('pos_active_caixa_id');
        }

        return Caixa::getAberto((int)auth()->id());
    }
}
