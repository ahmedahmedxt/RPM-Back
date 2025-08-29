<?php

namespace App\Entity;

use App\Repository\EmployeLangueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FontLib\Table\Type\name;

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

    #[ORM\OneToMany(targetEntity: Employe::class, mappedBy: 'employeLangue')]
    private Collection $employes;

    public function __construct()
    {
        $this->employes = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Employe>
     */
    public function getEmployes(): Collection
    {
        return $this->employes;
    }

    public function addEmploye(Employe $employe): static
    {
        if (!$this->employes->contains($employe)) {
            $this->employes->add($employe);
            $employe->setEmployeLangue($this);
        }

        return $this;
    }

    public function removeEmploye(Employe $employe): static
    {
        if ($this->employes->removeElement($employe)) {
            // set the owning side to null (unless already changed)
            if ($employe->getEmployeLangue() === $this) {
                $employe->setEmployeLangue(null);
            }
        }

        return $this;
    }
}
