<?php

declare(strict_types=1);

namespace App\ApiResource;

use App\Entity\Order;

final class OrderResource
{
    public function __construct(
        public readonly string $id,
        public readonly string $orderNumber,
        public readonly ServiceResource $service,
        public readonly string $customerEmail,
        public readonly int $priceAmount,
        public readonly string $priceCurrency,
        public readonly string $status,
        public readonly string $createdAt,
    ) {
    }

    public static function fromEntity(Order $order): self
    {
        return new self(
            id: $order->getId()->toString(),
            orderNumber: $order->getOrderNumber(),
            service: ServiceResource::fromEntity($order->getService()),
            customerEmail: $order->getCustomerEmail(),
            priceAmount: (int) $order->getPrice()->getAmount(),
            priceCurrency: $order->getPrice()->getCurrency()->getCode(),
            status: $order->getStatus(),
            createdAt: $order->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'orderNumber' => $this->orderNumber,
            'service' => $this->service->toArray(),
            'customerEmail' => $this->customerEmail,
            'price' => [
                'amount' => $this->priceAmount,
                'currency' => $this->priceCurrency,
            ],
            'status' => $this->status,
            'createdAt' => $this->createdAt,
        ];
    }
}
