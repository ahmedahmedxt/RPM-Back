<?php

namespace App\Entity;

use App\Repository\AppelOffresPersonnelCleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: AppelOffresPersonnelCleRepository::class)]
#[ORM\Table(name: "appeloffrespersonnelcle")]
class AppelOffresPersonnelCle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "appelOffresPersonnelCleId")]
    private ?int $appelOffresPersonnelCleId = null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "appelOffresPersonnelCleIntitule")]
    private ?string $appelOffresPersonnelCleIntitule = null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "appelOffresPersonnelCleDescription")]
    private ?string $appelOffresPersonnelCleDescription = null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "appelOffresPersonnelCleNiveauEtudeMin")]
    private ?string $appelOffresPersonnelCleNiveauEtudeMin = null;

    #[ORM\Column(type: "integer", nullable: true, name: "appelOffresPersonnelCleNbrAnneeExperience")]
    private ?int $appelOffresPersonnelCleNbrAnneeExperience = null;

    #[ORM\Column(type: "integer", nullable: true, name: "appelOffresPersonnelCleCollaborateursCount", options: ["default" => 0])]
    private ?int $appelOffresPersonnelCleCollaborateursCount = 0;

    #[ORM\ManyToOne(targetEntity: NiveauEtude::class)]
    #[ORM\JoinColumn(name: "niveauEtudeId", referencedColumnName: "niveauEtudeId", nullable: true, onDelete: "SET NULL")]
    private ?NiveauEtude $niveauEtude = null;

    #[ORM\OneToMany(targetEntity: Collaborateur::class, mappedBy: "appelOffresPersonnelCle")]
    private Collection $collaborateurs;

    #[ORM\OneToMany(mappedBy: 'appelOffresPersonnelCle', targetEntity: AppelOffresPersonnelCleAppelOffres::class, cascade: ['persist', 'remove'])]
    private Collection $appelOffresPersonnelCleAppelOffres;

    public function __construct()
    {
        $this->collaborateurs = new ArrayCollection();
        $this->appelOffresPersonnelCleAppelOffres = new ArrayCollection();
    }

    public function getAppelOffresPersonnelCleId(): ?int
    {
        return $this->appelOffresPersonnelCleId;
    }

    public function getAppelOffresPersonnelCleIntitule(): ?string
    {
        return $this->appelOffresPersonnelCleIntitule;
    }

    public function setAppelOffresPersonnelCleIntitule(?string $appelOffresPersonnelCleIntitule): void
    {
        $this->appelOffresPersonnelCleIntitule = $appelOffresPersonnelCleIntitule;
    }

    public function getAppelOffresPersonnelCleDescription(): ?string
    {
        return $this->appelOffresPersonnelCleDescription;
    }

    public function setAppelOffresPersonnelCleDescription(?string $appelOffresPersonnelCleDescription): void
    {
        $this->appelOffresPersonnelCleDescription = $appelOffresPersonnelCleDescription;
    }

    public function getAppelOffresPersonnelCleNiveauEtudeMin(): ?string
    {
        return $this->appelOffresPersonnelCleNiveauEtudeMin;
    }

    public function setAppelOffresPersonnelCleNiveauEtudeMin(?string $appelOffresPersonnelCleNiveauEtudeMin): void
    {
        $this->appelOffresPersonnelCleNiveauEtudeMin = $appelOffresPersonnelCleNiveauEtudeMin;
    }

    public function getAppelOffresPersonnelCleNbrAnneeExperience(): ?int
    {
        return $this->appelOffresPersonnelCleNbrAnneeExperience;
    }

    public function setAppelOffresPersonnelCleNbrAnneeExperience(?int $appelOffresPersonnelCleNbrAnneeExperience): void
    {
        $this->appelOffresPersonnelCleNbrAnneeExperience = $appelOffresPersonnelCleNbrAnneeExperience;
    }

    public function getAppelOffresPersonnelCleCollaborateursCount(): ?int
    {
        return $this->appelOffresPersonnelCleCollaborateursCount ?? 0;
    }

    public function setAppelOffresPersonnelCleCollaborateursCount(?int $appelOffresPersonnelCleCollaborateursCount): self
    {
        $this->appelOffresPersonnelCleCollaborateursCount = $appelOffresPersonnelCleCollaborateursCount ?? 0;
        return $this;
    }

    public function getNiveauEtude(): ?NiveauEtude
    {
        return $this->niveauEtude;
    }

    public function setNiveauEtude(?NiveauEtude $niveauEtude): void
    {
        $this->niveauEtude = $niveauEtude;
    }

    public function getCollaborateurs(): Collection
    {
        return $this->collaborateurs;
    }

    public function addCollaborateur(Collaborateur $collaborateur): self
    {
        if (!$this->collaborateurs->contains($collaborateur)) {
            $this->collaborateurs->add($collaborateur);
            $collaborateur->setAppelOffresPersonnelCle($this);
        }

        return $this;
    }

    public function removeCollaborateur(Collaborateur $collaborateur): self
    {
        if ($this->collaborateurs->removeElement($collaborateur)) {
            if ($collaborateur->getAppelOffresPersonnelCle() === $this) {
                $collaborateur->setAppelOffresPersonnelCle(null);
            }
        }

        return $this;
    }

    public function getAppelOffresPersonnelCleAppelOffres(): Collection
    {
        return $this->appelOffresPersonnelCleAppelOffres;
    }

    public function addAppelOffresPersonnelCleAppelOffres(AppelOffresPersonnelCleAppelOffres $item): self
    {
        if (!$this->appelOffresPersonnelCleAppelOffres->contains($item)) {
            $this->appelOffresPersonnelCleAppelOffres->add($item);
            $item->setAppelOffresPersonnelCle($this);
        }
        return $this;
    }

    public function removeAppelOffresPersonnelCleAppelOffres(AppelOffresPersonnelCleAppelOffres $item): self
    {
        if ($this->appelOffresPersonnelCleAppelOffres->removeElement($item)) {
            if ($item->getAppelOffresPersonnelCle() === $this) {
                $item->setAppelOffresPersonnelCle(null);
            }
        }
        return $this;
    }
}