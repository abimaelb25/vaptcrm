<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'tipo_entrega' => ['required', 'string', 'in:retirada,entrega_local,entrega_agendada'],
            'valor_frete' => ['nullable', 'numeric', 'min:0'],
            'taxas_adicionais' => ['nullable', 'numeric', 'min:0'],
            'desconto' => ['nullable', 'numeric', 'min:0'],
            'prazo_entrega' => ['nullable', 'date'],
            'observacoes' => ['nullable', 'string'],
            'cupom_codigo' => ['nullable', 'string'],
            
            // Itens
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.produto_id' => ['required', 'integer', 'exists:produtos,id'],
            'itens.*.quantidade' => ['required', 'integer', 'min:1'],
            'itens.*.valor_unitario' => ['required', 'numeric', 'min:0'],
            'itens.*.especificacoes' => ['nullable', 'string'],
        ];
    }
}
