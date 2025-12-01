<?php

namespace App\Entity;

use App\Repository\EmployeLangueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeLangueRepository::class)]
#[ORM\Table(name: 'employelangue')]
class EmployeLangue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'employeLangueId')]
    private ?int $employeLangueId = null;

    #[ORM\Column(name: "employeeLangueLue", nullable: true)]
    private ?int $employeeLangueLue = null;

    #[ORM\Column(name: "employeeLangueEcrite", nullable: true)]
    private ?int $employeeLangueEcrite = null;

    #[ORM\Column(name: "employeeLangueParlee",nullable: true)]
    private ?int $employeeLangueParlee = null;

    #[ORM\ManyToOne(inversedBy: 'employeLangues')]
    #[ORM\JoinColumn(name: "employeLangueNiveauId",referencedColumnName: "employeLangueNiveauId")]
    private ?EmployeLangueNiveau $employeLangueNiveauId = null;

    public function getEmployeLangueId(): ?int
    {
        return $this->employeLangueId;
    }

    public function getEmployeeLangueLue(): ?int
    {
        return $this->employeeLangueLue;
    }

    public function setEmployeeLangueLue(?int $employeeLangueLue): static
    {
        $this->employeeLangueLue = $employeeLangueLue;
        return $this;
    }

    public function getEmployeeLangueEcrite(): ?int
    {
        return $this->employeeLangueEcrite;
    }

    public function setEmployeeLangueEcrite(?int $employeeLangueEcrite): static
    {
        $this->employeeLangueEcrite = $employeeLangueEcrite;
        return $this;
    }

    public function getEmployeeLangueParlee(): ?int
    {
        return $this->employeeLangueParlee;
    }

    public function setEmployeeLangueParlee(?int $employeeLangueParlee): static
    {
        $this->employeeLangueParlee = $employeeLangueParlee;
        return $this;
    }

    public function getEmployeLangueNiveauId(): ?EmployeLangueNiveau
    {
        return $this->employeLangueNiveauId;
    }

    public function setEmployeLangueNiveauId(?EmployeLangueNiveau $employeLangueNiveauId): static
    {
        $this->employeLangueNiveauId = $employeLangueNiveauId;
        return $this;
    }
}