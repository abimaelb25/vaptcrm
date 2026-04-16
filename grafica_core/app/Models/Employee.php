<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:00
*/

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTenancy;

class Employee extends Model
{
    use SoftDeletes, HasTenancy;

    protected $table = 'employees';

    protected $fillable = [
        'loja_id',
        'user_id',
        'nome_completo',
        'nome_social',
        'cpf',
        'rg',
        'orgao_emissor',
        'data_nascimento',
        'sexo',
        'estado_civil',
        'nacionalidade',
        'naturalidade',
        'telefone',
        'whatsapp',
        'email_pessoal',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'matricula',
        'cargo_formal',
        'cargo_interno',
        'setor',
        'tipo_vinculo',
        'data_admissao',
        'data_desligamento',
        'status_funcional',
        'jornada_tipo',
        'carga_horaria_semanal',
        'salario_base',
        'comissao_percentual',
        'observacoes_gerais',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_admissao' => 'date',
        'data_desligamento' => 'date',
        'salario_base' => 'decimal:2',
        'comissao_percentual' => 'decimal:2',
        'carga_horaria_semanal' => 'integer',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function ferias(): HasMany
    {
        return $this->hasMany(EmployeeVacation::class);
    }

    public function registrosSaude(): HasMany
    {
        return $this->hasMany(EmployeeHealthRecord::class);
    }

    public function historico(): HasMany
    {
        return $this->hasMany(EmployeeHistory::class);
    }
}
