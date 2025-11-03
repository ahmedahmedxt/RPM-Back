<?php

namespace App\Entity;

use App\Repository\PaysRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\AppelOffres;
use App\Entity\Lieu;
use App\Entity\Client;
use App\Entity\EmployeExperience;
use App\Entity\OrganismeDemandeur;
use App\Entity\Continent;

#[ORM\Entity(repositoryClass: PaysRepository::class)]
#[ORM\Table(name: 'pays')]
class Pays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'paysId', type: 'integer')]
    private ?int $paysId = null;

    #[ORM\Column(name: 'paysLibelle', length: 254, unique: true)]
    #[Assert\NotBlank(message: 'Le libellé du pays est obligatoire.')]
    private ?string $paysLibelle = null;

    #[ORM\Column(name: 'paysCapitale', length: 254)]
    #[Assert\NotBlank(message: 'La capitale du pays est obligatoire.')]
    private ?string $paysCapitale = null;

    #[ORM\ManyToOne(targetEntity: Continent::class)]
    #[ORM\JoinColumn(name: 'continentId', referencedColumnName: 'continentId', nullable: true, onDelete: 'SET NULL')]
    private ?Continent $continent = null;

    #[ORM\OneToMany(targetEntity: Lieu::class, mappedBy: 'pays', cascade: ['persist', 'remove'])]
    private Collection $lieux;

    #[ORM\OneToMany(targetEntity: AppelOffres::class, mappedBy: 'appelOffresPaysId', cascade: ['persist', 'remove'])]
    private Collection $appelOffres;

    #[ORM\OneToMany(targetEntity: EmployeExperience::class, mappedBy: 'pays', cascade: ['persist', 'remove'])]
    private Collection $employeExperiences;

    #[ORM\OneToMany(targetEntity: OrganismeDemandeur::class, mappedBy: 'pays')]
    private Collection $organismesDemandeurs;

    public function __construct()
    {
        $this->lieux = new ArrayCollection();
        $this->appelOffres = new ArrayCollection();
        $this->employeExperiences = new ArrayCollection();
        $this->organismesDemandeurs = new ArrayCollection();
    }


    public function getPaysId(): ?int
    {
        return $this->paysId;
    }

    public function getPaysLibelle(): ?string
    {
        return $this->paysLibelle;
    }

    public function setPaysLibelle(string $paysLibelle): static
    {
        $this->paysLibelle = $paysLibelle;
        return $this;
    }

    public function getPaysCapitale(): ?string
    {
        return $this->paysCapitale;
    }

    public function setPaysCapitale(string $paysCapitale): static
    {
        $this->paysCapitale = $paysCapitale;
        return $this;
    }

    public function getContinent(): ?Continent
    {
        return $this->continent;
    }

    public function setContinent(?Continent $continent): static
    {
        $this->continent = $continent;
        return $this;
    }

    /**
     * @return Collection<int, Lieu>
     */
    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    public function addLieu(Lieu $lieu): self
    {
        if (!$this->lieux->contains($lieu)) {
            $this->lieux->add($lieu);
            $lieu->setPays($this);
        }
        return $this;
    }

    public function removeLieu(Lieu $lieu): self
    {
        if ($this->lieux->removeElement($lieu)) {
            if ($lieu->getPays() === $this) {
                $lieu->setPays(null);
            }
        }
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
            $appel->setAppelOffresPaysId($this);
        }
        return $this;
    }

    public function removeAppelOffres(AppelOffres $appel): self
    {
        if ($this->appelOffres->removeElement($appel)) {
            if ($appel->getAppelOffresPaysId() === $this) {
                $appel->setAppelOffresPaysId(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, EmployeExperience>
     */
    public function getEmployeExperiences(): Collection
    {
        return $this->employeExperiences;
    }

    public function addEmployeExperience(EmployeExperience $employeExperience): static
    {
        if (!$this->employeExperiences->contains($employeExperience)) {
            $this->employeExperiences->add($employeExperience);
            $employeExperience->setPays($this);
        }
        return $this;
    }

    public function removeEmployeExperience(EmployeExperience $employeExperience): static
    {
        if ($this->employeExperiences->removeElement($employeExperience)) {
            if ($employeExperience->getPays() === $this) {
                $employeExperience->setPays(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, OrganismeDemandeur>
     */
    public function getOrganismesDemandeurs(): Collection
    {
        return $this->organismesDemandeurs;
    }

    public function addOrganismesDemandeur(OrganismeDemandeur $organisme): self
    {
        if (!$this->organismesDemandeurs->contains($organisme)) {
            $this->organismesDemandeurs->add($organisme);
            $organisme->setPays($this);
        }
        return $this;
    }

    public function removeOrganismesDemandeur(OrganismeDemandeur $organisme): self
    {
        if ($this->organismesDemandeurs->removeElement($organisme)) {
            if ($organisme->getPays() === $this) {
                $organisme->setPays(null);
            }
        }
        return $this;
    }
}
