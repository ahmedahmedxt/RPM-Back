<?php

namespace App\Entity;

use App\Repository\TypeDiplomeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeDiplomeRepository::class)]
#[ORM\Table(name: "typediplome")]
class TypeDiplome
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "typeDiplomeId")]
    private ?int $typeDiplomeId = null;

    #[ORM\Column(name: "typeDiplomeLibelle", length: 254, nullable: true)]
    private ?string $typeDiplomeLibelle = null;

    #[ORM\OneToMany(targetEntity: EmployeEducation::class, mappedBy: 'typeDiplome')]
    private Collection $employeEducation;

    public function __construct()
    {
        $this->employeEducation = new ArrayCollection();
    }

    public function getTypeDiplomeId(): ?int
    {
        return $this->typeDiplomeId;
    }

    public function getTypeDiplomeLibelle(): ?string
    {
        return $this->typeDiplomeLibelle;
    }

    public function setTypeDiplomeLibelle(?string $typeDiplomeLibelle): static
    {
        $this->typeDiplomeLibelle = $typeDiplomeLibelle;

        return $this;
    }

    /**
     * @return Collection<int, EmployeEducation>
     */
    public function getEmployeEducation(): Collection
    {
        return $this->employeEducation;
    }

    public function addEmployeEducation(EmployeEducation $employeEducation): static
    {
        if (!$this->employeEducation->contains($employeEducation)) {
            $this->employeEducation->add($employeEducation);
            $employeEducation->setTypeDiplome($this);
        }

        return $this;
    }

    public function removeEmployeEducation(EmployeEducation $employeEducation): static
    {
        if ($this->employeEducation->removeElement($employeEducation)) {
            // set the owning side to null (unless already changed)
            if ($employeEducation->getTypeDiplome() === $this) {
                $employeEducation->setTypeDiplome(null);
            }
        }

        return $this;
    }
}
