<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    public const CREATE = 'USER_CREATE';

    public function __construct(private readonly Security $security)
    {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                if ($this->security->isGranted(User::ROLE_SUPER_ADMIN)) {
                    return true;
                }

                // the company admin can only create the new user with ROLE_USER for their own company.
                if (
                    $this->security->isGranted(User::ROLE_COMPANY_ADMIN)
                    && $user->getCompany() === $subject->getCompany()
                    && User::ROLE_USER === $subject->getRole()
                ) {
                    return true;
                }
                break;
        }

        return false;
    }
}
