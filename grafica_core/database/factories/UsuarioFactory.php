<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Loja;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            // loja_id deve ser fornecido pelo teste via ->for(Loja::factory())
            // ou ->create(['loja_id' => $loja->id]).
            // Deixamos null aqui para forçar declaração explícita.
            'loja_id' => null,
            'nome'    => $this->faker->name(),
            'email'   => $this->faker->unique()->safeEmail(),
            'senha'   => Hash::make('secret'),
            'perfil'  => 'administrador',
            'ativo'   => true,
        ];
    }

    /** Administrador da loja */
    public function administrador(): static
    {
        return $this->state(fn () => ['perfil' => 'administrador']);
    }

    /** Operador de produção */
    public function producao(): static
    {
        return $this->state(fn () => ['perfil' => 'producao']);
    }

    /** Associa o usuário a uma loja existente */
    public function paraLoja(Loja $loja): static
    {
        return $this->state(fn () => ['loja_id' => $loja->id]);
    }
}
