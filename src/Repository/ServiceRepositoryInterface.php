<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Service;

interface ServiceRepositoryInterface
{
    public function findOneBySlug(string $slug): ?Service;

    public function findActive(): array;

    public function findAll(): array;
}
