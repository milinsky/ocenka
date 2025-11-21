<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PasswordResetController extends AbstractController
{
    #[Route('/password-reset', name: 'password_reset_init')]
    public function init(): Response
    {
        return $this->render('password_reset/init.html.twig');
    }

    #[Route('/password-reset/confirm/{token}', name: 'password_reset_confirm')]
    public function confirm(string $token): Response
    {
        return $this->render('password_reset/confirm.html.twig', [
            'token' => $token,
        ]);
    }
}
