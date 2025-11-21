<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\ApiResource\OrderResource;
use App\ApiResource\Request\OrderCreateRequest;
use App\ApiResource\Request\OrderUpdateRequest;
use App\Service\OrderService;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_map;
use function count;
use function json_decode;

#[Route('/api/orders', name: 'api_orders_')]
final class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(): JsonResponse
    {
        $orders = $this->orderService->getAllOrders();

        $resources = array_map(
            fn ($order) => OrderResource::fromEntity($order)->toArray(),
            $orders
        );

        return $this->json($resources);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = OrderCreateRequest::fromArray($data ?? []);

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            return $this->json([
                'error' => 'Validation failed',
                'violations' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $order = $this->orderService->createOrder($dto->serviceId, $dto->customerEmail);

            return $this->json(
                OrderResource::fromEntity($order)->toArray(),
                Response::HTTP_CREATED
            );
        } catch (InvalidArgumentException $e) {
            return $this->json([
                'error' => 'Not found',
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(string $id): JsonResponse
    {
        try {
            $order = $this->orderService->getOrder($id);

            if ($order === null) {
                return $this->json([
                    'error' => 'Not found',
                    'message' => 'Order not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->json(OrderResource::fromEntity($order)->toArray());
        } catch (InvalidArgumentException $e) {
            return $this->json([
                'error' => 'Bad request',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = OrderUpdateRequest::fromArray($data ?? []);

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            return $this->json([
                'error' => 'Validation failed',
                'violations' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $order = $this->orderService->updateOrder($id, $dto->status);

            return $this->json(OrderResource::fromEntity($order)->toArray());
        } catch (InvalidArgumentException $e) {
            return $this->json([
                'error' => 'Not found',
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(string $id): JsonResponse
    {
        try {
            $this->orderService->deleteOrder($id);

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (InvalidArgumentException $e) {
            return $this->json([
                'error' => 'Not found',
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
