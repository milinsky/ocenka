<?php

declare(strict_types=1);

namespace App\ApiResource;

use App\Entity\Service;

final class ServiceResource
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly int $priceAmount,
        public readonly string $priceCurrency,
        public readonly bool $isActive,
    ) {
    }

    public static function fromEntity(Service $service): self
    {
        return new self(
            id: $service->getId()->toString(),
            name: $service->getName(),
            slug: $service->getSlug(),
            description: $service->getDescription(),
            priceAmount: (int) $service->getPrice()->getAmount(),
            priceCurrency: $service->getPrice()->getCurrency()->getCode(),
            isActive: $service->isActive(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => [
                'amount' => $this->priceAmount,
                'currency' => $this->priceCurrency,
            ],
            'isActive' => $this->isActive,
        ];
    }
}
