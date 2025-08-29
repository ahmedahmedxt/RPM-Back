<?php

namespace App\Entity;

use App\Repository\EmployeExperienceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeExperienceRepository::class)]
#[ORM\Table(name: 'employeexperience')]
class EmployeExperience
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'employeExperienceId')]
    private ?int $employeExperienceId = null;

    #[ORM\Column(name: "employeExperienceOrganismeEmployeur", length: 254, nullable: true)]
    #[Assert\NotBlank]
    private ?string $employeExperienceOrganismeEmployeur = null;

    #[ORM\Column(name: "employeExperiencePeriode", length: 254, nullable: true)]
    #[Assert\NotBlank]
    private ?string $employeExperiencePeriode = null;

    #[ORM\Column(name: "employeExperienceFonctionOccupe", length: 254, nullable: true)]
    #[Assert\NotBlank]
    private ?string $employeExperienceFonctionOccupe = null;

    #[ORM\ManyToOne(targetEntity: Employe::class, inversedBy: 'employeExperiences')]
    #[ORM\JoinColumn(name: "employeId", referencedColumnName: "employeId", nullable: false)]
    #[Assert\NotBlank]
    private ?Employe $employe = null;

    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(name: "paysId", referencedColumnName: "paysId", nullable: true)]
    private ?Pays $pays = null;

    public function getEmployeExperienceId(): ?int
    {
        return $this->employeExperienceId;
    }

    public function getEmployeExperienceOrganismeEmployeur(): ?string
    {
        return $this->employeExperienceOrganismeEmployeur;
    }

    public function setEmployeExperienceOrganismeEmployeur(?string $employeExperienceOrganismeEmployeur): static
    {
        $this->employeExperienceOrganismeEmployeur = $employeExperienceOrganismeEmployeur;

        return $this;
    }

    public function getEmployeExperiencePeriode(): ?string
    {
        return $this->employeExperiencePeriode;
    }

    public function setEmployeExperiencePeriode(?string $employeExperiencePeriode): static
    {
        $this->employeExperiencePeriode = $employeExperiencePeriode;

        return $this;
    }

    public function getEmployeExperienceFonctionOccupe(): ?string
    {
        return $this->employeExperienceFonctionOccupe;
    }

    public function setEmployeExperienceFonctionOccupe(?string $employeExperienceFonctionOccupe): static
    {
        $this->employeExperienceFonctionOccupe = $employeExperienceFonctionOccupe;

        return $this;
    }

    public function getEmploye(): ?Employe
    {
        return $this->employe;
    }

    public function setEmploye(?Employe $employe): static
    {
        $this->employe = $employe;

        return $this;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): static
    {
        $this->pays = $pays;

        return $this;
    }
}
