<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\ApiResource\ServiceResource;
use App\Repository\ServiceRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

use function array_map;

#[Route('/api/services', name: 'api_services_')]
final class ServiceController extends AbstractController
{
    public function __construct(
        private readonly ServiceRepositoryInterface $serviceRepository,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $services = $this->serviceRepository->findActive();

        $resources = array_map(
            fn ($service) => ServiceResource::fromEntity($service)->toArray(),
            $services
        );

        return $this->json($resources);
    }
}
