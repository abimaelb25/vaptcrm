<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalogo;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-06 00:00 -03:00
*/

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Cupom;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use App\Services\CupomService;
use App\Services\FreteService;
use App\Services\MercadoPagoConfigService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        protected FreteService $freteService,
        protected CupomService $cupomService,
        protected MercadoPagoConfigService $mercadoPagoService,
        protected \App\Services\Pix\AsaasService $asaasService
    ) {}

    public function store(Request $request, Produto $produto): RedirectResponse
    {
        $dados = $request->validate([
            'nome_cliente' => ['required', 'string', 'max:150'],
            'telefone_cliente' => ['required', 'string', 'max:20'],
            'email_cliente' => ['nullable', 'email', 'max:150'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'especificacoes' => ['nullable', 'string', 'max:1000'],
            'tipo_arte' => ['nullable', 'string', 'in:enviar,contratar'],
            'arte_arquivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'variacoes' => ['nullable', 'array'],
            'cupom_codigo' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            DB::beginTransaction();

            // 1. Identificar ou Criar Cliente
            // Busca priorizando telefone limpar ou email (para evitar duplicidade burra, buscaremos exato pelo telefone)
            $cliente = Cliente::query()
                ->where('telefone', trim((string) $dados['telefone_cliente']))
                ->first();

            if (! $cliente) {
                // Tenta pelo e-mail se foi preenchido e não achou telefone
                if (! empty($dados['email_cliente'])) {
                    $cliente = Cliente::query()->where('email', $dados['email_cliente'])->first();
                }

                // Se realmente não existe, cadastra como Lead
                if (! $cliente) {
                    $cliente = Cliente::query()->create([
                        'loja_id' => $produto->loja_id,
                        'nome' => strip_tags((string) $dados['nome_cliente']),
                        'telefone' => strip_tags((string) $dados['telefone_cliente']),
                        'email' => isset($dados['email_cliente']) ? strip_tags((string) $dados['email_cliente']) : null,
                        'origem_lead' => 'Site',
                        'status' => 'novo_contato',
                    ]);
                }
            }

            // 2. Montar o Pedido
            $numeroBase = 'SITE-' . now()->format('ymd') . '-' . Str::upper(Str::random(4));
            
            // Taxas de Arte (Upsell)
            $taxaArte = 0;
            $servicoArteIncluso = false;
            if ($produto->exige_arte && ($dados['tipo_arte'] ?? '') === 'contratar') {
                $taxaArte = (float) ($produto->preco_arte ?? 0);
                $servicoArteIncluso = true;
            }

            // Lógica de Variações Selecionadas
            $adicionalVariacoes = 0;
            $textoVariacoes = "";
            if (!empty($dados['variacoes']) && is_array($dados['variacoes'])) {
                $variacoesBanco = $produto->variacoes;
                foreach ($dados['variacoes'] as $tipo => $opcao) {
                    if (empty($opcao)) continue;
                    
                    $varEncontrada = $variacoesBanco->where('tipo_variacao', $tipo)->where('nome_opcao', $opcao)->first();
                    if ($varEncontrada) {
                        $adicionalVariacoes += (float) $varEncontrada->acrescimo_venda;
                        $textoVariacoes .= "\n> {$tipo}: {$opcao}" . ($varEncontrada->acrescimo_venda > 0 ? " (+ R$ " . number_format((float) $varEncontrada->acrescimo_venda, 2, ',', '.') . ")" : "");
                    } else {
                        $textoVariacoes .= "\n> {$tipo}: {$opcao}";
                    }
                }
            }

            $valorUnitarioCalculado = ((float) $produto->preco_base) + $adicionalVariacoes;
            $subtotalItens = $valorUnitarioCalculado * (int) $dados['quantidade'];

            // Pega o primeiro atendente ou admin ativo para ser o dono inicial, se não houver usa 1
            $responsavel = \App\Models\Usuario::whereIn('perfil', ['administrador', 'atendimento'])->where('ativo', true)->first();
            $respId = $responsavel ? $responsavel->id : 1;

            // Aplicar frete fixo baseado na configuração do responsável
            $valorFrete = $this->freteService->calculateForOrder($respId, 'retirada');

            // Aplicar cupom se informado
            $cupomResult = $this->cupomService->validateAndApply(
                $dados['cupom_codigo'] ?? null,
                $subtotalItens,
                $respId
            );

            $descontoCupom = $cupomResult['valido'] ? $cupomResult['desconto'] : 0;
            $cupomId = $cupomResult['valido'] ? $cupomResult['cupom_id'] : null;

            if ($cupomResult['valido'] && $cupomId) {
                $this->cupomService->incrementUsage($cupomId);
            }

            $totalGeral = max(0, $subtotalItens + $taxaArte + $valorFrete - $descontoCupom);

            $pedido = Pedido::query()->create([
                'loja_id' => $produto->loja_id,
                'numero' => $numeroBase,
                'origem' => 'online',
                'cliente_id' => $cliente->id,
                'responsavel_id' => $respId,
                'status' => Pedido::STATUS_AGUARDANDO,
                'tipo_total' => 'automatico',
                'subtotal' => $subtotalItens,
                'valor_frete' => $valorFrete,
                'taxas_adicionais' => $taxaArte,
                'desconto' => 0,
                'cupom_id' => $cupomId,
                'valor_desconto_cupom' => $descontoCupom,
                'total' => $totalGeral,
                'tipo_entrega' => 'retirada',
                'observacoes' => 'Pedido originado via Catálogo Público.' . ($cupomResult['mensagem'] ? ' ' . $cupomResult['mensagem'] : ''),
            ]);

            // Upload de Arte se houver
            $caminhoArte = null;
            if ($produto->exige_arte && ($dados['tipo_arte'] ?? '') === 'enviar' && $request->hasFile('arte_arquivo')) {
                // Guarda fora do public, necessita autenticacao para baixar via controller
                $caminhoArte = $request->file('arte_arquivo')->store('artes_pedidos');
            }

            // 3. Montar o Único Item (Produto do Catálogo)
            $textoArte = $servicoArteIncluso ? "\n> Contratou Criação da Arte" : "";
            $descricaoItem = sprintf(
                "Produto: %s\nEspecificações do Cliente: %s%s%s",
                $produto->nome,
                !empty($dados['especificacoes']) ? strip_tags((string) $dados['especificacoes']) : 'Nenhuma',
                $textoArte,
                $textoVariacoes
            );

            ItemPedido::query()->create([
                'loja_id' => $produto->loja_id,
                'pedido_id' => $pedido->id,
                'produto_id' => $produto->id,
                'descricao_item' => $descricaoItem,
                'quantidade' => (int) $dados['quantidade'],
                'valor_unitario' => $valorUnitarioCalculado,
                'valor_total' => $subtotalItens,
                'caminho_arte' => $caminhoArte,
                'servico_arte_incluso' => $servicoArteIncluso,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Erro Checkout DB: ' . $e->getMessage() . ' no arquivo ' . $e->getFile() . ':' . $e->getLine());
            return back()->with('erro', 'Ocorreu um erro ao processar seu pedido. Tente novamente ou nos chame no WhatsApp. Detalhe técnico: ' . $e->getMessage());
        }

        // Se chegou aqui, pedido e itens foram criados com sucesso no banco.
        // Vamos tentar conectar ao Stripe para pagamento online imediato.
        try {
            $stripeService = app(\App\Services\StripeService::class);
            $pagamento = $stripeService->criarCheckoutOnline($pedido);

            // Redireciona o usuário para a sessão do Stripe (Checkout gerado lá na Stripe)
            return redirect()->away($pagamento->stripe_checkout_url);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao gerar Stripe Checkout no Catálogo: ' . $e->getMessage());

            // Tenta Redundância com Asaas PIX
            try {
                // Para o Asaas, precisaríamos do customer_id. Se não tivermos um mapeamento, 
                // precisaríamos criar o cliente lá primeiro. Por simplificação técnica neste estágio,
                // vamos assumir que o sistema enviará os dados básicos se o Asaas permitir criação rápida ou se tivermos o ID.
                // Como não queremos travar o checkout, se falhar aqui também, vai para o sucesso padrão.
                
                // Exemplo simplificado de fallback (lógica real dependeria de ter o customer_id do Asaas)
                // Se não implementado totalmente o "criar cliente no asaas", mantemos o redirect de aviso.
                
                return redirect()->route('site.checkout.sucesso', ['numero' => $pedido->numero])
                    ->with('sucesso_checkout', 'Pedido recebido! Houve uma instabilidade momentânea no cartão. Favor aguardar nosso contato para enviarmos o seu PIX de pagamento.');
            } catch (\Throwable $asaasError) {
                return redirect()->route('site.checkout.sucesso', ['numero' => $pedido->numero])
                    ->with('sucesso_checkout', 'Pedido recebido! Favor aguardar nosso contato via WhatsApp para combinarmos o pagamento.');
            }
        }
    }

    public function sucesso($numero)
    {
        $pedido = Pedido::where('numero', $numero)->firstOrFail();
        
        return view('publico.checkout-sucesso', [
            'pedido' => $pedido
        ]);
    }
}

