<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CompanyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    normalizationContext: ['groups' => ['company.read']],
    denormalizationContext: ['groups' => ['company.write']],
)]
#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[UniqueEntity(fields: 'name', message: 'This company already exists!')]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['company.read', 'company.write'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\Length(min: 5, max: 100)]
    #[Groups(['company.read', 'company.write'])]
    private ?string $name = null;

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
}
