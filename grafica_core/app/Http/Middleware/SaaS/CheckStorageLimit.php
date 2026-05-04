<?php

declare(strict_types=1);

namespace App\Http\Middleware\SaaS;

use App\Services\SaaS\PlanService;
use App\Services\SaaS\UsageTrackerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStorageLimit
{
    public function __construct(
        private readonly PlanService $planService,
        private readonly UsageTrackerService $usageTrackerService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $incomingBytes = (int) ($request->server('CONTENT_LENGTH') ?? 0);
        $policy = $this->planService->evaluateStoragePolicy($incomingBytes);

        if (! $policy['allowed']) {
            $message = $policy['message'] ?? 'Limite de armazenamento do plano atingido para novos uploads.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error_code' => 'PLAN_STORAGE_LIMIT_EXCEEDED',
                    'storage_level' => $policy['level'] ?? 'blocked',
                    'redirect' => route('admin.billing.index'),
                ], 422);
            }

            return back()->with('warning', $message)->withInput();
        }

        if (! empty($policy['message'])) {
            session()->flash('warning', $policy['message']);
        }

        if ($incomingBytes > 0) {
            $lojaId = (int) ($request->user()?->loja_id ?? 0);
            if ($lojaId > 0) {
                $this->usageTrackerService->trackUpload($lojaId, $incomingBytes, [
                    'path' => $request->path(),
                    'method' => $request->method(),
                ]);
            }
        }

        return $next($request);
    }
}
