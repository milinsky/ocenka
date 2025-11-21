<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use function json_decode;

final class ServiceControllerTest extends WebTestCase
{
    public function testGetServicesReturnsListOfActiveServices(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/services');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        foreach ($data as $service) {
            $this->assertArrayHasKey('id', $service);
            $this->assertArrayHasKey('name', $service);
            $this->assertArrayHasKey('slug', $service);
            $this->assertArrayHasKey('price', $service);
            $this->assertArrayHasKey('isActive', $service);
            $this->assertTrue($service['isActive']);

            $this->assertArrayHasKey('amount', $service['price']);
            $this->assertArrayHasKey('currency', $service['price']);
        }
    }

    public function testGetServicesIsPubliclyAccessible(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/services');

        $this->assertResponseIsSuccessful();
    }

    public function testGetServicesReturnsJson(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/services');

        $this->assertResponseHeaderSame('content-type', 'application/json');
    }
}
