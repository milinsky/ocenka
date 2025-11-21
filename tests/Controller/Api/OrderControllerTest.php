<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use function json_decode;
use function json_encode;

final class OrderControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testCreateOrderAsAnonymous(): void
    {
        $service = $this->entityManager->getRepository(Service::class)->findOneBySlug('car-valuation');

        $this->client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'serviceId' => $service->getId()->toString(),
            'customerEmail' => 'test@example.com',
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('orderNumber', $responseData);
        $this->assertEquals('test@example.com', $responseData['customerEmail']);
    }

    public function testCreateOrderWithInvalidData(): void
    {
        $this->client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'serviceId' => '',
            'customerEmail' => 'invalid-email',
        ]));

        $this->assertResponseStatusCodeSame(422);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Validation failed', $responseData['error']);
        $this->assertArrayHasKey('violations', $responseData);
    }

    public function testListOrdersWithoutAuth(): void
    {
        $this->client->request('GET', '/api/orders');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testListOrdersWithAuth(): void
    {
        $token = $this->getAuthToken();

        $this->client->request('GET', '/api/orders', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
    }

    public function testGetOrderWithoutAuth(): void
    {
        $service = $this->entityManager->getRepository(Service::class)->findOneBySlug('car-valuation');

        $this->client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'serviceId' => $service->getId()->toString(),
            'customerEmail' => 'test@example.com',
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $orderId = $responseData['id'];

        $this->client->request('GET', '/api/orders/' . $orderId);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetOrderWithAuthReturnsOrderData(): void
    {
        $service = $this->entityManager->getRepository(Service::class)->findOneBy(['isActive' => true]);

        $this->client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'serviceId' => $service->getId()->toString(),
            'customerEmail' => 'customer@example.com',
        ]));

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $orderId = $createResponse['id'];

        $token = $this->getAuthToken();

        $this->client->request('GET', '/api/orders/' . $orderId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('orderNumber', $data);
        $this->assertArrayHasKey('customerEmail', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('service', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('customer@example.com', $data['customerEmail']);
    }

    public function testUpdateOrderStatusWithAuth(): void
    {
        $service = $this->entityManager->getRepository(Service::class)->findOneBy(['isActive' => true]);

        $this->client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'serviceId' => $service->getId()->toString(),
            'customerEmail' => 'update@example.com',
        ]));

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $orderId = $createResponse['id'];

        $token = $this->getAuthToken();

        $this->client->request('PUT', '/api/orders/' . $orderId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'status' => 'completed',
        ]));

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('completed', $data['status']);
    }

    public function testUpdateOrderWithoutAuthFails(): void
    {
        $service = $this->entityManager->getRepository(Service::class)->findOneBy(['isActive' => true]);

        $this->client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'serviceId' => $service->getId()->toString(),
            'customerEmail' => 'test@example.com',
        ]));

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $orderId = $createResponse['id'];

        $this->client->request('PUT', '/api/orders/' . $orderId, [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'status' => 'completed',
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeleteOrderWithAuth(): void
    {
        $service = $this->entityManager->getRepository(Service::class)->findOneBy(['isActive' => true]);

        $this->client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'serviceId' => $service->getId()->toString(),
            'customerEmail' => 'delete@example.com',
        ]));

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $orderId = $createResponse['id'];

        $token = $this->getAuthToken();

        $this->client->request('DELETE', '/api/orders/' . $orderId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(204);
    }

    public function testDeleteOrderWithoutAuthFails(): void
    {
        $service = $this->entityManager->getRepository(Service::class)->findOneBy(['isActive' => true]);

        $this->client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'serviceId' => $service->getId()->toString(),
            'customerEmail' => 'test@example.com',
        ]));

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $orderId = $createResponse['id'];

        $this->client->request('DELETE', '/api/orders/' . $orderId);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateOrderWithNonExistentServiceFails(): void
    {
        $this->client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'serviceId' => '01932d7e-8b4a-7c3f-9d2e-1f0a8b7c6d5e',
            'customerEmail' => 'test@example.com',
        ]));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateOrderValidatesEmailFormat(): void
    {
        $service = $this->entityManager->getRepository(Service::class)->findOneBy(['isActive' => true]);

        $this->client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'serviceId' => $service->getId()->toString(),
            'customerEmail' => 'not-an-email',
        ]));

        $this->assertResponseStatusCodeSame(422);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('violations', $data);
    }

    public function testCreateOrderValidatesRequiredFields(): void
    {
        $this->client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        $this->assertResponseStatusCodeSame(422);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('violations', $data);
    }

    private function getAuthToken(): string
    {
        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]));

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        return $responseData['token'];
    }
}
