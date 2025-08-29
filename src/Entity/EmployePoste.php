<?php

namespace App\Entity;

use App\Repository\EmployePosteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\OneToMany(targetEntity: Employe::class, mappedBy: 'employePoste')]
    private Collection $employes;

    public function __construct()
    {
        $this->employes = new ArrayCollection();
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
            $employe->setEmployePoste($this);
        }

        return $this;
    }

    public function removeEmploye(Employe $employe): static
    {
        if ($this->employes->removeElement($employe)) {
            // set the owning side to null (unless already changed)
            if ($employe->getEmployePoste() === $this) {
                $employe->setEmployePoste(null);
            }
        }

        return $this;
    }
}
