<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['user.read']],
    denormalizationContext: ['groups' => ['user.write']],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity('email', message: 'This email already exists!')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_COMPANY_ADMIN = 'ROLE_COMPANY_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public const ROLES = [
        self::ROLE_USER,
        self::ROLE_COMPANY_ADMIN,
        self::ROLE_SUPER_ADMIN
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user.read', 'user.write'])]
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
    #[Groups(['user.read', 'user.write'])]
    #[ApiProperty(default: 'Jane')]
    private ?string $name = null;

    /**
     * @var string The user's email
     */
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Sequentially([
        new Assert\NotBlank,
        new Assert\Email
    ])]
    #[Groups(['user.read', 'user.write'])]
    #[ApiProperty(example: 'jane@doe.com')]
    private ?string $email = null;

    /**
     * @var string The user's role
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Sequentially([
        new Assert\NotBlank,
        new Assert\Choice(
            choices: self::ROLES,
            message: 'This is not a valid user\'s role.'
        )
    ])]
    #[Groups(['user.read', 'user.write'])]
    #[ApiProperty(example: self::ROLE_USER)]
    private ?string $role = null;

    /**
     * @var string The user's plain password
     */
    #[Assert\NotBlank]
    #[Groups(['user.write'])]
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
    #[Groups(['user.read', 'user.write'])]
    #[ApiProperty(example: '/api/companies/1')]
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
