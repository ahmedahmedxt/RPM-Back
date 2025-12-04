<?php

namespace App\Entity;

use App\Repository\LangueRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: LangueRepository::class)]
class Langue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $langueNom = null;

    #[ORM\Column(name: "langueCodeISO", length: 10, nullable: true)]
    private ?string $langueCodeISO = null;
    
    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getLangueNom(): ?string
    {
        return $this->langueNom;
    }

    public function setLangueNom(string $langueNom): static
    {
        $this->langueNom = $langueNom;
        return $this;
    }

    public function getLangueCodeISO(): ?string
    {
        return $this->langueCodeISO;
    }

    public function setLangueCodeISO(?string $langueCodeISO): static
    {
        $this->langueCodeISO = $langueCodeISO;
        return $this;
    }
}