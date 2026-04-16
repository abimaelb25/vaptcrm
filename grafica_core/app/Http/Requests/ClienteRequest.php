<?php

declare(strict_types=1);

namespace App\Http\Requests;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-06 00:00 -03:00
*/

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $dados = $this->all();
        array_walk_recursive($dados, function (&$valor) {
            if (is_string($valor)) {
                $valor = trim(strip_tags($valor));
            }
        });
        $this->merge($dados);
    }

    public function rules(): array
    {
        $clienteId = $this->route('cliente')?->id;

        return [
            'nome' => ['required', 'string', 'max:150'],
            'empresa' => ['nullable', 'string', 'max:150'],
            'tipo_pessoa' => ['required', 'string', 'in:F,J'],
            'cpf_cnpj' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('clientes', 'cpf_cnpj')->ignore($clienteId)
            ],
            'telefone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'data_nascimento' => ['nullable', 'date'],
            'observacoes' => ['nullable', 'string'],
            'origem_lead' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:40'],
            'avatar_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ];
    }
}
