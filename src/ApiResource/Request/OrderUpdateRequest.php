<?php

declare(strict_types=1);

namespace App\ApiResource\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class OrderUpdateRequest
{
    #[Assert\Choice(choices: ['pending', 'completed', 'cancelled'], message: 'Invalid status')]
    public ?string $status = null;

    public function __construct(?string $status = null)
    {
        $this->status = $status;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? null,
        );
    }
}
