<?php

namespace App\Entity;

use App\Repository\OrganismeDemandeurRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrganismeDemandeurRepository::class)]
class OrganismeDemandeur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $organismeDemandeurLibelle = null;

    #[ORM\OneToMany(targetEntity: AppelOffre::class, mappedBy: 'organismeDemandeur')]
    private Collection $appelOffres;

    public function __construct()
    {
        $this->appelOffres = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganismeDemandeurLibelle(): ?string
    {
        return $this->organismeDemandeurLibelle;
    }

    public function setOrganismeDemandeurLibelle(?string $organismeDemandeurLibelle): static
    {
        $this->organismeDemandeurLibelle = $organismeDemandeurLibelle;
        return $this;
    }

    /**
     * @return Collection|AppelOffre[]
     */
    public function getAppelOffres(): Collection
    {
        return $this->appelOffres;
    }

    public function addAppelOffre(AppelOffre $appelOffre): self
    {
        if (!$this->appelOffres->contains($appelOffre)) {
            $this->appelOffres[] = $appelOffre;
            $appelOffre->setOrganismeDemandeur($this);
        }

        return $this;
    }

    public function removeAppelOffre(AppelOffre $appelOffre): self
    {
        if ($this->appelOffres->removeElement($appelOffre)) {
            // set the owning side to null (unless already changed)
            if ($appelOffre->getOrganismeDemandeur() === $this) {
                $appelOffre->setOrganismeDemandeur(null);
            }
        }

        return $this;
    }
}
