<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmNfeImportacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $lojaId = (int) (auth()->user()->loja_id ?? 0);

        return [
            'valor_total_nota' => ['nullable', 'numeric', 'min:0'],
            'confirmar_custo_nao_alocado' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            'items.*.acao' => ['required', Rule::in(['criar', 'vincular', 'ignorar'])],
            'items.*.tipo_item_operacional' => ['required', Rule::in(['consumivel', 'embalagem', 'componente', 'apoio', 'ignorado'])],
            'items.*.tratamento_financeiro' => ['required', Rule::in(['custo_proprio', 'ratear_consumiveis', 'custo_agregado', 'desconsiderar'])],
            'items.*.valor_financeiro_alocado' => ['nullable', 'numeric', 'min:0'],
            'items.*.confirmacao_desconsideracao' => ['nullable', 'boolean'],
            'items.*.insumo_id' => [
                'nullable',
                Rule::exists('insumos', 'id')->where(fn ($q) => $q->where('loja_id', $lojaId)),
            ],
            'items.*.novo_nome' => ['nullable', 'string', 'max:255'],
            'items.*.unidade_medida' => ['nullable', 'string', 'max:20'],
            'items.*.unidade_compra' => ['nullable', 'string', 'max:30'],
            'items.*.quantidade_por_compra' => ['nullable', 'numeric', 'min:0.0001'],
            'items.*.controlar_estoque' => ['nullable', 'boolean'],
            'items.*.usar_na_precificacao' => ['nullable', 'boolean'],
            'items.*.categoria' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $items = $this->input('items', []);

            foreach ($items as $idx => $item) {
                $acao = $item['acao'] ?? null;
                $tipo = $item['tipo_item_operacional'] ?? null;
                $tratamento = $item['tratamento_financeiro'] ?? null;
                $valorAlocado = (float) ($item['valor_financeiro_alocado'] ?? 0);

                if ($acao === 'criar' && empty($item['novo_nome'])) {
                    $validator->errors()->add("items.{$idx}.novo_nome", 'Informe o nome do novo insumo para os itens marcados como criar.');
                }

                if ($acao === 'criar' && empty(trim((string) ($item['unidade_medida'] ?? '')))) {
                    $validator->errors()->add("items.{$idx}.unidade_medida", 'Informe explicitamente a unidade de estoque/consumo do novo insumo. Nao use a sigla fiscal da NF-e sem validar.');
                }

                if ($acao === 'vincular' && empty($item['insumo_id'])) {
                    $validator->errors()->add("items.{$idx}.insumo_id", 'Selecione o insumo existente para os itens marcados como vincular.');
                }

                if ($acao === 'criar' && !empty($item['unidade_compra']) && empty($item['quantidade_por_compra'])) {
                    $validator->errors()->add("items.{$idx}.quantidade_por_compra", 'Informe a quantidade por compra quando a unidade de compra for preenchida.');
                }

                if ($tratamento === 'desconsiderar' && !$this->boolean("items.{$idx}.confirmacao_desconsideracao")) {
                    $validator->errors()->add("items.{$idx}.confirmacao_desconsideracao", 'Confirme explicitamente quando o valor do item for desconsiderado no custo.');
                }

                if ($tipo === 'ignorado' && $acao !== 'ignorar') {
                    $validator->errors()->add("items.{$idx}.acao", 'Quando o tipo for ignorado, a ação deve ser ignorar.');
                }

                if ($valorAlocado < 0) {
                    $validator->errors()->add("items.{$idx}.valor_financeiro_alocado", 'O valor financeiro alocado deve ser maior ou igual a zero.');
                }
            }

            $valorTotalNota = (float) $this->input('valor_total_nota', 0);
            $valorAlocadoNoCusto = 0.0;

            foreach ($items as $item) {
                $tratamento = (string) ($item['tratamento_financeiro'] ?? 'custo_proprio');
                $valorItem = (float) ($item['valor_financeiro_alocado'] ?? 0);

                if ($tratamento !== 'desconsiderar') {
                    $valorAlocadoNoCusto += $valorItem;
                }
            }

            $diferenca = round($valorTotalNota - $valorAlocadoNoCusto, 2);

            if (abs($diferenca) > 0.01 && !$this->boolean('confirmar_custo_nao_alocado')) {
                $validator->errors()->add(
                    'confirmar_custo_nao_alocado',
                    'Existe custo da nota sem alocação completa. Revise o resumo financeiro e confirme explicitamente para continuar.'
                );
            }
        });
    }
}
