<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use function bin2hex;
use function random_bytes;
use function sprintf;

final class PasswordResetService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepositoryInterface $userRepository,
        private readonly MailerInterface $mailer,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function initPasswordReset(string $email): void
    {
        $user = $this->userRepository->findOneByEmail($email);

        if (!$user instanceof User) {
            throw new InvalidArgumentException('User not found');
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = new DateTime('+1 hour');

        $user->setPasswordResetToken($token);
        $user->setPasswordResetExpiresAt($expiresAt);

        $this->entityManager->flush();

        $emailMessage = (new Email())
            ->from('noreply@ocenka.local')
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->text(sprintf(
                'To reset your password, click the following link: %s',
                'http://localhost/password-reset/confirm/' . $token
            ));

        $this->mailer->send($emailMessage);
    }

    public function confirmPasswordReset(string $token, string $newPassword): void
    {
        $user = $this->userRepository->findOneByPasswordResetToken($token);

        if (!$user instanceof User) {
            throw new InvalidArgumentException('Invalid or expired token');
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $user->setPasswordResetToken(null);
        $user->setPasswordResetExpiresAt(null);

        $this->entityManager->flush();
    }
}
