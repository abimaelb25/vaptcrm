<?php

declare(strict_types=1);

namespace App\Http\Middleware\SaaS;

use App\Exceptions\SaaS\PlanLimitExceededException;
use App\Exceptions\SaaS\PlanSubscriptionInactiveException;
use App\Services\SaaS\PlanService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimit
{
    public function __construct(
        private readonly PlanService $planService,
    ) {}

    public function handle(Request $request, Closure $next, string $limitKey, string $increment = '1'): Response
    {
        $delta = max(0, (int) $increment);

        try {
            $this->planService->ensureLimit($limitKey, $delta);
        } catch (PlanSubscriptionInactiveException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_code' => 'SUBSCRIPTION_INACTIVE',
                    'redirect' => route('admin.billing.index'),
                ], 402);
            }

            return redirect()->route('admin.billing.index')->with('warning', $e->getMessage());
        } catch (PlanLimitExceededException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_code' => 'PLAN_LIMIT_EXCEEDED',
                ], 422);
            }

            return back()->with('warning', $e->getMessage())->withInput();
        }

        $response = $next($request);

        if ($response->getStatusCode() < 400 && $delta > 0) {
            $this->planService->recordUsage('limit_check_passed', [
                'limit_key' => $limitKey,
                'delta' => $delta,
                'used_total' => $this->planService->currentUsage($limitKey),
                'metadata' => [
                    'path' => $request->path(),
                    'method' => $request->method(),
                ],
            ]);
        }

        return $response;
    }
}
