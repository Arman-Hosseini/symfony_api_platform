<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final class ApiUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security)
    {}

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->apply($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->apply($resourceClass, $queryBuilder);
    }

    private function apply($resourceClass, $queryBuilder)
    {
        if (User::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        /** @var User $user */
        $user = $this->security->getUser();

        if (null === $user) {
            return;
        }

        if ($this->security->isGranted(User::ROLE_SUPER_ADMIN)) {
            return;
        }

        // the company admin and users can only see the users from their own company
        if ($this->security->isGranted(User::ROLE_COMPANY_ADMIN) ||
            $this->security->isGranted(User::ROLE_USER)) {
            $queryBuilder->andWhere(sprintf('%s.company = :company', $rootAlias));
            $queryBuilder->setParameter('company', $user->getCompany());
        }
    }
}
