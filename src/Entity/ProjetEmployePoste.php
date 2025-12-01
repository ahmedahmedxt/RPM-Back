<?php

namespace App\Entity;

use App\Repository\ProjetEmployePosteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjetEmployePosteRepository::class)]
class ProjetEmployePoste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    private ?string $duree = null;
    
    #[ORM\ManyToOne(targetEntity: Collaborateur::class)]
    #[ORM\JoinColumn(name: "collaborateurId", referencedColumnName: "collaborateurId", nullable: true)]
    private ?Collaborateur $collaborateur = null;

    #[ORM\ManyToOne(targetEntity: Projet::class)]
    #[ORM\JoinColumn(name: "projetId", referencedColumnName: "id", nullable: true)]
    private ?Projet $projet = null;

    #[ORM\ManyToOne(targetEntity: Poste::class)]
    #[ORM\JoinColumn(name: "posteId", referencedColumnName: "id", nullable: true)]
    private ?Poste $poste = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(?string $duree): self
    {
        $this->duree = $duree;
        return $this;
    }

    public function getCollaborateur(): ?Collaborateur
    {
        return $this->collaborateur;
    }

    public function setCollaborateur(?Collaborateur $collaborateur): self
    {
        $this->collaborateur = $collaborateur;
        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;
        return $this;
    }

    public function getPoste(): ?Poste
    {
        return $this->poste;
    }

    public function setPoste(?Poste $poste): self
    {
        $this->poste = $poste;
        return $this;
    }
}