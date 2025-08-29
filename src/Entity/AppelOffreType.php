<?php

namespace App\Entity;

use App\Repository\AppelOffreTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppelOffreTypeRepository::class)]
class AppelOffreType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $appelOffreType;

    #[ORM\OneToMany(targetEntity: AppelOffre::class, mappedBy: 'appelOffreType')]
    private Collection $appelOffres;


    public function __construct()
    {
        $this->appelOffres = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppelOffreType(): ?string
    {
        return $this->appelOffreType;
    }

    public function setAppelOffreType(?string $appelOffreType): static
    {
        $this->appelOffreType = $appelOffreType;

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
            $appelOffre->setAppelOffreType($this);
        }

        return $this;
    }

    public function removeAppelOffre(AppelOffre $appelOffre): self
    {
        if ($this->appelOffres->removeElement($appelOffre)) {
            // set the owning side to null (unless already changed)
            if ($appelOffre->getAppelOffreType() === $this) {
                $appelOffre->setAppelOffreType(null);
            }
        }

        return $this;
    }
}
