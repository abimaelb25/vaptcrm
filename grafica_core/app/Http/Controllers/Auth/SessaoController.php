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
        // Geração do desafio anti-bot
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        session(['captcha_resultado' => $num1 + $num2]);

        return view('auth.login', compact('num1', 'num2'));
    }

    public function autenticar(Request $request): RedirectResponse
    {
        $credenciais = $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
            'verificacao_humana' => ['prohibited'], // Anti-bot dinâmico
            'captcha' => ['required', 'numeric'],
        ], [
            'verificacao_humana.prohibited' => 'Acesso negado. Atividade suspeita detectada.',
            'captcha.required' => 'Por favor, responda ao desafio anti-bot.',
            'captcha.numeric' => 'O desafio precisa ser um número.',
        ]);

        if ((int)$request->captcha !== session('captcha_resultado')) {
            return back()->withErrors(['captcha' => 'A resposta do cálculo anti-bot está incorreta.'])->withInput();
        }

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
        $request->session()->forget('captcha_resultado');

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

    // Abimael Borges | https://abimaelborges.adv.br | 2026-04-16 - Recuperação de Senha
    public function recuperarSenhaForm()
    {
        return view('auth.recuperar-senha');
    }

    public function enviarRecuperacao(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        // Simulação de envio de instrução (evita vazamento de confirmação de e-mail existente por segurança)
        return back()->with('sucesso', 'Se o e-mail estiver cadastrado, você receberá um link com as instruções para redefinição de senha em instantes.');
    }
}
