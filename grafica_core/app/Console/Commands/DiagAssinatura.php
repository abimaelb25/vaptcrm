<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagAssinatura extends Command
{
    protected $signature = 'diag:assinatura';
    protected $description = 'Diagnóstico de assinatura';

    public function handle()
    {
        $this->info('=== DIAGNÓSTICO DE ASSINATURA ===');
        $this->newLine();

        $ass = DB::table('saas_assinaturas')->first();
        
        if (!$ass) {
            $this->error('SEM ASSINATURA CADASTRADA!');
            return 1;
        }

        $this->line("Assinatura ID: {$ass->id}");
        $this->line("Loja ID: {$ass->loja_id}");
        $this->line("Status: {$ass->status}");
        $this->line("trial_ends_at: " . ($ass->trial_ends_at ?: 'null'));
        $this->line("ends_at: " . ($ass->ends_at ?: 'null'));
        $this->line("NOW: " . now()->toDateTimeString());

        $this->newLine();
        $loja = DB::table('lojas')->where('id', $ass->loja_id)->first();
        if ($loja) {
            $this->info('=== LOJA ===');
            $this->line("Nome: {$loja->nome_fantasia}");
            $this->line("bloqueada_em: " . ($loja->bloqueada_em ?: 'NÃO BLOQUEADA'));
        }

        $this->newLine();
        $model = \App\Models\SaaS\Assinatura::find($ass->id);
        $this->info('=== MODELO ===');
        $this->line("ativa(): " . ($model->ativa() ? 'SIM' : 'NÃO'));
        $this->line("expirada(): " . ($model->expirada() ? 'SIM' : 'NÃO'));
        $this->line("emTrial(): " . ($model->emTrial() ? 'SIM' : 'NÃO'));

        // Verificar condições de trial
        $this->newLine();
        $this->info('=== ANÁLISE TRIAL ===');
        $this->line("status === 'trial': " . ($ass->status === 'trial' ? 'SIM' : 'NÃO'));
        if ($ass->trial_ends_at) {
            $trialEnds = \Carbon\Carbon::parse($ass->trial_ends_at);
            $this->line("trial_ends_at > now(): " . ($trialEnds->gt(now()) ? 'SIM (VÁLIDO)' : 'NÃO (EXPIRADO)'));
            $this->line("Diferença: " . now()->diffForHumans($trialEnds, true) . " " . ($trialEnds->gt(now()) ? 'restantes' : 'atrás'));
        }

        return 0;
    }
}
