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

    #[ORM\OneToMany(targetEntity: CollaborateurEducation::class, mappedBy: 'typeDiplome')]
    private Collection $collaborateurEducation;

    public function __construct()
    {
        $this->collaborateurEducation = new ArrayCollection();
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
     * @return Collection<int, CollaborateurEducation>
     */
    public function getCollaborateurEducation(): Collection
    {
        return $this->collaborateurEducation;
    }

    public function addCollaborateurEducation(CollaborateurEducation $collaborateurEducation): static
    {
        if (!$this->collaborateurEducation->contains($collaborateurEducation)) {
            $this->collaborateurEducation->add($collaborateurEducation);
            $collaborateurEducation->setTypeDiplome($this);
        }

        return $this;
    }

    public function removeCollaborateurEducation(CollaborateurEducation $collaborateurEducation): static
    {
        if ($this->collaborateurEducation->removeElement($collaborateurEducation)) {
            // set the owning side to null (unless already changed)
            if ($collaborateurEducation->getTypeDiplome() === $this) {
                $collaborateurEducation->setTypeDiplome(null);
            }
        }

        return $this;
    }
}