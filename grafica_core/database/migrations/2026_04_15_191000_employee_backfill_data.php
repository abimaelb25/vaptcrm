<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Usuario;
use App\Models\Employee;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:10
*/

return new class extends Migration
{
    public function up(): void
    {
        $usuarios = Usuario::withoutGlobalScope('loja')->whereNotNull('loja_id')->get();
        
        foreach ($usuarios as $user) {
            $existe = Employee::withoutGlobalScope('loja')->where('user_id', $user->id)->first();
            if ($existe) continue;

            Employee::create([
                'loja_id' => $user->loja_id,
                'user_id' => $user->id,
                'nome_completo' => $user->nome,
                'cargo_interno' => $user->cargo,
                'status_funcional' => $user->ativo ? 'ativo' : 'inativo',
                'data_admissao' => $user->created_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
            ]);
        }
    }

    public function down(): void
    {
        // Opcional: remover os funcionários criados automaticamente se necessário
        // Porém, manter dados é mais seguro em caso de rollback de estrutura mas não de dados.
    }
};
