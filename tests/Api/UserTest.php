<?php

namespace App\Tests\Api;

use App\Entity\Company;
use App\Entity\User;
use App\Factory\CompanyFactory;
use App\Factory\UserFactory;

class UserTest extends AbstractApiTestCase
{
    public function testCreateUserWithInvalidName()
    {
        $data = [
            'email' => 'test1@test.com',
            'role' => User::ROLE_USER,
            'plainPassword' => '123456'
        ];

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => ['name' => 'te'] + $data
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'name',
                    'message' => 'This value is too short. It should have 3 characters or more.',
                    'code' => '9ff3fdc4-b214-49db-8718-39c315e33d45'
                ]
            ]
        ]);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => ['name' => '11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111'] + $data
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

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => ['name' => 'jane'] + $data
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'name',
                    'message' => 'The name must start with a uppercase letter.',
                    'code' => 'de1e3db3-5ed4-4941-aae4-59f3667cc3a3'
                ]
            ]
        ]);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => ['name' => 'Jane Doe.1'] + $data
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'name',
                    'message' => 'The name must contains letters and space.',
                    'code' => 'de1e3db3-5ed4-4941-aae4-59f3667cc3a3'
                ]
            ]
        ]);
    }

    public function testCreateUserWithInvalidEmail()
    {
        $data = [
            'name' => 'Jane Doe',
            'role' => User::ROLE_USER,
            'plainPassword' => '123456'
        ];

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => ['email' => ''] + $data
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'email',
                    'message' => 'This value should not be blank.',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3'
                ]
            ]
        ]);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => ['email' => 'janedoe.com'] + $data
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'email',
                    'message' => 'This value is not a valid email address.',
                    'code' => 'bd79c0ab-ddba-46cc-a703-a7a4b08de310'
                ]
            ]
        ]);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => ['email' => 'arman@arman.com'] + $data
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'email',
                    'message' => 'This email already exists!',
                    'code' => '23bd9dbf-6b9b-41cd-a99e-4844bcf3077f'
                ]
            ]
        ]);
    }

    public function testCreateUserWithInvalidRole()
    {
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@doe.com',
            'plainPassword' => '123456'
        ];

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => ['role' => ''] + $data
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'role',
                    'message' => 'This value should not be blank.',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3'
                ],
            ]
        ]);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => ['role' => 'ROLE_IS_NOT_AVAILABLE'] + $data
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'role',
                    'message' => 'This is not a valid user\'s role.',
                    'code' => '8e179f1b-97aa-4560-a02f-2a8b42e49df7'
                ],
            ]
        ]);
    }

    public function testCreateUserWithInvalidPassword()
    {
        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => [
                'name' => 'Jane Doe',
                'email' => 'jane@doe.com',
                'role' => User::ROLE_USER,
                'plainPassword' => ''
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'plainPassword',
                    'message' => 'This value should not be blank.',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3'
                ]
            ]
        ]);
    }

    public function testCreateOneSuperAdminThatCantHaveCompany()
    {
        CompanyFactory::createOne();
        $companyIRI = $this->findIriBy(Company::class, []);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => [
                'name' => 'Jane Doe',
                'email' => 'jane@doe.com',
                'role' => 'ROLE_SUPER_ADMIN',
                'plainPassword' => '123456',
                'company' => $companyIRI
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'company',
                    'message' => 'The user with this role can\'t have a company.',
                    'code' => '183ad2de-533d-4796-a439-6d3c3852b549'
                ]
            ]
        ]);
    }

    public function testCreateOneUserAndOneCompanyAdminThatMustHaveCompany()
    {
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@doe.com',
            'plainPassword' => '123456',
        ];

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => ['role' => 'ROLE_USER'] + $data
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'company',
                    'message' => 'The user with this role must have a company.',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3'
                ]
            ]
        ]);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => ['role' => 'ROLE_COMPANY_ADMIN'] + $data
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'status' => 422,
            'violations' => [
                [
                    'propertyPath' => 'company',
                    'message' => 'The user with this role must have a company.',
                    'code' => 'c1051bb4-d103-4f74-8988-acbcafc7fdc3'
                ]
            ]
        ]);
    }

    public function testCreateUserSuccessful()
    {
        CompanyFactory::createOne();
        $companyIRI = $this->findIriBy(Company::class, []);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => [
                'name' => 'Arman',
                'email' => 'test1@test.com',
                'role' => User::ROLE_USER,
                'plainPassword' => '123456',
                'company' => $companyIRI
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
    }

    public function testSeeEveryUserBySuperAdmin()
    {
        $company = CompanyFactory::createOne();
        $user1 = UserFactory::createOne(['role' => User::ROLE_USER, 'email' => 'user1@test.com', 'company' => $company]);
        $user2 = UserFactory::createOne(['role' => User::ROLE_COMPANY_ADMIN, 'email' => 'user2@test.com', 'company' => $company]);
        $user3 = UserFactory::createOne(['role' => User::ROLE_SUPER_ADMIN, 'email' => 'user3@test.com']);

        $companyIRI = $this->findIriBy(Company::class, ['id' => $company->getId()]);
        static::createClientWithCredentials()->request('GET', '/api/users');

        self::assertResponseStatusCodeSame(200);
        self::assertJsonContains([
            [
                "email" => "arman@arman.com",
                "role" => User::ROLE_SUPER_ADMIN
            ],
            [
                "email" => "user1@test.com",
                "role" => User::ROLE_USER,
                "company" => $companyIRI
            ],
            [
                "email" => "user2@test.com",
                "role" => User::ROLE_COMPANY_ADMIN,
                "company" => $companyIRI
            ],
            [
                "email" => "user3@test.com",
                "role" => User::ROLE_SUPER_ADMIN
            ]
        ]);

        static::createClientWithCredentials()->request('GET', sprintf('/api/users/%d', $user1->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonContains([
            "email" => "user1@test.com",
            "role" => User::ROLE_USER,
            "company" => $companyIRI
        ]);

        static::createClientWithCredentials()->request('GET', sprintf('/api/users/%d', $user2->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonContains([
            "email" => "user2@test.com",
            "role" => User::ROLE_COMPANY_ADMIN,
            "company" => $companyIRI
        ]);
    }

    public function testSeeAllTheirOwnCompanyUsersByUserAndCompanyAdmin()
    {
        $company1 = CompanyFactory::createOne();
        $company1IRI = $this->findIriBy(Company::class, ['id' => $company1->getId()]);
        $company2 = CompanyFactory::createOne();
        $company2IRI = $this->findIriBy(Company::class, ['id' => $company2->getId()]);
        $user1 = UserFactory::createOne(['role' => User::ROLE_USER, 'email' => 'user1@test.com', 'company' => $company1]);
        $user2 = UserFactory::createOne(['role' => User::ROLE_COMPANY_ADMIN, 'email' => 'user2@test.com', 'company' => $company1]);
        $user3 = UserFactory::createOne(['role' => User::ROLE_USER, 'email' => 'user3@test.com', 'company' => $company2]);
        $user4 = UserFactory::createOne(['role' => User::ROLE_COMPANY_ADMIN, 'email' => 'user4@test.com', 'company' => $company2]);
        UserFactory::createOne(['role' => User::ROLE_SUPER_ADMIN, 'email' => 'user5@test.com']);

        $this->login(['email' => 'user2@test.com', 'password' => '123456']);
        $this->createClientWithCredentials()->request('GET', '/api/users');

        $companyIRI1 = $this->findIriBy(Company::class, ['id' => $company1->getId()]);

        self::assertResponseStatusCodeSame(200);
        self::assertJsonContains([
            [
                "email" => "user1@test.com",
                "role" => User::ROLE_USER,
                "company" => $companyIRI1
            ],
            [
                "email" => "user2@test.com",
                "role" => User::ROLE_COMPANY_ADMIN,
                "company" => $companyIRI1
            ]
        ]);

        $this->login(['email' => 'user3@test.com', 'password' => '123456']);
        $this->createClientWithCredentials()->request('GET', '/api/users');

        $companyIRI2 = $this->findIriBy(Company::class, ['id' => $company2->getId()]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            [
                "email" => "user3@test.com",
                "role" => User::ROLE_USER,
                "company" => $companyIRI2
            ],
            [
                "email" => "user4@test.com",
                "role" => User::ROLE_COMPANY_ADMIN,
                "company" => $companyIRI2
            ]
        ]);

        $this->login(['email' => 'user1@test.com', 'password' => '123456']);
        static::createClientWithCredentials()->request('GET', sprintf('/api/users/%d', $user1->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonContains([
            "email" => "user1@test.com",
            "role" => User::ROLE_USER,
            "company" => $company1IRI
        ]);

        static::createClientWithCredentials()->request('GET', sprintf('/api/users/%d', $user2->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonContains([
            "email" => "user2@test.com",
            "role" => User::ROLE_COMPANY_ADMIN,
            "company" => $company1IRI
        ]);

        static::createClientWithCredentials()->request('GET', sprintf('/api/users/%d', $user3->getId()));

        self::assertResponseStatusCodeSame(404);

        $this->login(['email' => 'user4@test.com', 'password' => '123456']);
        static::createClientWithCredentials()->request('GET', sprintf('/api/users/%d', $user3->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonContains([
            "email" => "user3@test.com",
            "role" => User::ROLE_USER,
            "company" => $company2IRI
        ]);

        static::createClientWithCredentials()->request('GET', sprintf('/api/users/%d', $user4->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertJsonContains([
            "email" => "user4@test.com",
            "role" => User::ROLE_COMPANY_ADMIN,
            "company" => $company2IRI
        ]);

        static::createClientWithCredentials()->request('GET', sprintf('/api/users/%d', $user2->getId()));

        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateEveryUserBySuperAdmin()
    {
        CompanyFactory::createOne();
        $companyIRI = $this->findIriBy(Company::class, []);

        $data = [
            'name' => 'Test user',
            'plainPassword' => '123456',
            'company' => $companyIRI
        ];

        $this->login(['email' => 'arman@arman.com', 'password' => '123456']);
        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => [
                'email' => 'test1@test.com',
                'role' => User::ROLE_USER,
            ] + $data
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => [
                'email' => 'test2@test.com',
                'role' => User::ROLE_COMPANY_ADMIN,
            ] + $data
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => [
                'name' => 'Test user',
                'email' => 'test3@test.com',
                'plainPassword' => '123456',
                'role' => User::ROLE_SUPER_ADMIN,
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
    }

    public function testCreateUserOnlyForTheirOwnCompanyByCompanyAdmin()
    {
        $company1 = CompanyFactory::createOne();
        $companyIRI1 = $this->findIriBy(Company::class, ['id' => $company1->getId()]);
        UserFactory::createOne(['role' => User::ROLE_COMPANY_ADMIN, 'email' => 'user1@test.com', 'company' => $company1]);
        $company2 = CompanyFactory::createOne();
        $companyIRI2 = $this->findIriBy(Company::class, ['id' => $company2->getId()]);

        $data = [
            'name' => 'Test user',
            'plainPassword' => '123456',
            'company' => $companyIRI1
        ];

        $this->login(['email' => 'user1@test.com', 'password' => '123456']);
        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => [
                'email' => 'test2@test.com',
                'role' => User::ROLE_USER,
            ] + $data
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => [
                'name' => 'Test user',
                'plainPassword' => '123456',
                'email' => 'test3@test.com',
                'role' => User::ROLE_USER,
                'company' => $companyIRI2
            ]
        ]);

        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonContains([
            "detail" => "Access Denied.",
            "status" => 403
        ]);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => [
                'email' => 'test4@test.com',
                'role' => User::ROLE_COMPANY_ADMIN,
            ] + $data
        ]);

        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonContains([
            "detail" => "Access Denied.",
            "status" => 403
        ]);

        $this->createClientWithCredentials()->request('POST', '/api/users', [
            'json' => [
                'email' => 'test5@test.com',
                'role' => User::ROLE_SUPER_ADMIN,
            ] + $data
        ]);

        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonContains([
            "detail" => "Access Denied.",
            "status" => 403
        ]);
    }

    public function testDeleteUserOnlyBySuperAdmin()
    {
        $company1 = CompanyFactory::createOne();
        $company2 = CompanyFactory::createOne();
        $user1 = UserFactory::createOne(['role' => User::ROLE_USER, 'email' => 'user1@test.com', 'company' => $company1]);
        $user2 = UserFactory::createOne(['role' => User::ROLE_COMPANY_ADMIN, 'email' => 'user2@test.com', 'company' => $company1]);
        $user3 = UserFactory::createOne(['role' => User::ROLE_SUPER_ADMIN, 'email' => 'user3@test.com']);

        $user4 = UserFactory::createOne(['role' => User::ROLE_USER, 'email' => 'user4@test.com', 'company' => $company2]);
        $user5 = UserFactory::createOne(['role' => User::ROLE_USER, 'email' => 'user5@test.com', 'company' => $company2]);
        UserFactory::createOne(['role' => User::ROLE_COMPANY_ADMIN, 'email' => 'user6@test.com', 'company' => $company2]);

        $this->login(['email' => 'arman@arman.com', 'password' => '123456']);
        $this->createClientWithCredentials()->request('DELETE', sprintf('/api/users/%d', $user1->getId()));

        $this->assertResponseStatusCodeSame(204);

        $this->createClientWithCredentials()->request('DELETE', sprintf('/api/users/%d', $user2->getId()));

        $this->assertResponseStatusCodeSame(204);

        $this->createClientWithCredentials()->request('DELETE', sprintf('/api/users/%d', $user3->getId()));

        $this->assertResponseStatusCodeSame(204);

        $this->login(['email' => 'user6@test.com', 'password' => '123456']);
        $this->createClientWithCredentials()->request('DELETE', sprintf('/api/users/%d', $user4->getId()));

        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonContains([
            "detail" => "Access Denied.",
            "status" => 403
        ]);

        $this->login(['email' => 'user5@test.com', 'password' => '123456']);
        $this->createClientWithCredentials()->request('DELETE', sprintf('/api/users/%d', $user5->getId()));

        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonContains([
            "detail" => "Access Denied.",
            "status" => 403
        ]);
    }
}
