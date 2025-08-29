<?php

namespace App\Entity;

use App\Repository\ContinentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContinentRepository::class)]
class Continent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "continentId")]
    private ?int $continentId = null;

    #[ORM\Column(name: "continentName",length: 254, nullable: true)]
    private ?string $continentName = null;

    public function getContinentId(): ?int
    {
        return $this->continentId;
    }

    public function getContinentName(): ?string
    {
        return $this->continentName;
    }

    public function setContinentName(?string $continentName): static
    {
        $this->continentName = $continentName;

        return $this;
    }
}
