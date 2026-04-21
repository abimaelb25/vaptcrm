<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Loja;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loja>
 */
class LojaFactory extends Factory
{
    protected $model = Loja::class;

    public function definition(): array
    {
        $nome = $this->faker->company();

        return [
            'nome_fantasia'      => $nome,
            'slug'               => \Illuminate\Support\Str::slug($nome) . '-' . $this->faker->unique()->numerify('####'),
            'responsavel_nome'   => $this->faker->name(),
            'responsavel_email'  => $this->faker->unique()->safeEmail(),
            'status'             => 'ativa',
            'storage_limit_mb'   => 1024,
            'storage_used_bytes' => 0,
        ];
    }

    /** Estado: loja bloqueada (usada em testes de expiração de plano) */
    public function bloqueada(): static
    {
        return $this->state(fn () => [
            'status'        => 'bloqueada',
            'bloqueada_em'  => now(),
            'motivo_bloqueio' => 'Inadimplência de teste',
        ]);
    }
}
