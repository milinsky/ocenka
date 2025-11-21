<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Order;

interface OrderRepositoryInterface
{
    public function findOneByOrderNumber(string $orderNumber): ?Order;

    public function findByCustomerEmail(string $email): array;

    public function findAll(): array;

    public function findLatest(int $limit = 20): array;
}
