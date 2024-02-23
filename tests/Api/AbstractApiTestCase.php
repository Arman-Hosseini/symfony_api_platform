<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AbstractApiTestCase extends ApiTestCase
{
    use ResetDatabase, Factories;

    private static ?string $token = null;

    protected function setUp(): void
    {
        UserFactory::createOne([
            'email' => 'arman@arman.com',
            'role' => User::ROLE_SUPER_ADMIN
        ]);
    }

    protected function login($credentials = []): void
    {
        $client = static::createClient();
        $response = $client->request('POST', '/api/login_check', ['json' => $credentials]);

        static::assertResponseHasHeader('content-type', 'application/json');
        static::assertResponseStatusCodeSame(200);

        $data = $response->toArray();
        self::$token = $data['token'];
    }


    public function testLoginInvalidCredentials()
    {
        $client = static::createClient();
        $client->request('POST', '/api/login_check', [
            'json' => [
                'email' => 'invalid@user.com',
                'password' => 'invalidPass'
            ]
        ]);

        $this->assertResponseHasHeader('content-type', 'application/json');
        $this->assertResponseStatusCodeSame(401);
    }

    protected function createClientWithCredentials(): Client
    {
        if (!self::$token) {
            $this->login(['email' => 'arman@arman.com', 'password' => '123456']);
        }

        return static::createClient([], [
            'headers' => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
                'Authorization' => sprintf('Bearer %s', self::$token)
            ]
        ]);
    }
}
