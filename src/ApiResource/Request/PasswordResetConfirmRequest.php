<?php

declare(strict_types=1);

namespace App\ApiResource\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class PasswordResetConfirmRequest
{
    #[Assert\NotBlank(message: 'Token is required')]
    public string $token;

    #[Assert\NotBlank(message: 'New password is required')]
    #[Assert\Length(min: 8, minMessage: 'Password must be at least 8 characters')]
    public string $newPassword;

    public function __construct(string $token = '', string $newPassword = '')
    {
        $this->token = $token;
        $this->newPassword = $newPassword;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'] ?? '',
            newPassword: $data['newPassword'] ?? '',
        );
    }
}
