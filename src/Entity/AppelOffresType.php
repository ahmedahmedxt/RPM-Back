<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'appel_offres_type')]
class AppelOffresType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'appelOffresTypeId', type: 'integer')]
    private ?int $appelOffresTypeId = null;

    #[ORM\Column(name: 'appelOffresTypeLibelle', type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $appelOffresTypeLibelle = null;

    #[ORM\Column(name: 'appelOffresTypeShort', type: 'string', length: 50, nullable: true)]
    private ?string $appelOffresTypeShort = null;

    // Relation inverse vers AppelOffres: la propriété côté AppelOffres s'appelle "appelOffresTypeId"
    #[ORM\OneToMany(targetEntity: AppelOffres::class, mappedBy: 'appelOffresTypeId')]
    private Collection $appelOffres;

    public function __construct()
    {
        $this->appelOffres = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) ($this->appelOffresTypeLibelle ?? '');
    }

    // Getters / Setters

    public function getAppelOffresTypeId(): ?int
    {
        return $this->appelOffresTypeId;
    }

    public function getAppelOffresTypeLibelle(): ?string
    {
        return $this->appelOffresTypeLibelle;
    }

    public function setAppelOffresTypeLibelle(?string $libelle): self
    {
        $this->appelOffresTypeLibelle = $libelle;
        return $this;
    }

    public function getAppelOffresTypeShort(): ?string
    {
        return $this->appelOffresTypeShort;
    }

    public function setAppelOffresTypeShort(?string $short): self
    {
        $this->appelOffresTypeShort = $short;
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
            $appel->setAppelOffresTypeId($this);
        }
        return $this;
    }

    public function removeAppelOffres(AppelOffres $appel): self
    {
        if ($this->appelOffres->removeElement($appel)) {
            if ($appel->getAppelOffresTypeId() === $this) {
                $appel->setAppelOffresTypeId(null);
            }
        }
        return $this;
    }
}