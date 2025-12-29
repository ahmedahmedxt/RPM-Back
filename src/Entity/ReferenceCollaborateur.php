<?php

namespace App\Entity;

use App\Repository\ReferenceCollaborateurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferenceCollaborateurRepository::class)]
#[ORM\Table(name: 'reference_collaborateur')]
class ReferenceCollaborateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'referenceCollaborateurId')]
    private ?int $referenceCollaborateurId = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'referenceCollaborateurDuree')]
    private ?int $referenceCollaborateurDuree = null;

    #[ORM\ManyToOne(targetEntity: Collaborateur::class)]
    #[ORM\JoinColumn(name: 'collaborateurId', referencedColumnName: 'collaborateurId', nullable: true)]
    private ?Collaborateur $collaborateur = null;

    #[ORM\ManyToOne(targetEntity: Reference::class, inversedBy: 'referenceCollaborateurs')]
    #[ORM\JoinColumn(name: 'reference_id', referencedColumnName: 'referenceID', nullable: false)]
    private ?Reference $reference = null;

    #[ORM\ManyToOne(targetEntity: EmployePoste::class, inversedBy: 'referenceCollaborateurs')]
    #[ORM\JoinColumn(name: 'employePosteId', referencedColumnName: 'employePosteId', nullable: false)]
    private ?EmployePoste $employePoste = null;

    public function getId(): ?int
    {
        return $this->referenceCollaborateurId;
    }

    public function getReferenceCollaborateurId(): ?int
    {
        return $this->referenceCollaborateurId;
    }

    public function getReferenceCollaborateurDuree(): ?int
    {
        return $this->referenceCollaborateurDuree;
    }

    public function setReferenceCollaborateurDuree(?int $months): static
    {
        if ($months !== null) {
            $months = max(0, (int) $months);
        }
        $this->referenceCollaborateurDuree = $months;
        return $this;
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

    public function getReference(): ?Reference
    {
        return $this->reference;
    }

    public function setReference(?Reference $reference): static
    {
        $this->reference = $reference;
        return $this;
    }

    public function getEmployePoste(): ?EmployePoste
    {
        return $this->employePoste;
    }

    public function setEmployePoste(?EmployePoste $employePoste): static
    {
        $this->employePoste = $employePoste;
        return $this;
    }
}
