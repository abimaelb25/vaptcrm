<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Models\WhatsApp\WhatsAppConversation;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Models\WhatsApp\WhatsAppWebhookEvent;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard metrics for the WhatsApp module.
 *
 * All queries are scoped to a single loja_id (multi-tenant safe).
 */
class WhatsAppDashboardService
{
    // -------------------------------------------------------------------------
    // Main payload
    // -------------------------------------------------------------------------

    /**
     * Returns all metrics needed for the dashboard view.
     *
     * @return array<string, mixed>
     */
    public function metricsForLoja(int $lojaId, string $inicio, string $fim): array
    {
        $sentInPeriod        = $this->countSentInPeriod($lojaId, $inicio, $fim);
        $receivedInPeriod    = $this->countReceivedInPeriod($lojaId, $inicio, $fim);
        $failedInPeriod      = $this->countFailedInPeriod($lojaId, $inicio, $fim);
        $openConversations   = $this->countOpenConversations($lojaId);
        $unreadConversations = $this->countUnreadConversations($lojaId);
        $avgFirstReply       = $this->avgFirstReplySeconds($lojaId, $inicio, $fim);
        $responseRate        = $this->responseRate($lojaId, $inicio, $fim);
        $failureBreakdown    = $this->failureBreakdown($lojaId, $inicio, $fim);
        $dailyVolume         = $this->dailyMessageVolume($lojaId, $inicio, $fim);
        $automatedVsHuman    = $this->automatedVsHumanBreakdown($lojaId, $inicio, $fim);
        $webhookEvents       = $this->recentWebhookEvents($lojaId);

        return compact(
            'sentInPeriod',
            'receivedInPeriod',
            'failedInPeriod',
            'openConversations',
            'unreadConversations',
            'avgFirstReply',
            'responseRate',
            'failureBreakdown',
            'dailyVolume',
            'automatedVsHuman',
            'webhookEvents',
            'inicio',
            'fim',
        );
    }

    // -------------------------------------------------------------------------
    // Counters
    // -------------------------------------------------------------------------

    public function countSentInPeriod(int $lojaId, string $inicio, string $fim): int
    {
        return (int) WhatsAppMessage::where('loja_id', $lojaId)
            ->where('direction', WhatsAppMessage::DIRECTION_OUTBOUND)
            ->whereNotIn('status', [WhatsAppMessage::STATUS_FAILED])
            ->whereBetween('created_at', [$inicio . ' 00:00:00', $fim . ' 23:59:59'])
            ->count();
    }

    public function countReceivedInPeriod(int $lojaId, string $inicio, string $fim): int
    {
        return (int) WhatsAppMessage::where('loja_id', $lojaId)
            ->where('direction', WhatsAppMessage::DIRECTION_INBOUND)
            ->whereBetween('created_at', [$inicio . ' 00:00:00', $fim . ' 23:59:59'])
            ->count();
    }

    public function countFailedInPeriod(int $lojaId, string $inicio, string $fim): int
    {
        return (int) WhatsAppMessage::where('loja_id', $lojaId)
            ->where('status', WhatsAppMessage::STATUS_FAILED)
            ->whereBetween('created_at', [$inicio . ' 00:00:00', $fim . ' 23:59:59'])
            ->count();
    }

    public function countOpenConversations(int $lojaId): int
    {
        return (int) WhatsAppConversation::where('loja_id', $lojaId)
            ->whereIn('status', [WhatsAppConversation::STATUS_OPEN, WhatsAppConversation::STATUS_WAITING])
            ->count();
    }

    public function countUnreadConversations(int $lojaId): int
    {
        return (int) WhatsAppConversation::where('loja_id', $lojaId)
            ->where('is_unread', true)
            ->count();
    }

    // -------------------------------------------------------------------------
    // Response metrics
    // -------------------------------------------------------------------------

    /**
     * Average first-reply time in seconds (outbound after inbound, same conversation).
     * Returns null if no data.
     */
    public function avgFirstReplySeconds(int $lojaId, string $inicio, string $fim): ?float
    {
        // Find conversations that had an inbound message in period
        $conversationIds = WhatsAppMessage::where('loja_id', $lojaId)
            ->where('direction', WhatsAppMessage::DIRECTION_INBOUND)
            ->whereBetween('created_at', [$inicio . ' 00:00:00', $fim . ' 23:59:59'])
            ->distinct()
            ->pluck('conversation_id');

        if ($conversationIds->isEmpty()) {
            return null;
        }

        $totalSeconds = 0;
        $count        = 0;

        foreach ($conversationIds as $convId) {
            $firstInbound = WhatsAppMessage::where('conversation_id', $convId)
                ->where('direction', WhatsAppMessage::DIRECTION_INBOUND)
                ->orderBy('created_at')
                ->value('created_at');

            $firstOutbound = WhatsAppMessage::where('conversation_id', $convId)
                ->where('direction', WhatsAppMessage::DIRECTION_OUTBOUND)
                ->where('created_at', '>', $firstInbound)
                ->orderBy('created_at')
                ->value('created_at');

            if ($firstInbound && $firstOutbound) {
                $totalSeconds += abs(strtotime((string) $firstOutbound) - strtotime((string) $firstInbound));
                $count++;
            }
        }

        return $count > 0 ? round($totalSeconds / $count) : null;
    }

    /**
     * Percentage of inbound conversations that got at least one outbound reply.
     */
    public function responseRate(int $lojaId, string $inicio, string $fim): float
    {
        $inboundConvs = WhatsAppMessage::where('loja_id', $lojaId)
            ->where('direction', WhatsAppMessage::DIRECTION_INBOUND)
            ->whereBetween('created_at', [$inicio . ' 00:00:00', $fim . ' 23:59:59'])
            ->distinct('conversation_id')
            ->count('conversation_id');

        if ($inboundConvs === 0) {
            return 0.0;
        }

        $replied = WhatsAppConversation::where('loja_id', $lojaId)
            ->whereHas('messages', function ($q) use ($inicio, $fim): void {
                $q->where('direction', WhatsAppMessage::DIRECTION_INBOUND)
                  ->whereBetween('created_at', [$inicio . ' 00:00:00', $fim . ' 23:59:59']);
            })
            ->whereHas('messages', function ($q): void {
                $q->where('direction', WhatsAppMessage::DIRECTION_OUTBOUND);
            })
            ->count();

        return round(min(100.0, ($replied / $inboundConvs) * 100), 1);
    }

    // -------------------------------------------------------------------------
    // Breakdowns
    // -------------------------------------------------------------------------

    /**
     * Failure reason breakdown for the period.
     *
     * @return array<string, int>
     */
    public function failureBreakdown(int $lojaId, string $inicio, string $fim): array
    {
        $failures = WhatsAppMessage::where('loja_id', $lojaId)
            ->where('status', WhatsAppMessage::STATUS_FAILED)
            ->whereBetween('created_at', [$inicio . ' 00:00:00', $fim . ' 23:59:59'])
            ->pluck('error_message');

        $buckets = [
            'Número inválido'       => 0,
            'Sem opt-in'            => 0,
            'Fora da janela 24h'    => 0,
            'Cota excedida'         => 0,
            'Outros'                => 0,
        ];

        foreach ($failures as $err) {
            $lower = mb_strtolower((string) $err);
            if (str_contains($lower, 'invalid') || str_contains($lower, 'number') || str_contains($lower, 'número')) {
                $buckets['Número inválido']++;
            } elseif (str_contains($lower, 'opt-in') || str_contains($lower, 'optin')) {
                $buckets['Sem opt-in']++;
            } elseif (str_contains($lower, '24h') || str_contains($lower, 'window') || str_contains($lower, 'janela')) {
                $buckets['Fora da janela 24h']++;
            } elseif (str_contains($lower, 'quota') || str_contains($lower, 'limit') || str_contains($lower, 'cota')) {
                $buckets['Cota excedida']++;
            } else {
                $buckets['Outros']++;
            }
        }

        return array_filter($buckets, fn (int $v): bool => $v > 0);
    }

    /**
     * Daily message volume for chart rendering.
     *
     * @return array<array{date: string, sent: int, received: int}>
     */
    public function dailyMessageVolume(int $lojaId, string $inicio, string $fim): array
    {
        $rows = WhatsAppMessage::where('loja_id', $lojaId)
            ->whereBetween('created_at', [$inicio . ' 00:00:00', $fim . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, direction, COUNT(*) as total')
            ->groupBy('date', 'direction')
            ->orderBy('date')
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $d = $row->date;
            if (!isset($grouped[$d])) {
                $grouped[$d] = ['date' => $d, 'sent' => 0, 'received' => 0];
            }
            if ($row->direction === WhatsAppMessage::DIRECTION_OUTBOUND) {
                $grouped[$d]['sent'] = (int) $row->total;
            } else {
                $grouped[$d]['received'] = (int) $row->total;
            }
        }

        return array_values($grouped);
    }

    /**
     * @return array{automated: int, human: int}
     */
    public function automatedVsHumanBreakdown(int $lojaId, string $inicio, string $fim): array
    {
        $outbound = WhatsAppMessage::where('loja_id', $lojaId)
            ->where('direction', WhatsAppMessage::DIRECTION_OUTBOUND)
            ->whereBetween('created_at', [$inicio . ' 00:00:00', $fim . ' 23:59:59'])
            ->selectRaw('is_automated, COUNT(*) as total')
            ->groupBy('is_automated')
            ->pluck('total', 'is_automated');

        return [
            'automated' => (int) ($outbound[1] ?? $outbound['1'] ?? 0),
            'human'     => (int) ($outbound[0] ?? $outbound['0'] ?? 0),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function recentWebhookEvents(int $lojaId, int $limit = 10)
    {
        return WhatsAppWebhookEvent::where('loja_id', $lojaId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    // -------------------------------------------------------------------------
    // Human-readable helpers
    // -------------------------------------------------------------------------

    /**
     * Format seconds as "Xm Ys" or "Xh Ym".
     */
    public static function formatSeconds(?float $seconds): string
    {
        if ($seconds === null) {
            return 'Sem dados';
        }

        $s = (int) $seconds;

        if ($s < 60) {
            return "{$s}s";
        }

        if ($s < 3600) {
            $m = (int) ($s / 60);
            $sec = $s % 60;
            return "{$m}m {$sec}s";
        }

        $h = (int) ($s / 3600);
        $m = (int) (($s % 3600) / 60);
        return "{$h}h {$m}m";
    }
}
