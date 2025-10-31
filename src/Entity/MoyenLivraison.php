<?php

namespace App\Entity;

use App\Repository\MoyenLivraisonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoyenLivraisonRepository::class)]
#[ORM\Table(name: 'moyen_livraison')]
class MoyenLivraison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'moyenLivraisonId', type: 'integer')]
    private ?int $moyenLivraisonId = null;

    #[ORM\Column(name: 'moyenLivraisonLibelle', type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $moyenLivraisonLibelle = null;

    #[ORM\Column(name: 'moyenLivraisonShort', type: 'string', length: 10, nullable: false)]
    private ?string $moyenLivraisonShort = '';

    // Relation inverse vers AppelOffres: la propriété côté AppelOffres s'appelle "appelOffresMoyenLivraisonId"
    #[ORM\OneToMany(mappedBy: 'appelOffresMoyenLivraisonId', targetEntity: AppelOffres::class)]
    private Collection $appelOffres;

    public function __construct()
    {
        $this->appelOffres = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) ($this->moyenLivraisonLibelle ?? '');
    }

    // Getters/Setters

    public function getMoyenLivraisonId(): ?int
    {
        return $this->moyenLivraisonId;
    }

    public function getMoyenLivraisonLibelle(): ?string
    {
        return $this->moyenLivraisonLibelle;
    }

    public function setMoyenLivraisonLibelle(?string $moyenLivraisonLibelle): self
    {
        $this->moyenLivraisonLibelle = $moyenLivraisonLibelle;
        return $this;
    }

    public function getMoyenLivraisonShort(): ?string
    {
        return $this->moyenLivraisonShort;
    }

    public function setMoyenLivraisonShort(string $moyenLivraisonShort): self
    {
        $this->moyenLivraisonShort = $moyenLivraisonShort;
        return $this;
    }

    /**
     * @return Collection<int, AppelOffres>
     */
    public function getAppelOffres(): Collection
    {
        return $this->appelOffres;
    }

    public function addAppelOffres(AppelOffres $appelOffres): self
    {
        if (!$this->appelOffres->contains($appelOffres)) {
            $this->appelOffres->add($appelOffres);
            $appelOffres->setAppelOffresMoyenLivraisonId($this);
        }
        return $this;
    }

    public function removeAppelOffres(AppelOffres $appelOffres): self
    {
        if ($this->appelOffres->removeElement($appelOffres)) {
            if ($appelOffres->getAppelOffresMoyenLivraisonId() === $this) {
                $appelOffres->setAppelOffresMoyenLivraisonId(null);
            }
        }
        return $this;
    }
}