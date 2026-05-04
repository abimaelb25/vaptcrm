<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exceptions\SaaS\PlanLimitExceededException;
use App\Exceptions\SaaS\PlanSubscriptionInactiveException;
use App\Models\ProductionOrder;
use App\Services\Domain\ProductionService;
use App\Services\SaaS\UsageTrackerService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ProductionOrderApiController extends Controller
{
    public function __construct(
        protected ProductionService $productionService,
        protected UsageTrackerService $usageTrackerService,
    ) {}

    public function kanban(Request $request): JsonResponse
    {
        $lojaId = (int) $request->user()->loja_id;
        $this->usageTrackerService->trackApiConsumption($lojaId, 'api.production.kanban', [
            'method' => 'GET',
        ]);

        return $this->successResponse([
            'kanban' => $this->productionService->getKanban($lojaId),
            'metrics' => $this->productionService->getProductionMetrics($lojaId),
        ]);
    }

    public function metrics(Request $request): JsonResponse
    {
        $lojaId = (int) $request->user()->loja_id;
        $this->usageTrackerService->trackApiConsumption($lojaId, 'api.production.metrics', [
            'method' => 'GET',
        ]);

        return $this->successResponse(
            $this->productionService->getProductionMetrics($lojaId)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $lojaId = (int) $request->user()->loja_id;
        $this->usageTrackerService->trackApiConsumption($lojaId, 'api.production.orders.store', [
            'method' => 'POST',
        ]);

        try {
            $this->authorize('create', ProductionOrder::class);

            $validated = Validator::make($request->all(), [
            'pedido_id' => [
                'required',
                'integer',
                Rule::exists('pedidos', 'id')->where(fn ($query) => $query->where('loja_id', $lojaId)),
            ],
            'cliente_nome' => ['required', 'string', 'max:255'],
            'produto_nome' => ['required', 'string', 'max:255'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'data_previsao' => ['nullable', 'date'],
            'prioridade' => ['nullable', Rule::in(['baixa', 'normal', 'alta', 'urgente'])],
            'observacoes' => ['nullable', 'string'],
            ])->validate();

            $order = $this->productionService->criarOrdem([
                ...$validated,
                'loja_id' => $lojaId,
                'usuario_id' => $request->user()->id,
            ]);

            return $this->successResponse(
                ['ordem' => $order],
                'Ordem de produção criada com sucesso.',
                201
            );
        } catch (AuthorizationException) {
            return $this->errorResponse('Acesso negado para criar ordem de produção.', 403);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        } catch (PlanSubscriptionInactiveException $exception) {
            return $this->errorResponse($exception->getMessage(), 402);
        } catch (PlanLimitExceededException $exception) {
            return $this->errorResponse($exception->getMessage(), 422);
        } catch (InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), 422);
        }
    }

    public function move(Request $request, int $id): JsonResponse
    {
        $lojaId = (int) $request->user()->loja_id;
        $this->usageTrackerService->trackApiConsumption($lojaId, 'api.production.orders.move', [
            'method' => 'PATCH',
            'order_id' => $id,
        ]);

        try {
            $order = ProductionOrder::where('loja_id', $lojaId)->find($id);

            if (!$order) {
                return $this->errorResponse('OP não encontrada para esta loja.', 404);
            }

            $this->authorize('move', $order);

            $validated = Validator::make($request->all(), [
            'next_step_id' => [
                'required',
                'integer',
                Rule::exists('production_steps', 'id')->where(fn ($query) => $query->where('loja_id', $lojaId)),
            ],
            'observacao' => ['nullable', 'string'],
            ])->validate();

            $movedOrder = $this->productionService->moverOrdem(
                lojaId: $lojaId,
                orderId: $id,
                nextStepId: (int) $validated['next_step_id'],
                usuarioId: (int) $request->user()->id,
                observacao: $validated['observacao'] ?? null,
            );

            return $this->successResponse(
                ['ordem' => $movedOrder],
                'Ordem de produção movida com sucesso.'
            );
        } catch (AuthorizationException) {
            return $this->errorResponse('Acesso negado para movimentar esta OP.', 403);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse($exception->getMessage(), 404);
        } catch (InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), 422);
        }
    }

    public function history(Request $request, int $id): JsonResponse
    {
        $lojaId = (int) $request->user()->loja_id;
        $this->usageTrackerService->trackApiConsumption($lojaId, 'api.production.orders.history', [
            'method' => 'GET',
            'order_id' => $id,
        ]);

        try {
            $order = ProductionOrder::where('loja_id', $lojaId)->find($id);

            if (!$order) {
                return $this->errorResponse('OP não encontrada para esta loja.', 404);
            }

            $this->authorize('view', $order);

            $history = $this->productionService->getOrderHistory($lojaId, $id);

            return $this->successResponse(['historico' => $history]);
        } catch (AuthorizationException) {
            return $this->errorResponse('Acesso negado para visualizar histórico desta OP.', 403);
        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse($exception->getMessage(), 404);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $lojaId = (int) $request->user()->loja_id;
        $this->usageTrackerService->trackApiConsumption($lojaId, 'api.production.orders.show', [
            'method' => 'GET',
            'order_id' => $id,
        ]);

        $order = ProductionOrder::where('loja_id', $lojaId)
            ->with([
                'stages' => fn ($q) => $q->orderBy('ordem_snapshot')
                    ->with(['stepDefinition:id,nome', 'asset:id,nome,tipo', 'insumos.insumo:id,nome,unidade_medida']),
                'currentStep:id,nome',
            ])
            ->find($id);

        if (!$order) {
            return $this->errorResponse('OP não encontrada para esta loja.', 404);
        }

        return $this->successResponse([
            'ordem' => $order,
            'progresso' => $order->progresso,
        ]);
    }

    public function updateStepStatus(Request $request, int $orderId, int $stepId): JsonResponse
    {
        $lojaId = (int) $request->user()->loja_id;
        $this->usageTrackerService->trackApiConsumption($lojaId, 'api.production.orders.step.status', [
            'method' => 'PATCH',
            'order_id' => $orderId,
            'step_id' => $stepId,
        ]);

        try {
            $order = ProductionOrder::where('loja_id', $lojaId)->find($orderId);

            if (!$order) {
                return $this->errorResponse('OP não encontrada para esta loja.', 404);
            }

            $orderStep = $order->stages()->where('id', $stepId)->first();

            if (!$orderStep) {
                return $this->errorResponse('Etapa não encontrada nesta OP.', 404);
            }

            $validated = Validator::make($request->all(), [
                'status' => ['required', Rule::in(['pendente', 'em_andamento', 'concluido'])],
            ])->validate();

            $this->productionService->updateStepStatus(
                $orderStep,
                $validated['status'],
                (int) $request->user()->id
            );

            return $this->successResponse(
                ['etapa' => $orderStep->fresh()],
                'Status da etapa atualizado.'
            );
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    private function successResponse(array $data, string $message = 'Operação realizada com sucesso.', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, int $status, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => [
                'errors' => $errors,
            ],
        ], $status);
    }

    private function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        return $this->errorResponse(
            'Dados inválidos para a operação solicitada.',
            422,
            $exception->errors()
        );
    }
}
