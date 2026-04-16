<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Usuario;

$usuarios = Usuario::all();
foreach ($usuarios as $u) {
    echo "ID: {$u->id} | Nome: {$u->nome} | LID: " . ($u->loja_id ?? 'NULL') . " | HasEmp: " . ($u->funcionario ? 'YES' : 'NO') . "\n";
}
