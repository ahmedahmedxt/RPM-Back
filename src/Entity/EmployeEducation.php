<?php

namespace App\Entity;

use App\Repository\EmployeEducationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeEducationRepository::class)]
#[ORM\Table(name: "employeeducation")]
class EmployeEducation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "employeEducationId")]
    private ?int $employeEducationId = null;

    #[ORM\Column(name: "employeEducationNatureEtudes", length: 254)]
    #[Assert\NotBlank]
    private ?string $employeEducationNatureEtudes = null;

    #[ORM\Column(name: "employeEducationEtablissement", length: 254)]
    #[Assert\NotBlank]
    private ?string $employeEducationEtablissement = null;

    #[ORM\Column(name: "employeEducationAnneeObtention", type: "date")]
    #[Assert\NotBlank]
    private  ?\DateTimeInterface  $employeEducationAnneeObtention = null;

    #[ORM\ManyToOne(targetEntity: Employe::class)]
    #[Assert\NotBlank]
    #[ORM\JoinColumn(name: "employeId", referencedColumnName: "employeId")]
    private ?Employe $employe;

    #[ORM\ManyToOne(inversedBy: 'employeEducation')]
    #[ORM\JoinColumn(name: "typeDiplomeId", referencedColumnName: "typeDiplomeId")]
    private ?TypeDiplome $typeDiplome = null;


    public function getEmployeEducationId(): ?int
    {
        return $this->employeEducationId;
    }

    public function getEmployeEducationNatureEtudes(): ?string
    {
        return $this->employeEducationNatureEtudes;
    }

    public function setEmployeEducationNatureEtudes(?string $employeEducationNatureEtudes): static
    {
        $this->employeEducationNatureEtudes = $employeEducationNatureEtudes;

        return $this;
    }

    public function getEmployeEducationEtablissement(): ?string
    {
        return $this->employeEducationEtablissement;
    }

    public function setEmployeEducationEtablissement(?string $employeEducationEtablissement): static
    {
        $this->employeEducationEtablissement = $employeEducationEtablissement;

        return $this;
    }

    public function getEmployeEducationAnneeObtention(): ?\DateTimeInterface
    {
        return $this->employeEducationAnneeObtention;
    }
    
    public function setEmployeEducationAnneeObtention(?\DateTimeInterface $employeEducationAnneeObtention): static
    {
        $this->employeEducationAnneeObtention = $employeEducationAnneeObtention;
    
        return $this;
    }
    
    public function getEmploye(): ?Employe
    {
        return $this->employe;
    }

    public function setEmploye(?Employe $employe): self
    {
        $this->employe = $employe;

        return $this;
    }

    public function getTypeDiplome(): ?TypeDiplome
    {
        return $this->typeDiplome;
    }

    public function setTypeDiplome(?TypeDiplome $typeDiplome): static
    {
        $this->typeDiplome = $typeDiplome;

        return $this;
    }
}
