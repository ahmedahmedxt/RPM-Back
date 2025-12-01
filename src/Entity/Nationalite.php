<?php

namespace App\Entity;

use App\Repository\NationaliteRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: NationaliteRepository::class)]
class Nationalite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $nationaliteLibelle = null;

    #[ORM\OneToMany(targetEntity: Collaborateur::class, mappedBy: 'nationalite', cascade: ["persist","remove"])]
    private Collection $collaborateurs;

    public function __construct()
    {
        $this->collaborateurs = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNationaliteLibelle(): ?string
    {
        return $this->nationaliteLibelle;
    }

    public function setNationaliteLibelle(?string $nationaliteLibelle): static
    {
        $this->nationaliteLibelle = $nationaliteLibelle;
        return $this;
    }

    public function getCollaborateurs(): Collection
    {
        return $this->collaborateurs;
    }

    public function addCollaborateur(Collaborateur $collaborateur): self
    {
        if (!$this->collaborateurs->contains($collaborateur)) {
            $this->collaborateurs[] = $collaborateur;
            $collaborateur->setNationalite($this);
        }
        return $this;
    }

    public function removeCollaborateur(Collaborateur $collaborateur): self
    {
        if ($this->collaborateurs->removeElement($collaborateur)) {
            if ($collaborateur->getNationalite() === $this) {
                $collaborateur->setNationalite(null);
            }
        }
        return $this;
    }
}