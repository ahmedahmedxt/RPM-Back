<?php

namespace App\Entity;

use App\Repository\PosteRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: PosteRepository::class)]
class Poste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $posteNom = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosteNom(): ?string
    {
        return $this->posteNom;
    }

    public function setPosteNom(string $posteNom): static
    {
        $this->posteNom = $posteNom;
        return $this;
    }
}