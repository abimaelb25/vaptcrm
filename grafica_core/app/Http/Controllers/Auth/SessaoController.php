<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 20:09 -03:00
*/

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessaoController extends Controller
{
    public function formulario()
    {
        return view('auth.login');
    }

    public function autenticar(Request $request): RedirectResponse
    {
        $credenciais = $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
        ]);

        // Tenta encontrar o usuário em qualquer loja (Cross-tenant) para resolver o contexto
        // Autoria: Abimael Borges | https://abimaelborges.adv.br | 2026-04-16
        $user = \App\Models\Usuario::withoutGlobalScope('loja')
            ->where('email', $credenciais['email'])
            ->first();

        if ($user && $user->loja_id) {
            // Sincroniza o contexto de Tenant para esta requisição com a loja do usuário
            app(\App\Services\SaaS\TenantContext::class)->setLoja($user->loja);
        }

        if (! Auth::attempt(['email' => $credenciais['email'], 'password' => $credenciais['senha']])) {
            return back()->withErrors([
                'email' => 'Credenciais inválidas ou usuário não pertence a esta unidade.',
            ])->withInput();
        }

        $request->session()->regenerate();

        // Diferencia o redirecionamento com base no perfil, respeitando a intenção original
        // Abimael Borges | https://abimaelborges.adv.br | 2026-04-16 01:04 BRT
        if (Auth::user()->isSuperAdmin()) {
            return redirect()->intended(route('superadmin.dashboard'));
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function sair(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Abimael Borges | https://abimaelborges.adv.br | 2026-04-17 - Recuperação de Senha
    public function recuperarSenhaForm()
    {
        return view('auth.recuperar-senha');
    }

    public function enviarRecuperacao(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // O broker do Laravel cuida da geração do token e do envio do e-mail
        $status = \Illuminate\Support\Facades\Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? back()->with('sucesso', 'Link de recuperação enviado com sucesso. Verifique sua caixa de entrada.')
            : back()->withErrors(['email' => 'O e-mail informado não foi encontrado em nossa base de dados.']);
    }

    public function redefinirSenhaForm(string $token)
    {
        return view('auth.redefinir-senha', ['token' => $token, 'email' => request('email')]);
    }

    public function atualizarSenha(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'senha' => \Illuminate\Support\Facades\Hash::make($password)
                ])->save();

                \Illuminate\Support\Facades\Auth::login($user);
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('admin.dashboard')->with('sucesso', 'Senha redefinida com sucesso. Você já está logado.')
            : back()->withErrors(['email' => 'Ocorreu um erro ao redefinir sua senha. Tente novamente ou solicite um novo link.']);
    }
}
