<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Order;
use Symfony\Contracts\EventDispatcher\Event;

final class OrderCreatedEvent extends Event
{
    public function __construct(
        private readonly Order $order,
    ) {
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}

