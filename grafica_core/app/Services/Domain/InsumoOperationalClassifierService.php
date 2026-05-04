<?php

declare(strict_types=1);

namespace App\Services\Domain;

class InsumoOperationalClassifierService
{
    public const TIPOS = ['consumivel', 'embalagem', 'componente', 'apoio', 'ignorado'];

    public const TRATAMENTOS_FINANCEIROS = [
        'custo_proprio',
        'ratear_consumiveis',
        'custo_agregado',
        'desconsiderar',
    ];

    public function normalizarCamposInsumo(array $data): array
    {
        $tipo = (string) ($data['tipo_item_operacional'] ?? 'consumivel');

        if (!in_array($tipo, self::TIPOS, true)) {
            $tipo = 'consumivel';
        }

        $defaults = $this->defaultsPorTipo($tipo);

        $data['tipo_item_operacional'] = $tipo;
        $data['controlar_estoque'] = array_key_exists('controlar_estoque', $data)
            ? (bool) $data['controlar_estoque']
            : $defaults['controlar_estoque'];
        $data['usar_na_precificacao'] = array_key_exists('usar_na_precificacao', $data)
            ? (bool) $data['usar_na_precificacao']
            : $defaults['usar_na_precificacao'];

        return $data;
    }

    public function sugestaoTipoPorDescricao(string $descricao): string
    {
        $texto = mb_strtolower(trim($descricao));

        if ($texto === '') {
            return 'consumivel';
        }

        $embalagem = ['frasco', 'tampa', 'rotulo', 'rótulo', 'caixa', 'sacola', 'etiqueta', 'blister'];
        $consumivel = ['dye', 'tinta', 'cola', 'papel', 'vinil', 'lona', 'solvente', 'verniz'];
        $componente = ['bico', 'valvula', 'válvula', 'conector', 'tubo', 'refil'];
        $apoio = ['pano', 'luva', 'fita', 'limpeza', 'álcool', 'alcool'];

        if ($this->contemPalavra($texto, $embalagem)) {
            return 'embalagem';
        }

        if ($this->contemPalavra($texto, $componente)) {
            return 'componente';
        }

        if ($this->contemPalavra($texto, $apoio)) {
            return 'apoio';
        }

        if ($this->contemPalavra($texto, $consumivel)) {
            return 'consumivel';
        }

        return 'consumivel';
    }

    public function defaultsPorTipo(string $tipo): array
    {
        return match ($tipo) {
            'consumivel' => ['controlar_estoque' => true, 'usar_na_precificacao' => true],
            'embalagem' => ['controlar_estoque' => true, 'usar_na_precificacao' => false],
            'componente' => ['controlar_estoque' => true, 'usar_na_precificacao' => false],
            'apoio' => ['controlar_estoque' => false, 'usar_na_precificacao' => false],
            'ignorado' => ['controlar_estoque' => false, 'usar_na_precificacao' => false],
            default => ['controlar_estoque' => true, 'usar_na_precificacao' => true],
        };
    }

    private function contemPalavra(string $texto, array $palavras): bool
    {
        foreach ($palavras as $palavra) {
            if (str_contains($texto, $palavra)) {
                return true;
            }
        }

        return false;
    }
}
