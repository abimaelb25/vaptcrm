<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Loja;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            // loja_id explícito obrigatório — nunca usar auto-fill silencioso em testes
            'loja_id'  => null,
            'nome'     => $this->faker->name(),
            'email'    => $this->faker->unique()->safeEmail(),
            'telefone' => $this->faker->numerify('(##) #####-####'),
            'status'   => 'ativo',
        ];
    }

    /** Associa o cliente a uma loja existente */
    public function paraLoja(Loja $loja): static
    {
        return $this->state(fn () => ['loja_id' => $loja->id]);
    }
}
