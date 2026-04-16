<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Produto;

$produtos = Produto::whereNotNull('imagem_principal')->get();
if ($produtos->isEmpty()) {
    echo "Nenhum produto com imagem encontrado no banco.\n";
} else {
    foreach ($produtos as $p) {
        echo "Produto: {$p->nome} | Imagem: {$p->imagem_principal}\n";
    }
}
