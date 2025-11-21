<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\ApiResource\Request\LoginRequest;
use App\ApiResource\Request\PasswordResetConfirmRequest;
use App\ApiResource\Request\PasswordResetInitRequest;
use App\ApiResource\UserResource;
use App\Repository\UserRepositoryInterface;
use App\Service\PasswordResetService;
use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function count;
use function json_decode;

#[Route('/api/auth', name: 'api_auth_')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService,
        private readonly ValidatorInterface $validator,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = LoginRequest::fromArray($data ?? []);

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

        $user = $this->userRepository->findOneByEmail($dto->email);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $dto->password)) {
            return $this->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user' => UserResource::fromEntity($user)->toArray(),
        ]);
    }

    #[Route('/password-reset/init', name: 'password_reset_init', methods: ['POST'])]
    public function passwordResetInit(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = PasswordResetInitRequest::fromArray($data ?? []);

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
            $this->passwordResetService->initPasswordReset($dto->email);

            return $this->json([
                'message' => 'Password reset email sent',
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->json([
                'error' => 'Not found',
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/password-reset/confirm', name: 'password_reset_confirm', methods: ['POST'])]
    public function passwordResetConfirm(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = PasswordResetConfirmRequest::fromArray($data ?? []);

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
            $this->passwordResetService->confirmPasswordReset($dto->token, $dto->newPassword);

            return $this->json([
                'message' => 'Password successfully reset',
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->json([
                'error' => 'Bad request',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
