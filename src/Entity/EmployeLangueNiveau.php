<?php

namespace App\Entity;

use App\Repository\EmployeLangueNiveauRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeLangueNiveauRepository::class)]
#[ORM\Table(name: 'employelangueniveau')]
class EmployeLangueNiveau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "employeLangueNiveauId")]
    private ?int $employeLangueNiveauId = null;

    #[ORM\Column(name: "employeLangueNiveauLibelle", length: 254, nullable: true)]
    private ?string $employeLangueNiveauLibelle = null;

    #[ORM\OneToMany(targetEntity: EmployeLangue::class, mappedBy: 'employeLangueNiveauId')]
    private Collection $employeLangues;

    public function __construct()
    {
        $this->employeLangues = new ArrayCollection();
    }

    public function getEmployeLangueNiveauId(): ?int
    {
        return $this->employeLangueNiveauId;
    }

    public function getEmployeLangueNiveauLibelle(): ?string
    {
        return $this->employeLangueNiveauLibelle;
    }

    public function setEmployeLangueNiveauLibelle(?string $employeLangueNiveauLibelle): static
    {
        $this->employeLangueNiveauLibelle = $employeLangueNiveauLibelle;

        return $this;
    }

    /**
     * @return Collection<int, EmployeLangue>
     */
    public function getEmployeLangues(): Collection
    {
        return $this->employeLangues;
    }

    public function addEmployeLangue(EmployeLangue $employeLangue): static
    {
        if (!$this->employeLangues->contains($employeLangue)) {
            $this->employeLangues->add($employeLangue);
            $employeLangue->setEmployeLangueNiveauId($this);
        }

        return $this;
    }

    public function removeEmployeLangue(EmployeLangue $employeLangue): static
    {
        if ($this->employeLangues->removeElement($employeLangue)) {
            // set the owning side to null (unless already changed)
            if ($employeLangue->getEmployeLangueNiveauId() === $this) {
                $employeLangue->setEmployeLangueNiveauId(null);
            }
        }

        return $this;
    }

}
