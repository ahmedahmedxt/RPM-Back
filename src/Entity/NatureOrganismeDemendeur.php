<?php

namespace App\Entity;

use App\Repository\NatureOrganismeDemendeurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NatureOrganismeDemendeurRepository::class)]
class NatureOrganismeDemendeur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'nature_organisme_demendeur_id', type: 'integer')]
    private ?int $natureOrganismeDemendeurId = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Le libellé de la nature de l\'organisme demandeur est obligatoire.')]
    private ?string $natureOrganismeDemendeurLibelle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $natureOrganismeDemendeurDescription = null;

    // Relation : One NatureOrganismeDemendeur → Many OrganismeDemandeur
    #[ORM\OneToMany(mappedBy: 'natureOrganismeDemendeur', targetEntity: OrganismeDemandeur::class)]
    private Collection $organismesDemandeurs;

    public function __construct()
    {
        $this->organismesDemandeurs = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->natureOrganismeDemendeurLibelle ?? 'Nature #' . $this->natureOrganismeDemendeurId;
    }

    // ------------------------
    // Getters and Setters
    // ------------------------

    public function getNatureOrganismeDemendeurId(): ?int
    {
        return $this->natureOrganismeDemendeurId;
    }

    public function getNatureOrganismeDemendeurLibelle(): ?string
    {
        return $this->natureOrganismeDemendeurLibelle;
    }

    public function setNatureOrganismeDemendeurLibelle(?string $libelle): self
    {
        $this->natureOrganismeDemendeurLibelle = $libelle;
        return $this;
    }

    public function getNatureOrganismeDemendeurDescription(): ?string
    {
        return $this->natureOrganismeDemendeurDescription;
    }

    public function setNatureOrganismeDemendeurDescription(?string $description): self
    {
        $this->natureOrganismeDemendeurDescription = $description;
        return $this;
    }

    /**
     * @return Collection|OrganismeDemandeur[]
     */
    public function getOrganismesDemandeurs(): Collection
    {
        return $this->organismesDemandeurs;
    }

    public function addOrganismeDemandeur(OrganismeDemandeur $organisme): self
    {
        if (!$this->organismesDemandeurs->contains($organisme)) {
            $this->organismesDemandeurs->add($organisme);
            $organisme->setNatureOrganismeDemendeur($this);
        }
        return $this;
    }

    public function removeOrganismeDemandeur(OrganismeDemandeur $organisme): self
    {
        if ($this->organismesDemandeurs->removeElement($organisme)) {
            if ($organisme->getNatureOrganismeDemendeur() === $this) {
                $organisme->setNatureOrganismeDemendeur(null);
            }
        }
        return $this;
    }
}
