<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 14/04/2026 04:20
*/

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasTenancy;

/**
 * Autoria: Abimael Borges
 * Site: https://abimaelborges.adv.br
 * Data: 17/04/2026
 */
class Usuario extends Authenticatable
{
    use HasFactory, SoftDeletes, HasTenancy, Notifiable;
    protected $table = 'usuarios';

    protected $fillable = [
        'loja_id',
        'nome',
        'email',
        'senha',
        'perfil',
        'cargo',
        'ativo',
        'permissoes',
        'avatar',
    ];

    protected $hidden = [
        'senha',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'senha' => 'hashed',
            'permissoes' => 'array',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class, 'responsavel_id');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'usuario_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoUsuario::class, 'usuario_id');
    }

    public function solicitacoes(): HasMany
    {
        return $this->hasMany(SolicitacaoAtualizacao::class, 'usuario_id');
    }

    public function funcionario(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function progressoAcademy(): HasMany
    {
        return $this->hasMany(UserLessonProgress::class, 'user_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'user_id');
    }
    
    /**
     * Verifica se o usuário tem uma permissão específica no JSON de permissões.
     */
    public function temPermissao(string $permissao): bool
    {
        // Administradores e Gerentes têm acesso total às ações dentro do tenant
        if (in_array(strtolower($this->perfil), ['administrador', 'gerente'], true)) {
            return true;
        }
        
        $permissoes = $this->permissoes ?? [];
        return !empty($permissoes[$permissao]) && $permissoes[$permissao] === true;
    }

    /**
     * Verifica se o usuário é um Super Administrador SaaS (dono da plataforma).
     * Abimael Borges | https://abimaelborges.adv.br | 2026-04-16 01:08 BRT
     */
    public function isSuperAdmin(): bool
    {
        return $this->perfil === 'super_admin';
    }
    /**
     * Envia o e-mail de redefinição de senha via Job assíncrono com SMTP da loja.
     */
    public function sendPasswordResetNotification($token): void
    {
        app(\App\Services\System\NotificationService::class)->notifyPasswordReset($this, $token);
    }
}
