<?php

namespace App\Entity;

use App\Repository\ReferenceEmployeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferenceEmployeRepository::class)]
class ReferenceEmploye
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Collaborateur::class)]
    #[ORM\JoinColumn(name: "collaborateurId", referencedColumnName: "collaborateurId", nullable: true)]
    private ?Collaborateur $collaborateur = null;

    public function getId(): ?int
    {
        return $this->id;
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