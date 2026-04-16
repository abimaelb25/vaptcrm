<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // Validação adicional pode ser feita via Spatie/Policies se necessário
    }

    public function rules(): array
    {
        return [
            // Identificação e Cadastro
            'modelo_cadastro'    => ['required', 'string', 'in:simples,configuravel,tecnico'],
            'segmento'           => ['required', 'string', 'in:grafica_rapida,comunicacao_visual,grafica_industrial'],
            'nome'               => ['required', 'string', 'max:150'],
            'subtitulo_comercial'=> ['nullable', 'string', 'max:150'],
            'categoria_id'       => ['nullable', 'integer', 'exists:categorias,id'],
            'visibilidade'       => ['required', 'string', 'in:interno,publico,ambos'],
            
            // Comercial e Marketing
            'frase_efeito'       => ['nullable', 'string', 'max:100'],
            'badge_comercial'    => ['nullable', 'string', 'max:50'],
            'descricao_curta'    => ['nullable', 'string', 'max:255'],
            'descricao_completa' => ['nullable', 'string'],
            'imagem_destaque'     => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'imagens_adicionais' => ['nullable', 'array'],
            'imagens_adicionais.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'destaque'           => ['nullable', 'boolean'],
            'ordem_exibicao'     => ['nullable', 'integer'],
            'ativo'              => ['nullable', 'boolean'],

            // Dados Básicos de Venda
            'preco_base'         => ['nullable', 'numeric', 'min:0'],
            'unidade_venda'      => ['required', 'string', 'max:20'],
            'prazo_estimado'     => ['nullable', 'string', 'max:100'],
            'exige_arte'         => ['nullable', 'boolean'],
            'oferece_design'     => ['nullable', 'boolean'],
            'preco_arte'         => ['nullable', 'numeric', 'min:0'],
            'custo_design'       => ['nullable', 'numeric', 'min:0'],

            // Especificações Técnicas (Nível 2/3)
            'largura'            => ['nullable', 'numeric', 'min:0'],
            'altura'             => ['nullable', 'numeric', 'min:0'],
            'formato'            => ['nullable', 'string', 'max:50'],
            'orientacao'         => ['nullable', 'string', 'in:vertical,horizontal,quadrado'],
            'gramatura'          => ['nullable', 'integer', 'min:0'],
            'tipo_impressao'     => ['nullable', 'string', 'max:50'],
            'cor_impressao'      => ['nullable', 'string', 'max:20'],
            'modo_producao'      => ['nullable', 'string', 'in:digital,offset,comunicacao_visual,terceirizado,outro'],

            // Precificação Técnica (Nível 3)
            'custo_base'         => ['nullable', 'numeric', 'min:0'],
            'custo_producao'     => ['nullable', 'numeric', 'min:0'],
            'margem_lucro'       => ['nullable', 'numeric', 'min:0'],
            'preco_sugerido'     => ['nullable', 'numeric', 'min:0'],

            // Produção e SEO
            'instrucoes_internas'=> ['nullable', 'string'],
            'checklist_producao' => ['nullable', 'string'],
            'meta_title'         => ['nullable', 'string', 'max:70'],
            'meta_description'   => ['nullable', 'string', 'max:160'],

            // Materiais (Array)
            'materiais'          => ['nullable', 'array'],
            'materiais.*.nome'   => ['required', 'string', 'max:100'],
            'materiais.*.preco_ajuste' => ['required', 'numeric'],

            // Acabamentos (Array)
            'acabamentos'        => ['nullable', 'array'],
            'acabamentos.*.nome' => ['required', 'string', 'max:100'],
            'acabamentos.*.preco_ajuste' => ['required', 'numeric'],
            'acabamentos.*.prazo_ajuste' => ['nullable', 'integer'],

            // Faixas de Quantidade (Array)
            'faixas'             => ['nullable', 'array'],
            'faixas.*.quantidade_minima' => ['required', 'integer', 'min:1'],
            'faixas.*.preco_unitario' => ['required', 'numeric', 'min:0'],
            'faixas.*.custo_unitario' => ['nullable', 'numeric', 'min:0'],

            // Grupos de Variação e Opções (Evolução Técnica)
            'grupos_variacao'    => ['nullable', 'array'],
            'grupos_variacao.*.nome_grupo'   => ['required', 'string', 'max:100'],
            'grupos_variacao.*.tipo_exibicao' => ['required', 'string', 'in:select,radio,color,file'],
            'grupos_variacao.*.obrigatorio'   => ['nullable', 'boolean'],
            'grupos_variacao.*.opcoes'        => ['required', 'array', 'min:1'],
            'grupos_variacao.*.opcoes.*.nome_opcao'     => ['required', 'string', 'max:150'],
            'grupos_variacao.*.opcoes.*.acrescimo_preco' => ['required', 'numeric'],
            'grupos_variacao.*.opcoes.*.acrescimo_custo' => ['nullable', 'numeric'],
            'grupos_variacao.*.opcoes.*.acrescimo_prazo' => ['nullable', 'integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'exige_arte'     => $this->boolean('exige_arte'),
            'oferece_design' => $this->boolean('oferece_design'),
            'destaque'       => $this->boolean('destaque'),
            'ativo'          => $this->boolean('ativo', true),
            'preco_base'     => $this->input('preco_base') ? (float) $this->input('preco_base') : 0,
            // Cálculo automático de área se preenchido dimensões
            'area_m2'        => ($this->input('largura') && $this->input('altura')) 
                                ? (float) $this->input('largura') * (float) $this->input('altura') 
                                : null,
        ]);
    }
}

