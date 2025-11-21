<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;
use App\Entity\Service;
use App\Event\OrderCreatedEvent;
use App\Repository\OrderRepositoryInterface;
use App\Repository\ServiceRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ServiceRepositoryInterface $serviceRepository,
        private readonly OrderNumberGenerator $numberGenerator,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function createOrder(string $serviceId, string $customerEmail): Order
    {
        $serviceUuid = Uuid::fromString($serviceId);
        $service = $this->serviceRepository->find($serviceUuid);

        if (!$service instanceof Service) {
            throw new InvalidArgumentException('Service not found');
        }

        if (!$service->isActive()) {
            throw new InvalidArgumentException('Service is not active');
        }

        $order = new Order();
        $order->setOrderNumber($this->numberGenerator->generate());
        $order->setService($service);
        $order->setCustomerEmail($customerEmail);
        $order->setPrice($service->getPrice());
        $order->setStatus('pending');

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new OrderCreatedEvent($order));

        return $order;
    }

    public function updateOrder(string $orderId, ?string $status = null): Order
    {
        $orderUuid = Uuid::fromString($orderId);
        $order = $this->orderRepository->find($orderUuid);

        if (!$order instanceof Order) {
            throw new InvalidArgumentException('Order not found');
        }

        if ($status !== null) {
            $order->setStatus($status);
        }

        $this->entityManager->flush();

        return $order;
    }

    public function deleteOrder(string $orderId): void
    {
        $orderUuid = Uuid::fromString($orderId);
        $order = $this->orderRepository->find($orderUuid);

        if (!$order instanceof Order) {
            throw new InvalidArgumentException('Order not found');
        }

        $this->entityManager->remove($order);
        $this->entityManager->flush();
    }

    public function getOrder(string $orderId): ?Order
    {
        $orderUuid = Uuid::fromString($orderId);

        return $this->orderRepository->find($orderUuid);
    }

    public function getAllOrders(): array
    {
        return $this->orderRepository->findAll();
    }
}
