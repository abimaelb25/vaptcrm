<?php

declare(strict_types=1);

namespace App\Services\Domain;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 23:45
*/

use Illuminate\Support\Facades\Cache;
use App\Models\SiteConfiguracao;
use App\Models\PaginaLegal;
use Carbon\Carbon;

class LegalPageRenderService
{
    /**
     * Renderiza o conteúdo da página legal trocando as variáveis dinâmicas.
     */
    public function render(PaginaLegal $pagina): string
    {
        $conteudo = $pagina->conteudo ?? '';
        if (empty($conteudo)) {
            return '';
        }

        $lojaId = $pagina->loja_id ?? 'global';
        $configs = Cache::remember("site_configs_{$lojaId}", 3600, function () use ($lojaId) {
            return SiteConfiguracao::query()
                ->when($lojaId !== 'global', fn($q) => $q->where('loja_id', $lojaId))
                ->pluck('valor', 'chave')
                ->toArray();
        });

        $placeholders = [
            '{{ loja_nome }}'     => $configs['empresa_nome'] ?? 'Nossa Loja',
            '{{ loja_email }}'    => $configs['empresa_email'] ?? 'contato@loja.com',
            '{{ loja_telefone }}' => $configs['empresa_telefone'] ?? '',
            '{{ loja_whatsapp }}' => $configs['empresa_whatsapp'] ?? '',
            '{{ loja_endereco }}' => $configs['empresa_endereco'] ?? 'Endereço não informado',
            '{{ loja_cidade_uf }}'=> $configs['empresa_cidade_uf'] ?? '',
            '{{ loja_site }}'     => $configs['empresa_site'] ?? url('/'),
            '{{ loja_pix }}'      => $configs['empresa_pix_chave'] ?? '',
            '{{ data_atual }}'    => Carbon::now()->format('d/m/Y'),
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $conteudo);
    }

    /**
     * Retorna modelos pré-prontos para as páginas legais.
     */
    public function getTemplates(): array
    {
        return [
            'politica_privacidade' => [
                'titulo' => 'Política de Privacidade',
                'conteudo' => $this->getTemplatePrivacidade(),
            ],
            'termos_condicoes' => [
                'titulo' => 'Termos e Condições de Uso',
                'conteudo' => $this->getTemplateTermos(),
            ],
            'reembolso_devolucao' => [
                'titulo' => 'Política de Reembolso e Devolução',
                'conteudo' => $this->getTemplateReembolso(),
            ],
            'entregas_finalizacoes' => [
                'titulo' => 'Política de Entregas e Prazos',
                'conteudo' => $this->getTemplateEntregas(),
            ]
        ];
    }

    private function getTemplatePrivacidade(): string
    {
        return "<h3>1. Introdução</h3>
<p>A privacidade e a segurança dos dados dos nossos clientes são prioridade para a <strong>{{ loja_nome }}</strong>. Esta Política de Privacidade descreve como coletamos, usamos, armazenamos e protegemos suas informações pessoais ao utilizar nosso site ({{ loja_site }}) e nossos serviços.</p>

<h3>2. Dados que Coletamos</h3>
<p>Para processar seus pedidos, coletamos informações sensíveis como nome completo, endereço ({{ loja_cidade_uf }} ou arredores), e-mail ({{ loja_email }}), telefone ({{ loja_telefone }}) e CPF/CNPJ.</p>

<h3>3. Uso das Informações</h3>
<p>As informações coletadas são utilizadas exclusivamente para:<br>
- Processamento e entrega de pedidos gráficos.<br>
- Emissão de notas fiscais.<br>
- Contato direto pelo nosso WhatsApp oficial: {{ loja_whatsapp }}.</p>

<h3>4. Contato e Encarregado de Dados</h3>
<p>Dúvidas sobre a coleta de dados podem ser encaminhadas para o e-mail <strong>{{ loja_email }}</strong> com o assunto \"Privacidade: LGPD\".</p>
<p><em>Atualizado em {{ data_atual }}</em></p>";
    }

    private function getTemplateTermos(): string
    {
         return "<h3>1. Aceitação dos Termos</h3>
<p>Ao realizar um pedido na <strong>{{ loja_nome }}</strong>, localizada em <strong>{{ loja_endereco }} - {{ loja_cidade_uf }}</strong>, o cliente concorda integralmente com os termos aqui dispostos.</p>

<h3>2. Garantia de Cores</h3>
<p>A gráfica compromete-se com o padrão de excelência, mas adverte que poderão haver variações de até 10% nas cores originais da arte aprovada, devido a diferenças nos perfis de calibração de telas de computadores.</p>

<h3>3. Responsabilidade Sobre a Arte</h3>
<p>Todo arquivo enviado à <strong>{{ loja_nome }}</strong> é de responsabilidade técnica e intelectual do cliente, e deve respeitar as margens de fechamento (sangria).</p>

<p><em>Atualizado em {{ data_atual }}</em></p>";
    }

    private function getTemplateReembolso(): string
    {
        return "<h3>1. Condições de Troca</h3>
<p>Por tratar-se de material de comunicação visual/impresso feito sob demanda e de forma estritamente personalizada, não prevemos a possibilidade de arrependimento (Art. 49, CDC) na aprovação de produções cujo layout e materiais tenham sido previamente confirmados pelo cliente.</p>

<h3>2. Defeitos e Ressarcimentos</h3>
<p>A <strong>{{ loja_nome }}</strong> efetuará o reembolso integral na modalidade Pix (chave ou PIX associado para a conta de destino) se for comprovado defeito estrutural na impressão.</p>

<h3>3. Contato de Suporte</h3>
<p>Sinalize defeitos pelo nosso suporte: <strong>{{ loja_whatsapp }}</strong> dentro de 7 dias do recebimento.</p>

<p><em>Atualizado em {{ data_atual }}</em></p>";
    }

    private function getTemplateEntregas(): string
    {
        return "<h3>1. Estimativas de Produção</h3>
<p>A <strong>{{ loja_nome }}</strong> trabalha com um fluxo produtivo organizado em etapas. O prazo de produção inicia-se apenas após a aprovação da arte final e confirmação de pagamento.</p>

<h3>2. Despacho e Modalidades</h3>
<p>Localizada em <strong>{{ loja_cidade_uf }}</strong>, possuímos retirada via balcão e envios estaduais/nacionais dependentes do código de rastreamento. A gráfica não se responsabiliza por eventuais perdas logísticas na malha de transportadoras terceiras.</p>

<p><em>Atualizado em {{ data_atual }}</em></p>";
    }
}
