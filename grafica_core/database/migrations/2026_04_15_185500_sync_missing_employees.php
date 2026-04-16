<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Usuario;
use App\Models\Employee;

return new class extends Migration
{
    /**
     * Sincroniza usuários que ficaram sem registro de Employee.
     * Autoria: Abimael Borges
     */
    public function up(): void
    {
        $usuarios = Usuario::whereNotNull('loja_id')
            ->whereDoesntHave('funcionario')
            ->get();

        foreach ($usuarios as $user) {
            if ($user->funcionario) continue;
            
            Employee::create([
                'loja_id' => $user->loja_id,
                'user_id' => $user->id,
                'nome_completo' => $user->nome,
                'email_pessoal' => $user->email,
                'cargo_interno' => $user->cargo ?? $user->perfil,
                'status_funcional' => $user->ativo ? 'ativo' : 'inativo',
            ]);
        }
    }

    public function down(): void
    {
        // Não reversível categoricamente
    }
};
