<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Money\Currency;
use Money\Money;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'password123');
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        $carService = new Service();
        $carService->setName('Оценка стоимости автомобиля');
        $carService->setSlug('car-valuation');
        $carService->setDescription('Профессиональная оценка рыночной стоимости автомобиля');
        $carService->setPrice(new Money('50000', new Currency('RUB')));
        $carService->setIsActive(true);
        $manager->persist($carService);

        $apartmentService = new Service();
        $apartmentService->setName('Оценка стоимости квартиры');
        $apartmentService->setSlug('apartment-valuation');
        $apartmentService->setDescription('Оценка рыночной стоимости квартиры или жилого помещения');
        $apartmentService->setPrice(new Money('100000', new Currency('RUB')));
        $apartmentService->setIsActive(true);
        $manager->persist($apartmentService);

        $businessService = new Service();
        $businessService->setName('Оценка стоимости бизнеса');
        $businessService->setSlug('business-valuation');
        $businessService->setDescription('Комплексная оценка стоимости действующего бизнеса');
        $businessService->setPrice(new Money('250000', new Currency('RUB')));
        $businessService->setIsActive(true);
        $manager->persist($businessService);

        $manager->flush();

        $order1 = new Order();
        $order1->setOrderNumber('ORD-20241118-ABC123');
        $order1->setService($carService);
        $order1->setCustomerEmail('customer1@example.com');
        $order1->setPrice($carService->getPrice());
        $order1->setStatus('pending');
        $manager->persist($order1);

        $order2 = new Order();
        $order2->setOrderNumber('ORD-20241118-DEF456');
        $order2->setService($apartmentService);
        $order2->setCustomerEmail('customer2@example.com');
        $order2->setPrice($apartmentService->getPrice());
        $order2->setStatus('completed');
        $manager->persist($order2);

        $manager->flush();
    }
}
