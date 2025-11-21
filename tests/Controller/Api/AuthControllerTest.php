<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use function json_decode;
use function json_encode;

final class AuthControllerTest extends WebTestCase
{
    public function testSuccessfulLoginReturnsJwtTokenAndUserData(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertIsString($data['token']);
        $this->assertNotEmpty($data['token']);

        $this->assertArrayHasKey('id', $data['user']);
        $this->assertArrayHasKey('email', $data['user']);
        $this->assertArrayHasKey('roles', $data['user']);

        $this->assertEquals('admin@example.com', $data['user']['email']);
        $this->assertContains('ROLE_ADMIN', $data['user']['roles']);
        $this->assertIsArray($data['user']['roles']);
    }

    public function testLoginFailsWithInvalidCredentials(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]));

        $this->assertResponseStatusCodeSame(401);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Unauthorized', $data['error']);
    }

    public function testLoginFailsWithNonExistentUser(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]));

        $this->assertResponseStatusCodeSame(401);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testLoginValidationFailsWithInvalidEmailFormat(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'invalid-email',
            'password' => 'password123',
        ]));

        $this->assertResponseStatusCodeSame(422);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('violations', $data);
        $this->assertEquals('Validation failed', $data['error']);
    }

    public function testLoginValidationFailsWithMissingEmail(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'password' => 'password123',
        ]));

        $this->assertResponseStatusCodeSame(422);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('violations', $data);
    }

    public function testLoginValidationFailsWithMissingPassword(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'admin@example.com',
        ]));

        $this->assertResponseStatusCodeSame(422);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('violations', $data);
    }

    public function testLoginValidationFailsWithEmptyCredentials(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => '',
            'password' => '',
        ]));

        $this->assertResponseStatusCodeSame(422);
    }
}
