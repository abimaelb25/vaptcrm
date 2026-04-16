<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\System;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use App\Http\Controllers\Controller;
use App\Models\SiteConfiguracao;
use App\Services\SaaS\DataMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ConfigController extends Controller
{
    public function __construct(
        protected DataMaintenanceService $maintenanceService
    ) {}

    public function index(): View
    {
        $configs = SiteConfiguracao::all()->pluck('valor', 'chave')->toArray();
        return view('painel.configuracoes.index', compact('configs'));
    }

    public function update(Request $request)
    {
        $dados = $request->except(['_token']);

        foreach ($dados as $chave => $valor) {
            SiteConfiguracao::updateOrCreate(
                ['chave' => $chave],
                ['valor' => $valor ?? '', 'tipo' => 'texto']
            );
        }

        Cache::forget('site_configuracoes_todas');

        return back()->with('sucesso', 'Configurações atualizadas com sucesso.');
    }

    public function export()
    {
        $dados = $this->maintenanceService->export();
        $filename = 'backup_catalogo_' . date('Y-m-d_H-i') . '.json';

        return response()->streamDownload(function () use ($dados) {
            echo json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function import(Request $request)
    {
        $request->validate(['arquivo_json' => ['required', 'file', 'mimes:json,txt']]);

        $content = file_get_contents($request->file('arquivo_json')->getRealPath());
        $jsonData = json_decode($content, true);

        if (!$jsonData || !isset($jsonData['dados'])) {
            return back()->with('erro', 'Arquivo JSON inválido.');
        }

        $result = $this->maintenanceService->import($jsonData);

        if (!empty($result['erros'])) {
            return back()->with('erro', 'Falha na importação: ' . implode(', ', $result['erros']));
        }

        return back()->with('sucesso', "Importação concluída: {$result['categorias']} cat, {$result['produtos']} prod e {$result['clientes']} clie.");
    }
}
