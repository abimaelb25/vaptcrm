<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UploadNfeXmlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'xml_file' => ['required', 'file', 'max:2048', 'mimes:xml'],
        ];
    }

    public function messages(): array
    {
        return [
            'xml_file.required' => 'Selecione um arquivo XML da nota fiscal.',
            'xml_file.file' => 'O arquivo enviado é inválido.',
            'xml_file.max' => 'O XML deve ter no máximo 2MB.',
            'xml_file.mimes' => 'Envie um arquivo com extensão .xml.',
        ];
    }
}
