<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Usuario;
use App\Models\Employee;

try {
    $u = Usuario::find(9);
    if (!$u) {
        echo "User 9 not found\n";
        exit;
    }
    echo "Creating employee for user 9 (LID: {$u->loja_id})...\n";
    $e = Employee::create([
        'loja_id' => $u->loja_id,
        'user_id' => $u->id,
        'nome_completo' => $u->nome,
        'email_pessoal' => $u->email,
        'cargo_interno' => $u->cargo ?? $u->perfil,
        'status_funcional' => 'ativo'
    ]);
    echo "Success! ID: {$e->id}\n";
} catch (\Exception $ex) {
    echo "Error: " . $ex->getMessage() . "\n";
    echo $ex->getTraceAsString() . "\n";
}
