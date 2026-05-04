<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Loja;
use App\Models\SaaS\Assinatura;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessarExpiracaoPlanosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $graceDays = max(0, (int) config('saas.grace_period_days', 3));

        Assinatura::query()->orderByDesc('id')->get()->unique('loja_id')->each(function (Assinatura $assinatura) use ($graceDays): void {
            $changed = false;
            $now = now();

            if ($assinatura->status === Assinatura::STATUS_TRIAL && $assinatura->trial_ends_at && $assinatura->trial_ends_at->isPast()) {
                if (! $assinatura->grace_ends_at || $assinatura->grace_ends_at->isPast()) {
                    $assinatura->status = Assinatura::STATUS_PAST_DUE;
                    $assinatura->grace_ends_at = $graceDays > 0 ? $now->copy()->addDays($graceDays) : $now;
                    $assinatura->ends_at = $assinatura->ends_at ?? $now;
                    $changed = true;
                }
            }

            if (in_array($assinatura->status, [Assinatura::STATUS_ACTIVE, Assinatura::STATUS_PAST_DUE], true)
                && $assinatura->ends_at
                && $assinatura->ends_at->isPast()
                && ! $assinatura->grace_ends_at
            ) {
                $assinatura->status = Assinatura::STATUS_PAST_DUE;
                $assinatura->grace_ends_at = $graceDays > 0 ? $now->copy()->addDays($graceDays) : $now;
                $changed = true;
            }

            if ($assinatura->grace_ends_at && $assinatura->grace_ends_at->isPast() && ! $assinatura->ativa()) {
                if ($assinatura->status !== Assinatura::STATUS_EXPIRED) {
                    $assinatura->status = Assinatura::STATUS_EXPIRED;
                    $assinatura->ends_at = $assinatura->ends_at ?? $now;
                    $changed = true;
                }

                $loja = Loja::query()->find($assinatura->loja_id);
                if ($loja && ! $loja->estaBloqueada()) {
                    $loja->bloquear('assinatura_expirada');
                }
            }

            if ($changed) {
                $assinatura->save();
            }
        });
    }
}
