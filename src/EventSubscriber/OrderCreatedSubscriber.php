<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\OrderCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class OrderCreatedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderCreatedEvent::class => 'onOrderCreated',
        ];
    }

    public function onOrderCreated(OrderCreatedEvent $event): void
    {
        $order = $event->getOrder();
        $service = $order->getService();
        $price = $order->getPrice();

        $email = (new Email())
            ->from('noreply@ocenka.local')
            ->to($order->getCustomerEmail())
            ->subject('Подтверждение заказа № ' . $order->getOrderNumber())
            ->text(sprintf(
                "Здравствуйте!\n\n" .
                "Ваш заказ успешно создан.\n\n" .
                "Номер заказа: %s\n" .
                "Услуга: %s\n" .
                "Стоимость: %s %s\n" .
                "Дата заказа: %s\n\n" .
                "Мы свяжемся с вами в ближайшее время.\n\n" .
                "С уважением,\n" .
                "Команда Ocenka",
                $order->getOrderNumber(),
                $service->getName(),
                $price->getAmount(),
                $price->getCurrency()->getCode(),
                $order->getCreatedAt()->format('d.m.Y H:i')
            ));

        $this->mailer->send($email);
    }
}

