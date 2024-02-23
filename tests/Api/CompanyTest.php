<?php

namespace App\Tests\Api;

use App\Entity\User;
use App\Factory\CompanyFactory;
use App\Factory\UserFactory;

class CompanyTest extends AbstractApiTestCase
{
    public function testSeeAllCompaniesByAllUsers()
    {
        CompanyFactory::createMany(10);
        UserFactory::createOne(['role' => User::ROLE_USER, 'email' => 'user1@test.com', 'company' => CompanyFactory::random()]);
        UserFactory::createOne(['role' => User::ROLE_COMPANY_ADMIN, 'email' => 'user2@test.com', 'company' => CompanyFactory::random()]);

        $this->login(['email' => 'user1@test.com', 'password' => '123456']);
        $response = $this->createClientWithCredentials()->request('GET', '/api/companies');
        $result = $response->toArray();

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(10, $result);

        $company = CompanyFactory::random();
        $this->createClientWithCredentials()->request('GET', sprintf('/api/companies/%d', $company->getId()));
        $this->assertResponseStatusCodeSame(200);

        $this->login(['email' => 'user2@test.com', 'password' => '123456']);
        $response = $this->createClientWithCredentials()->request('GET', '/api/companies');
        $result = $response->toArray();

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(10, $result);

        $company = CompanyFactory::random();
        $this->createClientWithCredentials()->request('GET', sprintf('/api/companies/%d', $company->getId()));
        $this->assertResponseStatusCodeSame(200);

        $this->login(['email' => 'arman@arman.com', 'password' => '123456']);
        $response = $this->createClientWithCredentials()->request('GET', '/api/companies');
        $result = $response->toArray();

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(10, $result);

        $company = CompanyFactory::random();
        $this->createClientWithCredentials()->request('GET', sprintf('/api/companies/%d', $company->getId()));
        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreateCompanyOnlyBySuperAdmin()
    {
        CompanyFactory::createMany(2);
        UserFactory::createOne(['role' => User::ROLE_USER, 'email' => 'user1@test.com', 'company' => CompanyFactory::random()]);
        UserFactory::createOne(['role' => User::ROLE_COMPANY_ADMIN, 'email' => 'user2@test.com', 'company' => CompanyFactory::random()]);

        $this->login(['email' => 'user1@test.com', 'password' => '123456']);
        $this->createClientWithCredentials()->request('POST', '/api/companies', [
            'json' => [
                'name' => 'Google LLC'
            ]
        ]);

        $this->assertResponseStatusCodeSame(403);

        $this->login(['email' => 'user2@test.com', 'password' => '123456']);
        $this->createClientWithCredentials()->request('POST', '/api/companies', [
            'json' => [
                'name' => 'Google LLC'
            ]
        ]);

        $this->assertResponseStatusCodeSame(403);

        $this->login(['email' => 'arman@arman.com', 'password' => '123456']);
        $this->createClientWithCredentials()->request('POST', '/api/companies', [
            'json' => [
                'name' => 'Google LLC'
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
    }

    public function testCreateCompanyInvalidName()
    {
        $this->createClientWithCredentials()->request('POST', '/api/companies', [
            'json' => [
                'name' => 'Goog'
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'name',
                    'message' => 'This value is too short. It should have 5 characters or more.',
                    'code' => '9ff3fdc4-b214-49db-8718-39c315e33d45'
                ]
            ]
        ]);

        $this->createClientWithCredentials()->request('POST', '/api/companies', [
            'json' => [
                'name' => 'Gooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooogle'
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'name',
                    'message' => 'This value is too long. It should have 100 characters or less.',
                    'code' => 'd94b19cc-114f-4f44-9cc4-4138e80a87b9'
                ]
            ]
        ]);
    }

    public function testCreateCompanyAlreadyExists()
    {
        CompanyFactory::createOne(['name' => 'Google LLC']);

        $this->createClientWithCredentials()->request('POST', '/api/companies', [
            'json' => [
                'name' => 'Google LLC'
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'name',
                    'message' => 'This company already exists!',
                    'code' => '23bd9dbf-6b9b-41cd-a99e-4844bcf3077f'
                ]
            ]
        ]);
    }
}
