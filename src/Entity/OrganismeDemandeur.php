<?php

namespace App\Entity;

use App\Repository\OrganismeDemandeurRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\AppelOffres;

#[ORM\Entity(repositoryClass: OrganismeDemandeurRepository::class)]
#[ORM\Table(name: 'organisme_demandeur')]
class OrganismeDemandeur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'organismeDemandeurLibelle', length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $organismeDemandeurLibelle = null;

    // Relation inverse: côté AppelOffres la propriété s'appelle "appelOffresOrganismeDemandeurId"
    #[ORM\OneToMany(targetEntity: AppelOffres::class, mappedBy: 'appelOffresOrganismeDemandeurId')]
    private Collection $appelOffres;

    public function __construct()
    {
        $this->appelOffres = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) ($this->organismeDemandeurLibelle ?? '');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganismeDemandeurLibelle(): ?string
    {
        return $this->organismeDemandeurLibelle;
    }

    public function setOrganismeDemandeurLibelle(?string $organismeDemandeurLibelle): self
    {
        $this->organismeDemandeurLibelle = $organismeDemandeurLibelle;
        return $this;
    }

    /**
     * @return Collection<int, AppelOffres>
     */
    public function getAppelOffres(): Collection
    {
        return $this->appelOffres;
    }

    public function addAppelOffres(AppelOffres $appel): self
    {
        if (!$this->appelOffres->contains($appel)) {
            $this->appelOffres->add($appel);
            // côté AppelOffres: propriété "appelOffresOrganismeDemandeurId"
            $appel->setAppelOffresOrganismeDemandeurId($this);
        }
        return $this;
    }

    public function removeAppelOffres(AppelOffres $appel): self
    {
        if ($this->appelOffres->removeElement($appel)) {
            if ($appel->getAppelOffresOrganismeDemandeurId() === $this) {
                $appel->setAppelOffresOrganismeDemandeurId(null);
            }
        }
        return $this;
    }
}