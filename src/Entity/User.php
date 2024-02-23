<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity('email', message: 'This email already exists!')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string The user's name
     */
    #[ORM\Column(length: 100)]
    #[Assert\Sequentially([
        new Assert\Length(min: 3, max: 100),
        new Assert\Regex(pattern: '/^[A-Z]/', message: "The name must start with a uppercase letter."),
        new Assert\Regex(pattern: '/[a-zA-Z\s]$/', message: "The name must contains letters and space.")
    ])]
    private ?string $name = null;

    /**
     * @var string The user's email
     */
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Sequentially([
        new Assert\NotBlank,
        new Assert\Email
    ])]
    private ?string $email = null;

    /**
     * @var string The user's role
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $role = null;

    /**
     * @var string The user's plain password
     */
    #[Assert\NotBlank]
    private ?string $plainPassword = null;

    /**
     * @var string The user's hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Company|null The user's company
     */
    #[ORM\ManyToOne]
    #[Assert\When(
        expression: "this.getRole() === 'ROLE_SUPER_ADMIN'",
        constraints: [
            new Assert\Blank(message: 'The user with this role can\'t have a company.')
        ]
    )]
    #[Assert\When(
        expression: "this.getRole() in ['ROLE_USER', 'ROLE_COMPANY_ADMIN']",
        constraints: [
            new Assert\NotBlank(message: 'The user with this role must have a company.')
        ]
    )]
    private ?Company $company = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string Returns the identifier for this user.
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @return string[] Returns the roles granted to the user.
     */
    public function getRoles(): array
    {
        return [$this->role];
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return void Removes sensitive data from the user.
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }
}
