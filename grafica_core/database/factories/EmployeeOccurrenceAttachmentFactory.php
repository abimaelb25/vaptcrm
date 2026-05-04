<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeOccurrence;
use App\Models\EmployeeOccurrenceAttachment;
use App\Models\Loja;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeOccurrenceAttachment>
 */
class EmployeeOccurrenceAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'loja_id' => Loja::factory(),
            'employee_occurrence_id' => EmployeeOccurrence::factory(),
            'titulo' => $this->faker->word() . ' - ' . $this->faker->fileExtension(),
            'arquivo_path' => 'ocorrencias/' . $this->faker->numberBetween(1, 100) . '/' . $this->faker->numberBetween(1, 1000) . '/' . Str::random(12) . '.pdf',
            'mime_type' => 'application/pdf',
            'tamanho_bytes' => $this->faker->numberBetween(10000, 5242880), // 10KB até 5MB
            'tipo_comprovacao' => $this->faker->randomElement(['atestado_medico', 'comprovante_justificativa', 'documento_legal']),
            'descricao' => $this->faker->sentence(),
            'uploaded_by' => Usuario::factory(),
        ];
    }
}
