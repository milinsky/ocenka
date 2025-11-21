<?php

declare(strict_types=1);

namespace App\ApiResource;

use App\Entity\User;

final class UserResource
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly array $roles,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId()->toString(),
            email: $user->getEmail(),
            roles: $user->getRoles(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'roles' => $this->roles,
        ];
    }
}
