<?php

namespace Database\Factories;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 10:35
| Descrição: Factory para EmployeeOccurrence (testes)
*/

use App\Models\Employee;
use App\Models\EmployeeOccurrence;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeOccurrence>
 */
class EmployeeOccurrenceFactory extends Factory
{
    protected $model = EmployeeOccurrence::class;

    public function definition(): array
    {
        $tipos = [
            EmployeeOccurrence::TIPO_ADVERTENCIA,
            EmployeeOccurrence::TIPO_SUSPENSAO,
            EmployeeOccurrence::TIPO_FALTA,
            EmployeeOccurrence::TIPO_ATESTADO,
            EmployeeOccurrence::TIPO_DESLIGAMENTO,
        ];
        $tipo = $this->faker->randomElement($tipos);

        $subtipos = [];
        if ($tipo === EmployeeOccurrence::TIPO_ADVERTENCIA) {
            $subtipos = ['verbal', 'escrita'];
        } elseif ($tipo === EmployeeOccurrence::TIPO_FALTA) {
            $subtipos = ['injustificada', 'justificada'];
        } elseif ($tipo === EmployeeOccurrence::TIPO_DESLIGAMENTO) {
            $subtipos = ['pedido_demissao', 'sem_justa_causa', 'justa_causa', 'termino_contrato'];
        }

        $subtipo = !empty($subtipos) ? $this->faker->randomElement($subtipos) : null;

        $dataOcorrencia = $this->faker->dateTimeBetween('-30 days', 'now');
        $dataInicio = $tipo === EmployeeOccurrence::TIPO_SUSPENSAO 
            ? clone $dataOcorrencia 
            : null;
        $dataFim = $dataInicio 
            ? $dataInicio->modify('+' . $this->faker->numberBetween(1, 10) . ' days')
            : null;

        return [
            'loja_id' => Employee::factory()->create()->loja_id,
            'employee_id' => Employee::factory(),
            'tipo' => $tipo,
            'subtipo' => $subtipo,
            'titulo' => $this->faker->sentence(5),
            'descricao' => $this->faker->paragraph(),
            'data_ocorrencia' => $dataOcorrencia,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'status' => $this->faker->randomElement([
                'registrada',
                'em_analise',
                'resolvida',
                'contestada',
                'arquivada',
            ]),
            'referencia_documento' => $this->faker->optional()->bothify('REF-######'),
            'metadados' => null,
            'created_by' => Usuario::factory(),
            'updated_by' => null,
        ];
    }
}
