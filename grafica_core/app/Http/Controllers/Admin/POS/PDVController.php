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
use App\Services\Domain\FinanceService;
use App\Services\SaaS\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Caixa;
use Illuminate\View\View;

class PDVController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected FinanceService $financeService,
        protected TenantContext $tenantContext,
    ) {}

    /**
     * Tela principal do PDV.
     */
    public function index(): View
    {
        $this->authorize('create', Pedido::class);
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
     * Protegido pelo middleware perfil:administrador,gerente na rota.
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
     * Protegido pelo middleware perfil:administrador,gerente na rota.
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
     * Protegido pelo middleware perfil:administrador,gerente na rota.
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
     * Busca AJAX de clientes — pré-carrega todos ou filtra por termo.
     * Retorna clientes da loja logada com contagem de pedidos para identificar frequentes.
     */
    public function buscarClientes(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Cliente::class);
        $term = trim((string) $request->query('q', ''));

        $query = Cliente::query()
            ->where('status', 'ativo')
            ->select(['id', 'nome', 'whatsapp', 'email', 'cpf_cnpj', 'cidade', 'empresa', 'tipo_pessoa'])
            ->withCount('pedidos');

        if (mb_strlen($term) >= 2) {
            $query->where(function ($q) use ($term) {
                $onlyNumbers = preg_replace('/\D/', '', $term);

                $q->where('nome', 'like', "%{$term}%")
                  ->orWhere('whatsapp', 'like', "%{$term}%")
                  ->orWhere('empresa', 'like', "%{$term}%");

                if (!empty($onlyNumbers)) {
                    $q->orWhere('whatsapp', 'like', "%{$onlyNumbers}%")
                      ->orWhere('telefone', 'like', "%{$onlyNumbers}%")
                      ->orWhere('cpf_cnpj', 'like', "%{$onlyNumbers}%");
                }

                $q->orWhere('email', 'like', "%{$term}%");
            });
        }

        $clientes = $query
            ->orderByDesc('pedidos_count')
            ->orderBy('nome')
            ->limit(50)
            ->get()
            ->map(function ($cliente) {
                $cliente->is_frequente = $cliente->pedidos_count >= 3;
                return $cliente;
            });

        return response()->json($clientes);
    }

    /**
     * Busca AJAX de pedidos ativos — para consulta rápida de status no balcão.
     * Filtra por nome/whatsapp/cpf_cnpj do cliente ou número do pedido.
     */
    public function buscarPedidos(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Pedido::class);
        $term = trim((string) $request->query('q', ''));
        $incluirFinalizados = $request->boolean('incluir_finalizados', false);

        // Status considerados "ativos" para atendimento de balcão
        $statusAtivos = [
            Pedido::STATUS_RASCUNHO,
            Pedido::STATUS_AGUARDANDO,
            Pedido::STATUS_APROVADO,
            Pedido::STATUS_EM_PRODUCAO,
            Pedido::STATUS_PRONTO,
            Pedido::STATUS_EM_TRANSPORTE,
            Pedido::STATUS_AGUARDANDO_PAGAMENTO,
        ];

        $query = Pedido::query()
            ->with(['cliente:id,nome,whatsapp,cpf_cnpj', 'atendente:id,nome'])
            ->select([
                'id', 'numero', 'numero_sequencial', 'codigo_pedido', 'numero_acompanhamento', 
                'cliente_id', 'status', 'total', 'prazo_entrega', 'observacoes', 'atendente_id', 'created_at'
            ]);

        // Filtrar por status ativos (a menos que peça finalizados)
        if (!$incluirFinalizados) {
            $query->whereIn('status', $statusAtivos);
        } else {
            // Mesmo incluindo finalizados, não traz cancelados por padrão
            $query->where('status', '!=', Pedido::STATUS_CANCELADO);
        }

        if (mb_strlen($term) >= 1) {
            $onlyNumbers = preg_replace('/\D/', '', $term);

            $query->where(function ($q) use ($term, $onlyNumbers) {
                // Prioridade 1: Busca exata por número sequencial (PDV)
                if (is_numeric($term)) {
                    $q->where('numero_sequencial', (int) $term);
                }

                // Prioridade 2: Busca por código do pedido
                $q->orWhere('codigo_pedido', 'like', "%{$term}%");

                // Busca por número legado e acompanhamento
                $q->orWhere('numero', 'like', "%{$term}%")
                  ->orWhere('numero_acompanhamento', 'like', "%{$term}%");

                // Busca por dados do cliente
                $q->orWhereHas('cliente', function ($cq) use ($term, $onlyNumbers) {
                    $cq->where('nome', 'like', "%{$term}%");

                    if (!empty($onlyNumbers)) {
                        $cq->orWhere('whatsapp', 'like', "%{$onlyNumbers}%")
                           ->orWhere('telefone', 'like', "%{$onlyNumbers}%")
                           ->orWhere('cpf_cnpj', 'like', "%{$onlyNumbers}%");
                    }
                });
            });
        }

        $pedidos = $query
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(function ($pedido) {
                return [
                    'id' => $pedido->id,
                    'numero' => $pedido->numero,
                    'numero_sequencial' => $pedido->numero_sequencial,
                    'codigo_pedido' => $pedido->codigo_pedido,
                    'numero_balcao' => $pedido->numero_balcao,
                    'codigo_acompanhamento' => $pedido->numero_acompanhamento,
                    'status' => $pedido->status,
                    'status_label' => $this->getStatusLabel($pedido->status),
                    'status_cor' => $this->getStatusCor($pedido->status),
                    'total' => (float) $pedido->total,
                    'prazo_entrega' => $pedido->prazo_entrega?->format('d/m/Y'),
                    'observacoes' => $pedido->observacoes,
                    'data_pedido' => $pedido->created_at->format('d/m/Y H:i'),
                    'data_relativa' => $pedido->created_at->diffForHumans(),
                    'cliente' => $pedido->cliente ? [
                        'id' => $pedido->cliente->id,
                        'nome' => $pedido->cliente->nome,
                        'whatsapp' => $pedido->cliente->whatsapp,
                        'cpf_cnpj' => $pedido->cliente->cpf_cnpj,
                    ] : null,
                    'atendente' => $pedido->atendente?->nome,
                ];
            });

        return response()->json($pedidos);
    }

    /**
     * Retorna label amigável para status do pedido.
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            Pedido::STATUS_RASCUNHO => 'Rascunho',
            Pedido::STATUS_AGUARDANDO => 'Aguardando Aprovação',
            Pedido::STATUS_APROVADO => 'Aprovado',
            Pedido::STATUS_EM_PRODUCAO => 'Em Produção',
            Pedido::STATUS_PRONTO => 'Pronto',
            Pedido::STATUS_EM_TRANSPORTE => 'Em Transporte',
            Pedido::STATUS_ENTREGUE => 'Entregue',
            Pedido::STATUS_CANCELADO => 'Cancelado',
            Pedido::STATUS_AGUARDANDO_PAGAMENTO => 'Aguardando Pagamento',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Retorna cor de badge para status do pedido.
     */
    private function getStatusCor(string $status): string
    {
        return match ($status) {
            Pedido::STATUS_RASCUNHO => 'slate',
            Pedido::STATUS_AGUARDANDO => 'amber',
            Pedido::STATUS_APROVADO => 'blue',
            Pedido::STATUS_EM_PRODUCAO => 'indigo',
            Pedido::STATUS_PRONTO => 'emerald',
            Pedido::STATUS_EM_TRANSPORTE => 'cyan',
            Pedido::STATUS_ENTREGUE => 'green',
            Pedido::STATUS_CANCELADO => 'red',
            Pedido::STATUS_AGUARDANDO_PAGAMENTO => 'orange',
            default => 'gray',
        };
    }

    /**
     * Busca AJAX de produtos com ranking de mais vendidos.
     */
    public function buscarProdutos(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Produto::class);
        try {
            $term = trim((string) $request->query('q', ''));

            $query = Produto::query()
                ->where('ativo', true)
                ->select(['id', 'nome', 'preco_base', 'prazo_estimado', 'imagem_principal', 'categoria_id']);

            if (mb_strlen($term) >= 3) {
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
        $this->authorize('create', Cliente::class);

        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'whatsapp' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            // loja_id explícito: HasTenancy cobre o create(), mas tornamos explícito
            // para garantir consistência mesmo em contextos sem global scope ativo
            $lojaId = $this->tenantContext->getLojaId() ?? auth()->user()->loja_id;

            $cliente = Cliente::create(array_merge($validated, [
                'status'      => 'ativo',
                'origem_lead' => 'pdv',
                'loja_id'     => $lojaId,
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
        $this->authorize('create', Produto::class);

        try {
            $validated = $request->validate([
                'nome'         => 'required|string|max:255',
                'preco'        => 'required|numeric|min:0',
                'tipo'         => 'required|in:produto,servico',
                'categoria_id' => 'nullable|exists:categorias,id',
            ]);

            $slug = Str::slug($validated['nome']) . '-' . rand(100, 999);

            $categoriaNome = 'Diversos';
            if (!empty($validated['categoria_id'])) {
                $categoriaNome = Categoria::find($validated['categoria_id'])->nome ?? 'Diversos';
            }

            // loja_id explícito para garantir isolamento de tenant
            $lojaId = $this->tenantContext->getLojaId() ?? auth()->user()->loja_id;

            $produto = Produto::create([
                'nome'            => $validated['nome'],
                'slug'            => $slug,
                'preco_base'      => $validated['preco'],
                'categoria_id'    => $validated['categoria_id'] ?? null,
                'categoria'       => $categoriaNome,
                'descricao_curta' => "Cadastro rápido via PDV ({$validated['tipo']})",
                'ativo'           => true,
                'visibilidade'    => 'interno',
                'loja_id'         => $lojaId,
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
        $this->authorize('create', Pedido::class);

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

                // 4. Registrar Pagamentos via FinanceService (integração com Títulos Financeiros)
                $pagamentos = $request->input('pagamentos', []);
                
                // Buscar título financeiro criado pelo OrderService (se existir)
                $titulo = \App\Models\FinancialTitle::where('origem', 'pedido')
                    ->where('referencia_id', $pedido->id)
                    ->first();
                
                foreach ($pagamentos as $pg) {
                    $metodo = $pg['metodo'];
                    $valor = (float) $pg['valor'];

                    // Montar payload_original com dados específicos do método
                    $payloadOriginal = ['origem' => 'pdv_manual'];

                    // Se for cartão (crédito ou débito), incluir dados adicionais
                    if (isset($pg['card_data']) && is_array($pg['card_data'])) {
                        $cardData = $pg['card_data'];
                        $payloadOriginal = [
                            'origem'              => 'pdv_cartao_manual',
                            'bandeira'            => $cardData['bandeira'] ?? null,
                            'parcelas'            => (int) ($cardData['parcelas'] ?? 1),
                            'nsu'                 => $cardData['nsu'] ?? null,
                            'codigo_autorizacao'  => $cardData['codigo_autorizacao'] ?? null,
                            'terminal_id'         => $cardData['terminal_id'] ?? null,
                            'observacao'          => $cardData['observacao'] ?? null,
                            'operador_confirmou'  => (bool) ($cardData['operador_confirmou'] ?? false),
                            'integration_status'  => 'manual',
                            'payment_channel'     => 'pos_terminal',
                            'captured_at'         => now()->toIso8601String(),
                        ];
                    }

                    // Registrar na tabela de pagamentos (compatibilidade Stripe/Asaas)
                    Pagamento::create([
                        'pedido_id'     => $pedido->id,
                        'tipo_cobranca' => 'presencial',
                        'gateway'       => 'balcao',
                        'metodo'        => $metodo,
                        'valor'         => $valor,
                        'status'        => 'pago',
                        'payload_original' => $payloadOriginal
                    ]);

                    // Registrar pagamento via FinanceService (atualiza FinancialTitle + MovimentacaoFinanceira)
                    if ($titulo) {
                        $this->financeService->addPayment($titulo, [
                            'valor'           => $valor,
                            'forma_pagamento' => $metodo,
                            'data_pagamento'  => now(),
                            'caixa_id'        => $caixa->id,
                            'usuario_id'      => $userId,
                            'descricao'       => "Recebimento PDV - Pedido #{$pedido->numero} ({$metodo})",
                        ]);
                    } else {
                        // Fallback: se não existe título, criar MovimentacaoFinanceira diretamente
                        // Isso pode acontecer em pedidos antigos ou cenários especiais
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
     * O route model binding resolve Pedido pelo ID; authorize('view') garante
     * que apenas usuários do mesmo tenant acessem a OS.
     */
    public function gerarOS(Pedido $pedido): View
    {
        $this->authorize('view', $pedido);
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
