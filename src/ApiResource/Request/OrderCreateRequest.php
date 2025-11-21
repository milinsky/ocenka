<?php

declare(strict_types=1);

namespace App\ApiResource\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class OrderCreateRequest
{
    #[Assert\NotBlank(message: 'Service ID is required')]
    #[Assert\Uuid(message: 'Invalid service ID format')]
    public string $serviceId;

    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Invalid email format')]
    public string $customerEmail;

    public function __construct(string $serviceId = '', string $customerEmail = '')
    {
        $this->serviceId = $serviceId;
        $this->customerEmail = $customerEmail;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            serviceId: $data['serviceId'] ?? '',
            customerEmail: $data['customerEmail'] ?? '',
        );
    }
}
