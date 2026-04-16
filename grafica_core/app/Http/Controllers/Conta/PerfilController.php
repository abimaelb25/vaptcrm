<?php

declare(strict_types=1);

namespace App\Http\Controllers\Conta;

use App\Http\Controllers\Controller;
use App\Models\DocumentoUsuario;
use App\Models\SolicitacaoAtualizacao;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    /**
     * Atualização livre da Senha e Avatar (Acesso Imediato)
     */
    public function atualizarSenhaAvatar(Request $request)
    {
        /** @var Usuario $user */
        $user = auth()->user();

        $regras = [];
        if ($request->filled('nova_senha')) {
            $regras['nova_senha'] = ['required', 'string', 'min:6'];
        }
        if ($request->hasFile('avatar')) {
            $regras['avatar'] = ['required', 'image', 'max:2048'];
        }

        $request->validate($regras);

        if ($request->filled('nova_senha')) {
            $user->senha = Hash::make($request->nova_senha);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $caminho = $request->file('avatar')->store('avatares', 'public');
            $user->avatar = $caminho;
        }

        $user->save();

        return back()->with('sucesso', 'Suas credenciais/foto foram atualizadas com sucesso.');
    }

    /**
     * Upload Seguro de Documentos Pessoais (.PDF)
     */
    public function uploadDocumento(Request $request)
    {
        $request->validate([
            'tipo_documento' => ['required', 'string', 'in:rg,cpf,certidao_nascimento,certidao_casamento,outro'],
            'arquivo_pdf' => ['required', 'file', 'mimes:pdf', 'max:5120'], // Máximo 5MB
        ]);

        /** @var Usuario $user */
        $user = auth()->user();
        $file = $request->file('arquivo_pdf');

        // Storage::put() sem usar o disco 'public' para impedir exposição via link
        $caminho = $file->store('documentos_privados/' . $user->id);

        $user->documentos()->create([
            'tipo_documento' => $request->tipo_documento,
            'caminho_arquivo' => $caminho,
            'nome_original' => $file->getClientOriginalName(),
        ]);

        return back()->with('sucesso', 'Documento anexado e trancado no cofre com sucesso.');
    }

    /**
     * View de Arquivos Privados através de Rota Controlada
     */
    public function visualizarDocumento(DocumentoUsuario $documento)
    {
        // Regra de Segurança: Só o dono do documento ou um admin podem visualizar
        $user = auth()->user();
        if ($documento->usuario_id !== $user->id && !in_array($user->perfil, ['administrador', 'gerente'], true)) {
            abort(403, 'Acesso Privado.');
        }

        if (!Storage::exists($documento->caminho_arquivo)) {
            abort(404, 'Arquivo físico não encontrado no cofre.');
        }

        return Storage::download($documento->caminho_arquivo, $documento->nome_original);
    }

    /**
     * Solicitar Alteração de Cadastro (Nome, E-mail) que dependem de aprovação Admin
     */
    public function solicitarAtualizacao(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
        ]);

        /** @var Usuario $user */
        $user = auth()->user();

        // Se os dados forem iguais aos atuais, avisa.
        if ($user->nome === $dados['nome'] && $user->email === $dados['email']) {
            return back()->with('aviso', 'Os dados solicitados são idênticos aos atuais.');
        }

        $user->solicitacoes()->create([
            'dados_antigos' => [
                'nome' => $user->nome,
                'email' => $user->email,
            ],
            'dados_novos' => $dados,
            'status' => 'pendente',
        ]);

        return back()->with('sucesso', 'Solicitação enviada. Um administrador avaliará as mudanças em breve.');
    }
}
