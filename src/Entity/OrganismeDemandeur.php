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
#[ORM\Table(name: 'organismeDemandeur')]
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

    #[ORM\Column(name: 'organismeDemandeurLibelle', length: 255, nullable: true)]
    private ?string $organismeDemandeurLibelle = null;

    #[ORM\Column(name: 'organismeDemandeurRaisonSocial', length: 255, nullable: true)]
    private ?string $organismeDemandeurRaisonSociale = null;

    #[ORM\Column(name: 'organismeDemandeurRaisonSocialShort', length: 100, nullable: true)]
    private ?string $organismeDemandeurRaisonSocialeShort = null;

    #[ORM\Column(name: 'organismeDemandeurDescription', type: 'text', nullable: true)]
    private ?string $organismeDemandeurDescription = null;

    #[ORM\Column(name: 'organismeDemandeurAcronyme', length: 100, nullable: true)]
    private ?string $organismeDemandeurAcronyme = null;

    #[ORM\Column(name: 'organismeDemandeurLogo', length: 255, nullable: true)]
    private ?string $organismeDemandeurLogo = null;

    #[ORM\Column(name: 'organismeDemandeurNomCoordinateur', length: 150, nullable: true)]
    private ?string $organismeDemandeurCoordinateurPrenomNom = null;

    #[ORM\Column(name: 'organismeDemandeurEmailCoordinateur', length: 180, nullable: true)]
    private ?string $organismeDemandeurCoordinateurEmail = null;

    #[ORM\Column(name: 'organismeDemandeurCoordinateurTel', length: 50, nullable: true)]
    private ?string $organismeDemandeurCoordinateurTel = null;

    #[ORM\Column(name: 'organismeDemandeurAdresse', length: 500, nullable: true)]
    private ?string $organismeDemandeurAdresse = null;

    #[ORM\Column(name: 'organismeDemandeurTelephone', length: 50, nullable: true)]
    private ?string $organismeDemandeurTelephone = null;

    #[ORM\Column(name: 'organismeDemandeurEmail', length: 180, nullable: true)]
    private ?string $organismeDemandeurEmail = null;

    #[ORM\Column(name: 'organismeDemandeurPersonneContact1', length: 150, nullable: true)]
    private ?string $organismeDemandeurPersonneContactPrenomNom1 = null;

    #[ORM\Column(name: 'organismeDemandeurPersonneTelephonne1', length: 50, nullable: true)]
    private ?string $organismeDemandeurPersonneContactTelephone1 = null;

    #[ORM\Column(name: 'organismeDemandeurPersonneContactEmail1', length: 180, nullable: true)]
    private ?string $organismeDemandeurPersonneContactEmail1 = null;

    #[ORM\Column(name: 'organismeDemandeurPersonneContact2', length: 150, nullable: true)]
    private ?string $organismeDemandeurPersonneContactPrenomNom2 = null;

    #[ORM\Column(name: 'organismeDemandeurTelephone2', length: 50, nullable: true)]
    private ?string $organismeDemandeurPersonneContactTelephone2 = null;

    #[ORM\Column(name: 'organismeDemandeurEmail2', length: 180, nullable: true)]
    private ?string $organismeDemandeurPersonneContactEmail2 = null;

    #[ORM\Column(name: 'organismeDemandeurPersonneContact3', length: 150, nullable: true)]
    private ?string $organismeDemandeurPersonneContactPrenomNom3 = null;

    #[ORM\Column(name: 'organismeDemandeurTelephone3', length: 50, nullable: true)]
    private ?string $organismeDemandeurPersonneContactTelephone3 = null;

    #[ORM\Column(name: 'organismeDemandeurEmail3', length: 180, nullable: true)]
    private ?string $organismeDemandeurPersonneContactEmail3 = null;

    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(name: 'paysId', referencedColumnName: 'paysId', nullable: true, onDelete: 'SET NULL')]
    private ?Pays $pays = null;

    #[ORM\ManyToOne(targetEntity: NatureOrganismeDemendeur::class, inversedBy: 'organismesDemandeurs')]
    #[ORM\JoinColumn(name: 'nature_organisme_demendeur_id', referencedColumnName: 'nature_organisme_demendeur_id', nullable: true, onDelete: 'SET NULL')]
    private ?NatureOrganismeDemendeur $natureOrganismeDemendeur = null;

    #[ORM\ManyToOne(targetEntity: SecteurActivite::class, inversedBy: 'organismesDemandeurs')]
    #[ORM\JoinColumn(name: 'secteur_activite_id', referencedColumnName: 'secteur_activite_id', nullable: true, onDelete: 'SET NULL')]
    private ?SecteurActivite $secteurActivite = null;

    #[ORM\OneToMany(targetEntity: AppelOffres::class, mappedBy: 'appelOffresOrganismeDemandeurId', cascade: ['persist', 'remove'])]
    private Collection $appelOffres;

    public function __construct()
    {
        $this->appelOffres = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) ($this->organismeDemandeurRaisonSociale ?? $this->organismeDemandeurRaisonSocialeShort ?? $this->organismeDemandeurLibelle ?? '');
    }

    public function getOrganismeDemandeurId(): ?int
    {
        return $this->organismeDemandeurId;
    }

    public function getOrganismeDemandeurLibelle(): ?string
    {
        return $this->organismeDemandeurLibelle;
    }

    public function setOrganismeDemandeurLibelle(?string $v): self
    {
        $this->organismeDemandeurLibelle = $v;
        return $this;
    }

    public function getOrganismeDemandeurRaisonSociale(): ?string
    {
        return $this->organismeDemandeurRaisonSociale;
    }

    public function setOrganismeDemandeurRaisonSociale(?string $v): self
    {
        $this->organismeDemandeurRaisonSociale = $v;
        return $this;
    }

    public function getOrganismeDemandeurRaisonSocialeShort(): ?string
    {
        return $this->organismeDemandeurRaisonSocialeShort;
    }

    public function setOrganismeDemandeurRaisonSocialeShort(?string $v): self
    {
        $this->organismeDemandeurRaisonSocialeShort = $v;
        return $this;
    }

    public function getOrganismeDemandeurDescription(): ?string { return $this->organismeDemandeurDescription; }
    public function setOrganismeDemandeurDescription(?string $v): self { $this->organismeDemandeurDescription = $v; return $this; }

    public function getOrganismeDemandeurAcronyme(): ?string { return $this->organismeDemandeurAcronyme; }
    public function setOrganismeDemandeurAcronyme(?string $v): self { $this->organismeDemandeurAcronyme = $v; return $this; }

    public function getOrganismeDemandeurLogo(): ?string { return $this->organismeDemandeurLogo; }
    public function setOrganismeDemandeurLogo(?string $v): self { $this->organismeDemandeurLogo = $v; return $this; }

    public function getOrganismeDemandeurCoordinateurPrenomNom(): ?string { return $this->organismeDemandeurCoordinateurPrenomNom; }
    public function setOrganismeDemandeurCoordinateurPrenomNom(?string $v): self { $this->organismeDemandeurCoordinateurPrenomNom = $v; return $this; }

    public function getOrganismeDemandeurCoordinateurEmail(): ?string { return $this->organismeDemandeurCoordinateurEmail; }
    public function setOrganismeDemandeurCoordinateurEmail(?string $v): self { $this->organismeDemandeurCoordinateurEmail = $v; return $this; }

    public function getOrganismeDemandeurCoordinateurTel(): ?string { return $this->organismeDemandeurCoordinateurTel; }
    public function setOrganismeDemandeurCoordinateurTel(?string $v): self { $this->organismeDemandeurCoordinateurTel = $v; return $this; }

    public function getOrganismeDemandeurAdresse(): ?string { return $this->organismeDemandeurAdresse; }
    public function setOrganismeDemandeurAdresse(?string $v): self { $this->organismeDemandeurAdresse = $v; return $this; }

    public function getOrganismeDemandeurTelephone(): ?string { return $this->organismeDemandeurTelephone; }
    public function setOrganismeDemandeurTelephone(?string $v): self { $this->organismeDemandeurTelephone = $v; return $this; }

    public function getOrganismeDemandeurEmail(): ?string { return $this->organismeDemandeurEmail; }
    public function setOrganismeDemandeurEmail(?string $v): self { $this->organismeDemandeurEmail = $v; return $this; }

    public function getOrganismeDemandeurPersonneContactPrenomNom1(): ?string { return $this->organismeDemandeurPersonneContactPrenomNom1; }
    public function setOrganismeDemandeurPersonneContactPrenomNom1(?string $v): self { $this->organismeDemandeurPersonneContactPrenomNom1 = $v; return $this; }

    public function getOrganismeDemandeurPersonneContactTelephone1(): ?string { return $this->organismeDemandeurPersonneContactTelephone1; }
    public function setOrganismeDemandeurPersonneContactTelephone1(?string $v): self { $this->organismeDemandeurPersonneContactTelephone1 = $v; return $this; }

    public function getOrganismeDemandeurPersonneContactEmail1(): ?string { return $this->organismeDemandeurPersonneContactEmail1; }
    public function setOrganismeDemandeurPersonneContactEmail1(?string $v): self { $this->organismeDemandeurPersonneContactEmail1 = $v; return $this; }

    public function getOrganismeDemandeurPersonneContactPrenomNom2(): ?string { return $this->organismeDemandeurPersonneContactPrenomNom2; }
    public function setOrganismeDemandeurPersonneContactPrenomNom2(?string $v): self { $this->organismeDemandeurPersonneContactPrenomNom2 = $v; return $this; }

    public function getOrganismeDemandeurPersonneContactTelephone2(): ?string { return $this->organismeDemandeurPersonneContactTelephone2; }
    public function setOrganismeDemandeurPersonneContactTelephone2(?string $v): self { $this->organismeDemandeurPersonneContactTelephone2 = $v; return $this; }

    public function getOrganismeDemandeurPersonneContactEmail2(): ?string { return $this->organismeDemandeurPersonneContactEmail2; }
    public function setOrganismeDemandeurPersonneContactEmail2(?string $v): self { $this->organismeDemandeurPersonneContactEmail2 = $v; return $this; }

    public function getOrganismeDemandeurPersonneContactPrenomNom3(): ?string { return $this->organismeDemandeurPersonneContactPrenomNom3; }
    public function setOrganismeDemandeurPersonneContactPrenomNom3(?string $v): self { $this->organismeDemandeurPersonneContactPrenomNom3 = $v; return $this; }

    public function getOrganismeDemandeurPersonneContactTelephone3(): ?string { return $this->organismeDemandeurPersonneContactTelephone3; }
    public function setOrganismeDemandeurPersonneContactTelephone3(?string $v): self { $this->organismeDemandeurPersonneContactTelephone3 = $v; return $this; }

    public function getOrganismeDemandeurPersonneContactEmail3(): ?string { return $this->organismeDemandeurPersonneContactEmail3; }
    public function setOrganismeDemandeurPersonneContactEmail3(?string $v): self { $this->organismeDemandeurPersonneContactEmail3 = $v; return $this; }

    public function getPays(): ?Pays { return $this->pays; }
    public function setPays(?Pays $p): self { $this->pays = $p; return $this; }

    public function getNatureOrganismeDemendeur(): ?NatureOrganismeDemendeur { return $this->natureOrganismeDemendeur; }
    public function setNatureOrganismeDemendeur(?NatureOrganismeDemendeur $n): self { $this->natureOrganismeDemendeur = $n; return $this; }

    public function getSecteurActivite(): ?SecteurActivite { return $this->secteurActivite; }
    public function setSecteurActivite(?SecteurActivite $s): self { $this->secteurActivite = $s; return $this; }

    public function getAppelOffres(): Collection { return $this->appelOffres; }

    public function addAppelOffres(AppelOffres $appel): self {
        if (!$this->appelOffres->contains($appel)) {
            $this->appelOffres->add($appel);
            $appel->setAppelOffresOrganismeDemandeurId($this);
        }
        return $this;
    }

    public function removeAppelOffres(AppelOffres $appel): self {
        if ($this->appelOffres->removeElement($appel)) {
            if ($appel->getAppelOffresOrganismeDemandeurId() === $this) {
                $appel->setAppelOffresOrganismeDemandeurId(null);
            }
        }
        return $this;
    }
}
