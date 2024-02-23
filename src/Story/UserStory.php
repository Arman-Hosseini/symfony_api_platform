<?php

namespace App\Story;

use App\Entity\User;
use App\Factory\CompanyFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class UserStory extends Story
{
    public function build(): void
    {
        $this->addState('company1', CompanyFactory::createOne());
        $this->addState('company2', CompanyFactory::createOne());
        $this->addState('company3', CompanyFactory::createOne());
        CompanyFactory::createMany(3);

        $this->addToPool('user1', UserFactory::createMany(3, [
                'role' => User::ROLE_USER,
                'company' => self::getState('company1')
            ]
        ));
        $this->addToPool('user2', UserFactory::createMany(3, [
                'role' => User::ROLE_USER,
                'company' => self::getState('company2')
            ]
        ));
        UserFactory::createOne([
            'role' => User::ROLE_COMPANY_ADMIN,
            'company' => self::getState('company1')
        ]);

        UserFactory::createOne([
            'role' => User::ROLE_COMPANY_ADMIN,
            'company' => self::getState('company2')
        ]);

        UserFactory::createOne([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);
    }
}
