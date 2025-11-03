<?php

namespace App\Entity;

use App\Repository\SecteurActiviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\OrganismeDemandeur;

#[ORM\Entity(repositoryClass: SecteurActiviteRepository::class)]
#[ORM\Table(name: 'secteur_activite')]
class SecteurActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'secteur_activite_id', type: 'integer')]
    private ?int $secteurActiviteId = null;

    // Backwards-compatible alias
    public function getId(): ?int
    {
        return $this->secteurActiviteId;
    }

    #[ORM\Column(name: 'secteurActiviteLibelle', length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Le libellé du secteur d’activité est obligatoire.')]
    private ?string $secteurActiviteLibelle = null;

    #[ORM\Column(name: 'secteurActiviteDescription', type: 'text', nullable: true)]
    private ?string $secteurActiviteDescription = null;

    // One SecteurActivite → Many OrganismeDemandeur
    #[ORM\OneToMany(mappedBy: 'secteurActivite', targetEntity: OrganismeDemandeur::class, cascade: ['persist'])]
    private Collection $organismesDemandeurs;

    public function __construct()
    {
        $this->organismesDemandeurs = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->secteurActiviteLibelle ?? 'Secteur #' . $this->secteurActiviteId;
    }

    // -------------------------
    // Getters & Setters
    // -------------------------

    public function getSecteurActiviteId(): ?int
    {
        return $this->secteurActiviteId;
    }

    public function getSecteurActiviteLibelle(): ?string
    {
        return $this->secteurActiviteLibelle;
    }

    public function setSecteurActiviteLibelle(?string $libelle): self
    {
        $this->secteurActiviteLibelle = $libelle;
        return $this;
    }

    public function getSecteurActiviteDescription(): ?string
    {
        return $this->secteurActiviteDescription;
    }

    public function setSecteurActiviteDescription(?string $description): self
    {
        $this->secteurActiviteDescription = $description;
        return $this;
    }

    /**
     * @return Collection<int, OrganismeDemandeur>
     */
    public function getOrganismesDemandeurs(): Collection
    {
        return $this->organismesDemandeurs;
    }

    public function addOrganismeDemandeur(OrganismeDemandeur $organisme): self
    {
        if (!$this->organismesDemandeurs->contains($organisme)) {
            $this->organismesDemandeurs->add($organisme);
            $organisme->setSecteurActivite($this);
        }
        return $this;
    }

    public function removeOrganismeDemandeur(OrganismeDemandeur $organisme): self
    {
        if ($this->organismesDemandeurs->removeElement($organisme)) {
            if ($organisme->getSecteurActivite() === $this) {
                $organisme->setSecteurActivite(null);
            }
        }
        return $this;
    }
}
