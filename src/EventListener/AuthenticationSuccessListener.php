<?php

declare(strict_types=1);

namespace App\EventListener;

use App\ApiResource\UserResource;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

final class AuthenticationSuccessListener
{
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $data = $event->getData();
        $data['user'] = UserResource::fromEntity($user)->toArray();

        $event->setData($data);
    }
}
