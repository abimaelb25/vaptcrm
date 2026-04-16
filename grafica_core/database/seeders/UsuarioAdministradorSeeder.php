<?php

declare(strict_types=1);

namespace Database\Seeders;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 20:00 -03:00
*/

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioAdministradorSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::query()->updateOrCreate(
            ['email' => 'admin@grafica.local'],
            [
                'nome' => 'Administrador Inicial',
                'senha' => Hash::make('Admin@123'),
                'perfil' => 'administrador',
                'ativo' => true,
            ]
        );
    }
}
