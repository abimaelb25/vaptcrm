<?php

declare(strict_types=1);

namespace App\Traits;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:04
*/

use Illuminate\Support\Facades\Auth;

trait Authorable
{
    /**
     * Inicializa o trait no modelo.
     */
    protected static function bootAuthorable(): void
    {
        // Ao criar um registro
        static::creating(function ($model) {
            if (Auth::check()) {
                if (empty($model->created_by_id)) {
                    $model->created_by_id = Auth::id();
                }
                if (empty($model->updated_by_id)) {
                    $model->updated_by_id = Auth::id();
                }
            }
        });

        // Ao atualizar um registro
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by_id = Auth::id();
            }
        });
    }

    /**
     * Relacionamento com o criador do registro.
     */
    public function criador()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'created_by_id');
    }

    /**
     * Relacionamento com quem fez a última atualização.
     */
    public function editor()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'updated_by_id');
    }
}
