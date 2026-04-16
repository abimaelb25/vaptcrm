<?php
$map = [
    'Dashboard' => ['DashboardController.php'],
    'Comercial' => ['ClienteController.php', 'ContatoController.php', 'PedidoController.php'],
    'Operacao'  => ['TarefaController.php', 'ProdutoController.php', 'FuncionarioController.php'],
    'CMS'       => ['BannerController.php', 'ConfiguracaoSiteController.php', 'DepoimentoController.php', 'PaginaLegalController.php'],
    'Conta'     => ['PerfilController.php'],
    'Catalogo'  => ['CatalogoController.php', 'CheckoutController.php', 'ConsultaPedidoController.php'],
    'Financeiro'=> ['StripeController.php']
];

$baseDir = __DIR__ . '/app/Http/Controllers/';

foreach($map as $folder => $files) {
    foreach($files as $file) {
        $path = $baseDir . $folder . '/' . $file;
        if(file_exists($path)) {
            $content = file_get_contents($path);
            $content = preg_replace('/namespace App\\\\Http\\\\Controllers\\\\(Admin|Publico|Pagamento);/', 'namespace App\Http\Controllers\\' . $folder . ';', $content);
            file_put_contents($path, $content);
        }
    }
}
echo "Namespaces atualizados nos controllers.\n";

$routesPath = __DIR__ . '/routes/web.php';
if(file_exists($routesPath)) {
    $content = file_get_contents($routesPath);
    $content = preg_replace('/use App\\\\Http\\\\Controllers\\\\Admin\\\\([^;]+);/', 'use App\Http\Controllers\MISSING_ADMIN_REPLACE\\\$1;', $content);
    $content = preg_replace('/use App\\\\Http\\\\Controllers\\\\Publico\\\\([^;]+);/', 'use App\Http\Controllers\Catalogo\\\$1;', $content);
    $content = preg_replace('/use App\\\\Http\\\\Controllers\\\\Pagamento\\\\([^;]+);/', 'use App\Http\Controllers\Financeiro\\\$1;', $content);
    
    // Tratando os arquivos vindos do Admin que espalhamos:
    $adminMap = [
        'DashboardController' => 'Dashboard',
        'ClienteController' => 'Comercial',
        'ContatoController' => 'Comercial',
        'PedidoController' => 'Comercial',
        'TarefaController' => 'Operacao',
        'ProdutoController' => 'Operacao',
        'FuncionarioController' => 'Operacao',
        'BannerController' => 'CMS',
        'ConfiguracaoSiteController' => 'CMS',
        'DepoimentoController' => 'CMS',
        'PaginaLegalController' => 'CMS',
        'PerfilController' => 'Conta'
    ];
    
    foreach($adminMap as $controller => $folder) {
        $content = str_replace("use App\Http\Controllers\MISSING_ADMIN_REPLACE\\$controller;", "use App\Http\Controllers\\$folder\\$controller;", $content);
    }
    
    file_put_contents($routesPath, $content);
    echo "Rotas atualizadas!\n";
}
