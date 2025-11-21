<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Yaml\Yaml;

final class OpenApiController extends AbstractController
{
    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    #[Route('/api/doc', name: 'api_doc', methods: ['GET'])]
    public function ui(): Response
    {
        return $this->render('api/swagger.html.twig');
    }

    #[Route('/api/openapi.json', name: 'api_openapi_json', methods: ['GET'])]
    public function spec(): JsonResponse
    {
        $specPath = $this->projectDir . '/config/openapi.yaml';

        $yaml = Yaml::parseFile($specPath);

        return $this->json($yaml);
    }
}
