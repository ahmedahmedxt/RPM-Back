<?php

namespace App\Entity;

use App\Repository\OrganismeDemandeurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\AppelOffres;
use App\Entity\Pays;
use App\Entity\NatureOrganismeDemendeur;
use App\Entity\SecteurActivite;

#[ORM\Entity(repositoryClass: OrganismeDemandeurRepository::class)]
#[ORM\Table(name: 'organisme_demandeur')]
class OrganismeDemandeur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'organisme_demandeur_id', type: 'integer')]
    private ?int $organismeDemandeurId = null;

    public function getId(): ?int
    {
        return $this->organismeDemandeurId;
    }

    #[ORM\Column(name: 'organismeDemandeurLibelle', length: 255, unique: true, nullable: false)]
    #[Assert\NotBlank(message: 'Le libellé est obligatoire.')]
    private ?string $organismeDemandeurLibelle = null;

    #[ORM\Column(name: 'organismeDemandeurDescription', type: 'text', nullable: false)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    private ?string $organismeDemandeurDescription = null;

    #[ORM\Column(name: 'organismeDemandeurAcronyme', length: 100, nullable: true)]
    private ?string $organismeDemandeurAcronyme = null;

    #[ORM\Column(name: 'organismeDemandeurLogo', length: 255, nullable: true)]
    private ?string $organismeDemandeurLogo = null;

    #[ORM\Column(name: 'organismeDemandeurNomCoordinateur', length: 150, nullable: false)]
    #[Assert\NotBlank(message: 'Le nom du coordinateur est obligatoire.')]
    private ?string $organismeDemandeurNomCoordinateur = null;

    #[ORM\Column(name: 'organismeDemandeurEmailCoordinateur', length: 180, nullable: false)]
    #[Assert\NotBlank(message: 'L\'email du coordinateur est obligatoire.')]
    #[Assert\Email]
    private ?string $organismeDemandeurEmailCoordinateur = null;

    #[ORM\Column(name: 'organismeDemandeurRaisonSocial', length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'La raison sociale est obligatoire.')]
    private ?string $organismeDemandeurRaisonSocial = null;

    #[ORM\Column(name: 'organismeDemandeurRaisonSocialShort', length: 100, nullable: false)]
    private ?string $organismeDemandeurRaisonSocialShort = null;

    #[ORM\Column(name: 'organismeDemandeurAdresse', length: 500, nullable: false)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire.')]
    private ?string $organismeDemandeurAdresse = null;

    #[ORM\Column(name: 'organismeDemandeurPersonneContact1', length: 150, nullable: true)]
    private ?string $organismeDemandeurPersonneContact1 = null;

    #[ORM\Column(name: 'organismeDemandeurPersonneTelephonne1', length: 50, nullable: true)]
    private ?string $organismeDemandeurPersonneTelephonne1 = null;

    #[ORM\Column(name: 'organismeDemandeurPersonneContact2', length: 150, nullable: true)]
    private ?string $organismeDemandeurPersonneContact2 = null;

    #[ORM\Column(name: 'organismeDemandeurTelephone2', length: 50, nullable: true)]
    private ?string $organismeDemandeurTelephone2 = null;

    #[ORM\Column(name: 'organismeDemandeurEmail2', length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $organismeDemandeurEmail2 = null;

    #[ORM\Column(name: 'organismeDemandeurPersonneContact3', length: 150, nullable: true)]
    private ?string $organismeDemandeurPersonneContact3 = null;

    #[ORM\Column(name: 'organismeDemandeurTelephone3', length: 50, nullable: true)]
    private ?string $organismeDemandeurTelephone3 = null;

    #[ORM\Column(name: 'organismeDemandeurEmail3', length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $organismeDemandeurEmail3 = null;

    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(name: 'paysId', referencedColumnName: 'paysId', nullable: true, onDelete: 'SET NULL')]
    private ?Pays $pays = null;

    #[ORM\OneToMany(targetEntity: AppelOffres::class, mappedBy: 'appelOffresOrganismeDemandeurId', cascade: ['persist', 'remove'])]
    private Collection $appelOffres;

    #[ORM\ManyToOne(targetEntity: NatureOrganismeDemendeur::class, inversedBy: 'organismesDemandeurs')]
    #[ORM\JoinColumn(
        name: 'nature_organisme_demendeur_id',
        referencedColumnName: 'nature_organisme_demendeur_id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?NatureOrganismeDemendeur $natureOrganismeDemendeur = null;

    /**
     * Many OrganismeDemandeur -> One SecteurActivite
     */
    #[ORM\ManyToOne(targetEntity: SecteurActivite::class, inversedBy: 'organismesDemandeurs')]
    #[ORM\JoinColumn(
        name: 'secteur_activite_id',
        referencedColumnName: 'secteur_activite_id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?SecteurActivite $secteurActivite = null;

    public function __construct()
    {
        $this->appelOffres = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) ($this->organismeDemandeurLibelle ?? '');
    }


    public function getOrganismeDemandeurId(): ?int
    {
        return $this->organismeDemandeurId;
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

    public function getOrganismeDemandeurDescription(): ?string
    {
        return $this->organismeDemandeurDescription;
    }

    public function setOrganismeDemandeurDescription(?string $organismeDemandeurDescription): self
    {
        $this->organismeDemandeurDescription = $organismeDemandeurDescription;
        return $this;
    }

    public function getOrganismeDemandeurAcronyme(): ?string
    {
        return $this->organismeDemandeurAcronyme;
    }

    public function setOrganismeDemandeurAcronyme(?string $organismeDemandeurAcronyme): self
    {
        $this->organismeDemandeurAcronyme = $organismeDemandeurAcronyme;
        return $this;
    }

    public function getOrganismeDemandeurLogo(): ?string
    {
        return $this->organismeDemandeurLogo;
    }

    public function setOrganismeDemandeurLogo(?string $organismeDemandeurLogo): self
    {
        $this->organismeDemandeurLogo = $organismeDemandeurLogo;
        return $this;
    }

    public function getOrganismeDemandeurNomCoordinateur(): ?string
    {
        return $this->organismeDemandeurNomCoordinateur;
    }

    public function setOrganismeDemandeurNomCoordinateur(?string $organismeDemandeurNomCoordinateur): self
    {
        $this->organismeDemandeurNomCoordinateur = $organismeDemandeurNomCoordinateur;
        return $this;
    }

    public function getOrganismeDemandeurEmailCoordinateur(): ?string
    {
        return $this->organismeDemandeurEmailCoordinateur;
    }

    public function setOrganismeDemandeurEmailCoordinateur(?string $organismeDemandeurEmailCoordinateur): self
    {
        $this->organismeDemandeurEmailCoordinateur = $organismeDemandeurEmailCoordinateur;
        return $this;
    }

    public function getOrganismeDemandeurRaisonSocial(): ?string
    {
        return $this->organismeDemandeurRaisonSocial;
    }

    public function setOrganismeDemandeurRaisonSocial(?string $organismeDemandeurRaisonSocial): self
    {
        $this->organismeDemandeurRaisonSocial = $organismeDemandeurRaisonSocial;
        return $this;
    }

    public function getOrganismeDemandeurRaisonSocialShort(): ?string
    {
        return $this->organismeDemandeurRaisonSocialShort;
    }

    public function setOrganismeDemandeurRaisonSocialShort(?string $organismeDemandeurRaisonSocialShort): self
    {
        $this->organismeDemandeurRaisonSocialShort = $organismeDemandeurRaisonSocialShort;
        return $this;
    }

    public function getOrganismeDemandeurAdresse(): ?string
    {
        return $this->organismeDemandeurAdresse;
    }

    public function setOrganismeDemandeurAdresse(?string $organismeDemandeurAdresse): self
    {
        $this->organismeDemandeurAdresse = $organismeDemandeurAdresse;
        return $this;
    }

    public function getOrganismeDemandeurPersonneContact1(): ?string
    {
        return $this->organismeDemandeurPersonneContact1;
    }

    public function setOrganismeDemandeurPersonneContact1(?string $organismeDemandeurPersonneContact1): self
    {
        $this->organismeDemandeurPersonneContact1 = $organismeDemandeurPersonneContact1;
        return $this;
    }

    public function getOrganismeDemandeurPersonneTelephonne1(): ?string
    {
        return $this->organismeDemandeurPersonneTelephonne1;
    }

    public function setOrganismeDemandeurPersonneTelephonne1(?string $organismeDemandeurPersonneTelephonne1): self
    {
        $this->organismeDemandeurPersonneTelephonne1 = $organismeDemandeurPersonneTelephonne1;
        return $this;
    }

    public function getOrganismeDemandeurPersonneContact2(): ?string
    {
        return $this->organismeDemandeurPersonneContact2;
    }

    public function setOrganismeDemandeurPersonneContact2(?string $organismeDemandeurPersonneContact2): self
    {
        $this->organismeDemandeurPersonneContact2 = $organismeDemandeurPersonneContact2;
        return $this;
    }

    public function getOrganismeDemandeurTelephone2(): ?string
    {
        return $this->organismeDemandeurTelephone2;
    }

    public function setOrganismeDemandeurTelephone2(?string $organismeDemandeurTelephone2): self
    {
        $this->organismeDemandeurTelephone2 = $organismeDemandeurTelephone2;
        return $this;
    }

    public function getOrganismeDemandeurEmail2(): ?string
    {
        return $this->organismeDemandeurEmail2;
    }

    public function setOrganismeDemandeurEmail2(?string $organismeDemandeurEmail2): self
    {
        $this->organismeDemandeurEmail2 = $organismeDemandeurEmail2;
        return $this;
    }

    public function getOrganismeDemandeurPersonneContact3(): ?string
    {
        return $this->organismeDemandeurPersonneContact3;
    }

    public function setOrganismeDemandeurPersonneContact3(?string $organismeDemandeurPersonneContact3): self
    {
        $this->organismeDemandeurPersonneContact3 = $organismeDemandeurPersonneContact3;
        return $this;
    }

    public function getOrganismeDemandeurTelephone3(): ?string
    {
        return $this->organismeDemandeurTelephone3;
    }

    public function setOrganismeDemandeurTelephone3(?string $organismeDemandeurTelephone3): self
    {
        $this->organismeDemandeurTelephone3 = $organismeDemandeurTelephone3;
        return $this;
    }

    public function getOrganismeDemandeurEmail3(): ?string
    {
        return $this->organismeDemandeurEmail3;
    }

    public function setOrganismeDemandeurEmail3(?string $organismeDemandeurEmail3): self
    {
        $this->organismeDemandeurEmail3 = $organismeDemandeurEmail3;
        return $this;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): self
    {
        $this->pays = $pays;
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

    public function getNatureOrganismeDemendeur(): ?NatureOrganismeDemendeur
    {
        return $this->natureOrganismeDemendeur;
    }

    public function setNatureOrganismeDemendeur(?NatureOrganismeDemendeur $nature): self
    {
        $this->natureOrganismeDemendeur = $nature;
        return $this;
    }

    public function getSecteurActivite(): ?SecteurActivite
    {
        return $this->secteurActivite;
    }

    public function setSecteurActivite(?SecteurActivite $secteur): self
    {
        $this->secteurActivite = $secteur;
        return $this;
    }
}
