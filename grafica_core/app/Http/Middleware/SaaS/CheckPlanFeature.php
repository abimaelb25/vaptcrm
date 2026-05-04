<?php

declare(strict_types=1);

namespace App\Http\Middleware\SaaS;

use App\Exceptions\SaaS\PlanFeatureNotAvailableException;
use App\Services\SaaS\FeatureGateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanFeature
{
    public function __construct(
        private readonly FeatureGateService $featureGateService,
    ) {}

    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        try {
            $this->featureGateService->ensure($featureKey);
        } catch (PlanFeatureNotAvailableException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_code' => 'PLAN_FEATURE_BLOCKED',
                    'cta_url' => '/painel/assinatura',
                ], 403);
            }

            return redirect()->route('admin.billing.index')->with('warning', $e->getMessage());
        }

        return $next($request);
    }
}
