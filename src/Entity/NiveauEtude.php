<?php

namespace App\Entity;

use App\Repository\NiveauEtudeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\AppelOffresPersonnelCle;

#[ORM\Entity(repositoryClass: NiveauEtudeRepository::class)]
#[ORM\Table(name: "niveauetude")]
class NiveauEtude
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "niveauEtudeId")]
    private ?int $niveauEtudeId = null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "niveauEtudeLibelle")]
    private ?string $niveauEtudeLibelle = null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "niveauEtudeDescription")]
    private ?string $niveauEtudeDescription = null;

    #[ORM\OneToMany(targetEntity: AppelOffresPersonnelCle::class, mappedBy: "niveauEtude")]
    private Collection $appelOffresPersonnelCles;

    public function __construct()
    {
        $this->appelOffresPersonnelCles = new ArrayCollection();
    }

    public function getNiveauEtudeId(): ?int
    {
        return $this->niveauEtudeId;
    }

    public function getNiveauEtudeLibelle(): ?string
    {
        return $this->niveauEtudeLibelle;
    }

    public function setNiveauEtudeLibelle(?string $niveauEtudeLibelle): void
    {
        $this->niveauEtudeLibelle = $niveauEtudeLibelle;
    }

    public function getNiveauEtudeDescription(): ?string
    {
        return $this->niveauEtudeDescription;
    }

    public function setNiveauEtudeDescription(?string $niveauEtudeDescription): void
    {
        $this->niveauEtudeDescription = $niveauEtudeDescription;
    }

    public function getAppelOffresPersonnelCles(): Collection
    {
        return $this->appelOffresPersonnelCles;
    }

    public function addAppelOffresPersonnelCle(AppelOffresPersonnelCle $appelOffresPersonnelCle): self
    {
        if (!$this->appelOffresPersonnelCles->contains($appelOffresPersonnelCle)) {
            $this->appelOffresPersonnelCles->add($appelOffresPersonnelCle);
            $appelOffresPersonnelCle->setNiveauEtude($this);
        }

        return $this;
    }

    public function removeAppelOffresPersonnelCle(AppelOffresPersonnelCle $appelOffresPersonnelCle): self
    {
        if ($this->appelOffresPersonnelCles->removeElement($appelOffresPersonnelCle)) {
            if ($appelOffresPersonnelCle->getNiveauEtude() === $this) {
                $appelOffresPersonnelCle->setNiveauEtude(null);
            }
        }

        return $this;
    }
}