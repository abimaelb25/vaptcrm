<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\System;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:15
*/

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeHealthRecord;
use App\Models\EmployeeHistory;
use App\Models\EmployeeVacation;
use App\Models\Tarefa;
use App\Models\Usuario;
use App\Services\Core\MediaService;
use App\Services\SaaS\SaaSService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(
        protected SaaSService $saasService,
        protected MediaService $mediaService
    ) {}

    public function index(Request $request): View
    {
        $query = Employee::with('usuario');

        if ($request->filled('busca')) {
            $query->where(function ($q) use ($request) {
                $q->where('nome_completo', 'like', '%' . $request->busca . '%')
                  ->orWhere('email_pessoal', 'like', '%' . $request->busca . '%')
                  ->orWhere('cargo_formal', 'like', '%' . $request->busca . '%')
                  ->orWhereHas('usuario', function($qu) use ($request) {
                      $qu->where('nome', 'like', '%' . $request->busca . '%')
                         ->orWhere('email', 'like', '%' . $request->busca . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status_funcional', $request->status);
        }

        return view('painel.funcionarios.index', [
            'funcionarios' => $query->latest()->paginate(20),
            'busca' => $request->busca,
        ]);
    }

    public function show(Employee $equipe): View
    {
        $equipe->load(['usuario', 'documentos', 'ferias', 'registrosSaude', 'historico.autor']);
        
        $statusList = ['backlog', 'a_fazer', 'em_andamento', 'bloqueada', 'concluida', 'cancelada'];
        $tarefas = collect();
        
        if ($equipe->user_id) {
            foreach ($statusList as $s) {
                $tarefas[$s] = Tarefa::where('responsavel_id', $equipe->user_id)->where('status', $s)->latest()->get();
            }
        }

        return view('painel.funcionarios.show', [
            'funcionario' => $equipe,
            'tarefas' => $tarefas
        ]);
    }

    public function create(): View
    {
        return view('painel.funcionarios.form', [
            'funcionario' => new Employee(),
            'usuario' => new Usuario(),
        ]);
    }

    public function edit(Employee $equipe): View
    {
        $equipe->load('usuario');
        return view('painel.funcionarios.form', [
            'funcionario' => $equipe,
            'usuario' => $equipe->usuario ?? new Usuario(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validData = $request->validate([
            // Dados Employee
            'nome_completo' => ['required', 'string', 'max:255'],
            'email_pessoal' => ['nullable', 'email'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'cargo_interno' => ['nullable', 'string'],
            'status_funcional' => ['required', 'string'],
            
            // Dados Acesso (Opcional)
            'criar_acesso' => ['nullable', 'boolean'],
            'nome_acesso' => ['required_if:criar_acesso,1', 'nullable', 'string', 'max:150'],
            'email_acesso' => ['required_if:criar_acesso,1', 'nullable', 'email', 'unique:usuarios,email'],
            'senha_acesso' => ['required_if:criar_acesso,1', 'nullable', 'string', 'min:6'],
            'perfil' => ['required_if:criar_acesso,1', 'nullable', 'in:administrador,gerente,atendente,producao,financeiro'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->criar_acesso && !$this->saasService->podeAdicionar('funcionario')) {
            return back()->with('erro', 'Limite de acessos do plano atingido.');
        }

        try {
            DB::beginTransaction();

            $user_id = null;
            if ($request->criar_acesso) {
                $avatarPath = null;
                if ($request->hasFile('avatar')) {
                    $avatarPath = $this->mediaService->saveWithSquareCrop($request->file('avatar'), 'avatars', 'emp');
                }

                $usuario = Usuario::create([
                    'nome' => $request->nome_acesso,
                    'email' => $request->email_acesso,
                    'senha' => Hash::make($request->senha_acesso),
                    'perfil' => $request->perfil,
                    'cargo' => $request->cargo_interno,
                    'avatar' => $avatarPath,
                    'ativo' => $request->status_funcional === 'ativo',
                    'permissoes' => $request->permissoes ?? [],
                ]);
                $user_id = $usuario->id;
            }

            $employee = Employee::create([
                'user_id' => $user_id,
                'nome_completo' => $request->nome_completo,
                'email_pessoal' => $request->email_pessoal,
                'cpf' => $request->cpf,
                'cargo_interno' => $request->cargo_interno,
                'status_funcional' => $request->status_funcional,
                // Outros campos serão preenchidos via edit profissional
            ]);

            // Registro inicial de histórico
            $employee->historico()->create([
                'loja_id' => $employee->loja_id,
                'tipo_evento' => 'admissao',
                'titulo' => 'Admissão / Cadastro Inicial',
                'data_evento' => now(),
                'criado_por' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('admin.system.equipe.index')->with('sucesso', 'Funcionário cadastrado com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('erro', 'Erro ao cadastrar: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, Employee $equipe): RedirectResponse
    {
        $validData = $request->validate([
            'nome_completo' => ['required', 'string', 'max:255'],
            'email_pessoal' => ['nullable', 'email'],
            'status_funcional' => ['required', 'string'],
            // RH Dados
            'cpf' => ['nullable', 'string'],
            'rg' => ['nullable', 'string'],
            'data_nascimento' => ['nullable', 'date'],
            'telefone' => ['nullable', 'string'],
            'whatsapp' => ['nullable', 'string'],
            'cargo_formal' => ['nullable', 'string'],
            'cargo_interno' => ['nullable', 'string'],
            'setor' => ['nullable', 'string'],
            'tipo_vinculo' => ['nullable', 'string'],
            'data_admissao' => ['nullable', 'date'],
            'salario_base' => ['nullable', 'numeric'],
            
            // Acesso Dados
            'email_acesso' => ['nullable', 'email', 'unique:usuarios,email,' . ($equipe->user_id ?? 0)],
            'senha_acesso' => ['nullable', 'string', 'min:6'],
        ]);

        try {
            DB::beginTransaction();

            $oldCargo = $equipe->cargo_interno;
            $oldSalario = (float) $equipe->salario_base;

            // Atualiza RH
            $equipe->update($request->except(['email_acesso', 'senha_acesso', 'perfil', 'permissoes', 'avatar']));

            // Registra Histórico (Mudanças críticas)
            if ($oldCargo !== $equipe->cargo_interno) {
                $equipe->historico()->create([
                    'loja_id' => $equipe->loja_id,
                    'tipo_evento' => 'mudanca_cargo',
                    'titulo' => 'Alteração de Cargo',
                    'descricao' => "Cargo alterado de [{$oldCargo}] para [{$equipe->cargo_interno}]",
                    'data_evento' => now(),
                    'criado_por' => auth()->id(),
                ]);
            }

            if ($oldSalario !== (float) $equipe->salario_base) {
                $equipe->historico()->create([
                    'loja_id' => $equipe->loja_id,
                    'tipo_evento' => 'aumento',
                    'titulo' => 'Alteração Salarial',
                    'descricao' => "Salário alterado de R$ " . number_format($oldSalario, 2) . " para R$ " . number_format((float)$equipe->salario_base, 2),
                    'data_evento' => now(),
                    'criado_por' => auth()->id(),
                ]);
            }

            // Atualiza Acesso se existir
            if ($equipe->user_id) {
                $user = $equipe->usuario;
                $userData = [
                    'nome' => $request->nome_completo,
                    'email' => $request->email_acesso ?? $user->email,
                    'ativo' => $request->status_funcional === 'ativo',
                ];

                if ($request->filled('senha_acesso')) {
                    $userData['senha'] = Hash::make($request->senha_acesso);
                }
                
                if ($request->filled('perfil')) {
                    $userData['perfil'] = $request->perfil;
                }

                if ($request->hasFile('avatar')) {
                    if ($user->avatar) {
                        $this->mediaService->delete($user->avatar);
                    }
                    $userData['avatar'] = $this->mediaService->saveWithSquareCrop($request->file('avatar'), 'avatars', 'emp');
                }

                $user->update($userData);
            }

            DB::commit();
            return back()->with('sucesso', 'Dados atualizados com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('erro', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Employee $equipe): RedirectResponse
    {
        if ($equipe->user_id === auth()->id()) {
            return back()->with('erro', 'Você não pode remover seu próprio perfil.');
        }

        try {
            DB::beginTransaction();
            
            // Se tiver usuário, inativa
            if ($equipe->user_id) {
                $equipe->usuario->update(['ativo' => false]);
            }

            $equipe->update(['status_funcional' => 'desligado']);
            $equipe->delete();

            DB::commit();
            return back()->with('sucesso', 'Funcionário removido do quadro ativo.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('erro', 'Erro ao remover.');
        }
    }
}
