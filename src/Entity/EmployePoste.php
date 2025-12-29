<?php

namespace App\Entity;

use App\Repository\EmployePosteRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: EmployePosteRepository::class)]
#[ORM\Table(name: 'employeposte')]
class EmployePoste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "employePosteId")]
    private ?int $employePosteId = null;

    #[ORM\Column(name: "employePosteLibelle", length: 254, nullable: true)]
    private ?string $employePosteLibelle = null;

    #[ORM\OneToMany(mappedBy: 'employePoste', targetEntity: ReferenceCollaborateur::class)]
    private Collection $referenceCollaborateurs;

    public function __construct()
    {
        $this->referenceCollaborateurs = new ArrayCollection();
    }

    public function getReferenceCollaborateurs(): Collection
    {
        return $this->referenceCollaborateurs;
    }

    public function getEmployePosteId(): ?int
    {
        return $this->employePosteId;
    }

    public function getEmployePosteLibelle(): ?string
    {
        return $this->employePosteLibelle;
    }

    public function setEmployePosteLibelle(?string $employePosteLibelle): static
    {
        $this->employePosteLibelle = $employePosteLibelle;
        return $this;
    }
}
