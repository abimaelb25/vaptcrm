// Diagnóstico simples de assinatura - para rodar via: php artisan tinker < scratch/diag.php

$ass = DB::table('saas_assinaturas')->first();
echo "=== ASSINATURA ===\n";
echo "ID: " . ($ass->id ?? 'N/A') . "\n";
echo "Loja ID: " . ($ass->loja_id ?? 'N/A') . "\n";
echo "Status: " . ($ass->status ?? 'N/A') . "\n";
echo "trial_ends_at: " . ($ass->trial_ends_at ?? 'null') . "\n";
echo "ends_at: " . ($ass->ends_at ?? 'null') . "\n";
echo "NOW: " . now()->toDateTimeString() . "\n\n";

if ($ass) {
    $loja = DB::table('lojas')->where('id', $ass->loja_id)->first();
    echo "=== LOJA ===\n";
    echo "Nome: " . ($loja->nome_fantasia ?? 'N/A') . "\n";
    echo "bloqueada_em: " . ($loja->bloqueada_em ?? 'NÃO BLOQUEADA') . "\n";
    
    // Testar o modelo
    $model = \App\Models\SaaS\Assinatura::find($ass->id);
    echo "\n=== MODELO ===\n";
    echo "ativa(): " . ($model->ativa() ? 'SIM' : 'NÃO') . "\n";
    echo "expirada(): " . ($model->expirada() ? 'SIM' : 'NÃO') . "\n";
    echo "emTrial(): " . ($model->emTrial() ? 'SIM' : 'NÃO') . "\n";
}
