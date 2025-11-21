<?php

declare(strict_types=1);

namespace App\ApiResource\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class PasswordResetInitRequest
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Invalid email format')]
    public string $email;

    public function __construct(string $email = '')
    {
        $this->email = $email;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? '',
        );
    }
}
