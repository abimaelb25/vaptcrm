<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimulatePricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // A política de produto (via policy/controller) já cuidará se o usuário pode acessar a rota em si,
        // mas aqui nós podemos retornar true, pois as validações vão garantir que os IDs cruzados (tenant) falhem.
        return true;
    }

    public function rules(): array
    {
        $lojaId = auth()->user()->loja_id;

        return [
            'insumos' => ['nullable', 'array'],
            'insumos.*.insumo_id' => [
                'required',
                'integer',
                // Evita cruzar dados de tenant silenciosamente: bloqueia na validação com erro!
                Rule::exists('insumos', 'id')->where(function ($query) use ($lojaId) {
                    $query->where('loja_id', $lojaId);
                }),
            ],
            'insumos.*.quantidade' => ['required', 'numeric', 'min:0'],
            'insumos.*.fator_perda' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'servicos' => ['nullable', 'array'],
            'servicos.*.servico_producao_id' => [
                'required',
                'integer',
                Rule::exists('servicos_producao', 'id')->where(function ($query) use ($lojaId) {
                    $query->where('loja_id', $lojaId);
                }),
            ],
            'servicos.*.quantidade' => ['required', 'numeric', 'min:0'],
            'servicos.*.fator_aplicacao' => ['nullable', 'numeric', 'min:0.01'],

            'tempo_producao_min' => ['nullable', 'integer', 'min:0'],
            'quantidade_base' => ['nullable', 'integer', 'min:1'],
            'perda_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'margem_lucro' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'insumos.*.insumo_id.exists' => 'Um dos insumos fornecidos não foi encontrado ou não pertence à sua loja.',
            'servicos.*.servico_producao_id.exists' => 'Um dos serviços de produção fornecidos não foi encontrado ou não pertence à sua loja.',
        ];
    }
}
