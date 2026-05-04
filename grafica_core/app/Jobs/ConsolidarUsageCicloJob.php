<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Loja;
use App\Models\SaaS\Assinatura;
use App\Models\SaaS\UsageChargePreview;
use App\Models\SaaS\UsageCycleSummary;
use App\Models\SaaS\UsageLog;
use App\Services\SaaS\PlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ConsolidarUsageCicloJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly ?string $referenceDate = null,
    ) {}

    public function handle(PlanService $planService): void
    {
        $reference = $this->referenceDate ? Carbon::parse($this->referenceDate) : now();
        $cycleStart = $reference->copy()->startOfMonth()->startOfDay();
        $cycleEnd = $reference->copy()->endOfMonth()->endOfDay();

        $metrics = ['max_pedidos_mes', 'max_storage_mb', 'max_produtos', 'max_usuarios'];

        Loja::query()->select('id')->orderBy('id')->chunk(200, function ($lojas) use ($metrics, $cycleStart, $cycleEnd, $planService): void {
            foreach ($lojas as $loja) {
                $assinatura = Assinatura::query()
                    ->where('loja_id', $loja->id)
                    ->latest('id')
                    ->first();

                if (! $assinatura) {
                    continue;
                }

                $breakdown = [];
                $totalAmount = 0.0;

                foreach ($metrics as $metricKey) {
                    $consumed = $this->resolveConsumed($loja->id, $metricKey, $cycleStart, $cycleEnd, $planService);
                    $limitValue = $planService->getLimit($metricKey, $loja->id);
                    $overage = $limitValue !== null ? max(0, $consumed - $limitValue) : 0;
                    $unitPrice = (float) data_get(config('saas.usage_pricing.metrics'), $metricKey . '.unit_price', 0);
                    $subtotal = round($overage * $unitPrice, 2);
                    $totalAmount += $subtotal;

                    UsageCycleSummary::query()->updateOrCreate(
                        [
                            'loja_id' => $loja->id,
                            'cycle_start' => $cycleStart->toDateString(),
                            'cycle_end' => $cycleEnd->toDateString(),
                            'metric_key' => $metricKey,
                        ],
                        [
                            'assinatura_id' => $assinatura->id,
                            'consumed' => $consumed,
                            'limit_value' => $limitValue,
                            'overage' => $overage,
                            'unit_price' => $unitPrice,
                            'subtotal' => $subtotal,
                            'metadata' => [
                                'source' => 'usage_logs',
                            ],
                            'consolidated_at' => now(),
                        ]
                    );

                    $breakdown[] = [
                        'metric_key' => $metricKey,
                        'consumed' => $consumed,
                        'limit_value' => $limitValue,
                        'overage' => $overage,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ];
                }

                UsageChargePreview::query()->updateOrCreate(
                    [
                        'loja_id' => $loja->id,
                        'cycle_start' => $cycleStart->toDateString(),
                        'cycle_end' => $cycleEnd->toDateString(),
                    ],
                    [
                        'assinatura_id' => $assinatura->id,
                        'currency' => (string) config('saas.usage_pricing.currency', 'BRL'),
                        'total_amount' => round($totalAmount, 2),
                        'status' => 'draft',
                        'breakdown' => $breakdown,
                        'generated_at' => now(),
                    ]
                );
            }
        });
    }

    private function resolveConsumed(int $lojaId, string $metricKey, Carbon $cycleStart, Carbon $cycleEnd, PlanService $planService): int
    {
        if ($metricKey === 'max_storage_mb') {
            $bytes = (int) UsageLog::query()
                ->where('loja_id', $lojaId)
                ->where('event_type', 'storage_delta')
                ->whereBetween('occurred_at', [$cycleStart, $cycleEnd])
                ->where('delta', '>', 0)
                ->get()
                ->sum(static fn (UsageLog $log): int => (int) data_get($log->metadata, 'bytes', 0));

            return (int) floor($bytes / 1024 / 1024);
        }

        $delta = (int) UsageLog::query()
            ->where('loja_id', $lojaId)
            ->where('limit_key', $metricKey)
            ->whereBetween('occurred_at', [$cycleStart, $cycleEnd])
            ->where('delta', '>', 0)
            ->whereNotIn('event_type', ['limit_check_passed'])
            ->sum('delta');

        if ($delta > 0) {
            return $delta;
        }

        return $planService->currentUsage($metricKey, $lojaId);
    }
}
