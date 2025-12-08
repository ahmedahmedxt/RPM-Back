<?php

namespace App\Entity;

use App\Repository\ReferenceCollaborateurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferenceCollaborateurRepository::class)]
class ReferenceCollaborateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $referenceCollaborateurid = null;

    #[ORM\Column(type: "date", nullable: true, name: "referenceCollaborateurDuree")]
    private $referenceCollaborateurDuree;

    #[ORM\ManyToOne(targetEntity: Collaborateur::class)]
    #[ORM\JoinColumn(name: "collaborateurId", referencedColumnName: "collaborateurId", nullable: true)]
    private ?Collaborateur $collaborateur = null;

    public function getId(): ?int
    {
        return $this->ireferenceCollaborateurid;
    }

    public function getCollaborateur(): ?Collaborateur
    {
        return $this->collaborateur;
    }

    public function setCollaborateur(?Collaborateur $collaborateur): static
    {
        $this->collaborateur = $collaborateur;
        return $this;
    }
}