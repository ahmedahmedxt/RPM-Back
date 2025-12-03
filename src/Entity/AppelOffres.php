<?php

namespace App\Entity;

use App\Repository\AppelOffresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\AppelOffresType;
use App\Entity\MoyenLivraison;
use App\Entity\Pays;
use App\Entity\OrganismeDemandeur;
use App\Entity\Devises;
use App\Entity\AppelOffresPartenaire;
use App\Entity\ParticipationType;
use App\Entity\AppelOffresPersonnelCleAppelOffres;

#[ORM\Entity(repositoryClass: AppelOffresRepository::class)]
#[ORM\Table(name: "appel_offres")]
class AppelOffres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "appelOffresId", type: "integer")]
    private ?int $appelOffresId = null;

    #[ORM\ManyToOne(targetEntity: AppelOffresType::class, inversedBy: "appelOffres")]
    #[ORM\JoinColumn(name: "appelOffresTypeId", referencedColumnName: "appelOffresTypeId", nullable: true)]
    private ?AppelOffresType $appelOffresTypeId = null;

    #[ORM\ManyToOne(targetEntity: MoyenLivraison::class, inversedBy: "appelOffres")]
    #[ORM\JoinColumn(name: "appelOffresMoyenLivraisonId", referencedColumnName: "moyenLivraisonId", nullable: true)]
    private ?MoyenLivraison $appelOffresMoyenLivraisonId = null;

    #[ORM\ManyToOne(targetEntity: Pays::class, inversedBy: "appelOffres")]
    #[ORM\JoinColumn(name: "appelOffresPaysId", referencedColumnName: "paysId", nullable: true)]
    private ?Pays $appelOffresPaysId = null;

    #[ORM\ManyToOne(targetEntity: OrganismeDemandeur::class, inversedBy: "appelOffres")]
    #[ORM\JoinColumn(name: "appelOffresOrganismeDemandeurId", referencedColumnName: "organismeDemandeurId", nullable: true)]
    private ?OrganismeDemandeur $appelOffresOrganismeDemandeurId = null;

    #[ORM\ManyToOne(targetEntity: Devises::class, inversedBy: "appelOffres")]
    #[ORM\JoinColumn(name: "appelOffresDevisesId", referencedColumnName: "devisesId", nullable: true)]
    private ?Devises $appelOffresDevisesId = null;

    #[ORM\ManyToOne(targetEntity: Devises::class)]
    #[ORM\JoinColumn(name: "appelOffresCautionBancaireDeviseId", referencedColumnName: "devisesId", nullable: true)]
    private ?Devises $appelOffresCautionBancaireDeviseId = null;

    #[ORM\OneToMany(mappedBy: 'appelOffres', targetEntity: AppelOffresPartenaire::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $appelOffresPartenaires;

    #[ORM\OneToMany(mappedBy: 'appelOffres', targetEntity: AppelOffresPersonnelCleAppelOffres::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $appelOffresPersonnelCleAppelOffres;

    #[ORM\Column(name: "appelOffresObjet", type: "string", length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    private ?string $appelOffresObjet = null;

    #[ORM\Column(name: "appelOffresDateLimiteRemise", type: "date", nullable: true)]
    private ?\DateTimeInterface $appelOffresDateLimiteRemise = null;

    #[ORM\Column(name: "appelOffresHeureLimiteRemise", type: "time", nullable: true)]
    private ?\DateTimeInterface $appelOffresHeureLimiteRemise = null;

    #[ORM\Column(name: "appelOffresCCRetire", type: "integer", nullable: true)]
    private ?int $appelOffresCCRetire = null;

    #[ORM\Column(name: "appelOffresLienAnnonce", type: "string", length: 255, nullable: true)]
    private ?string $appelOffresLienAnnonce = null;

    #[ORM\Column(name: "appelOffresCautionBancaire", type: "integer", nullable: true)]
    private ?int $appelOffresCautionBancaire = null;

    #[ORM\ManyToOne(targetEntity: ParticipationType::class)]
    #[ORM\JoinColumn(name: "appelOffresTypeParticipationId", referencedColumnName: "participationTypeId", nullable: true)]
    private ?ParticipationType $appelOffresTypeParticipationId = null;

    #[ORM\Column(name: "appelOffresRemarque", type: "text", nullable: true)]
    private ?string $appelOffresRemarque = null;

    #[ORM\Column(name: "appelOffresParticipation", type: "integer", nullable: true)]
    private ?int $appelOffresParticipation = null;

    #[ORM\Column(name: "appelOffresDateParticipation", type: "date", nullable: true)]
    private ?\DateTimeInterface $appelOffresDateParticipation = null;

    #[ORM\Column(name: "appelOffresResultatEtat", type: "string", length: 50, nullable: true)]
    private ?string $appelOffresResultatEtat = null;

    #[ORM\Column(name: "appelOffresResultatRang", type: "integer", nullable: true)]
    private ?int $appelOffresResultatRang = null;

    #[ORM\Column(name: "appelOffresResultatRangTotal", type: "integer", nullable: true)]
    private ?int $appelOffresResultatRangTotal = null;

    #[ORM\Column(name: "appelOffresNumeroDevisParticipation", type: "string", length: 50, nullable: true)]
    private ?string $appelOffresNumeroDevisParticipation = null;

    #[ORM\Column(name: "appelOffresNumero", type: "string", length: 10, nullable: true)]
    private ?string $appelOffresNumero = null;

    #[ORM\Column(name: "appelOffresAnnee", type: "integer", nullable: true)]
    private ?int $appelOffresAnnee = null;

    public const ETATS = [
        'EN_ATTENTE' => 'En Attente du résultat',
        'ANNULE' => 'Annulé',
        'REPORTE' => 'Reporté',
        'GAGNE' => 'Gagné',
    ];

    public function __construct()
    {
        $this->appelOffresPartenaires = new ArrayCollection();
        $this->appelOffresPersonnelCleAppelOffres = new ArrayCollection();
    }

    public function getAppelOffresId(): ?int { return $this->appelOffresId; }

    public function getAppelOffresTypeId(): ?AppelOffresType { return $this->appelOffresTypeId; }
    public function setAppelOffresTypeId(?AppelOffresType $v): self { $this->appelOffresTypeId = $v; return $this; }

    public function getAppelOffresMoyenLivraisonId(): ?MoyenLivraison { return $this->appelOffresMoyenLivraisonId; }
    public function setAppelOffresMoyenLivraisonId(?MoyenLivraison $v): self { $this->appelOffresMoyenLivraisonId = $v; return $this; }

    public function getAppelOffresPaysId(): ?Pays { return $this->appelOffresPaysId; }
    public function setAppelOffresPaysId(?Pays $v): self { $this->appelOffresPaysId = $v; return $this; }

    public function getAppelOffresOrganismeDemandeurId(): ?OrganismeDemandeur { return $this->appelOffresOrganismeDemandeurId; }
    public function setAppelOffresOrganismeDemandeurId(?OrganismeDemandeur $v): self { $this->appelOffresOrganismeDemandeurId = $v; return $this; }

    public function getAppelOffresDevisesId(): ?Devises { return $this->appelOffresDevisesId; }
    public function setAppelOffresDevisesId(?Devises $v): self { $this->appelOffresDevisesId = $v; return $this; }

    public function getAppelOffresCautionBancaireDeviseId(): ?Devises { return $this->appelOffresCautionBancaireDeviseId; }
    public function setAppelOffresCautionBancaireDeviseId(?Devises $v): self { $this->appelOffresCautionBancaireDeviseId = $v; return $this; }

    public function getAppelOffresObjet(): ?string { return $this->appelOffresObjet; }
    public function setAppelOffresObjet(?string $v): self { $this->appelOffresObjet = $v; return $this; }

    public function getAppelOffresDateLimiteRemise(): ?\DateTimeInterface { return $this->appelOffresDateLimiteRemise; }
    public function setAppelOffresDateLimiteRemise(?\DateTimeInterface $v): self { $this->appelOffresDateLimiteRemise = $v; return $this; }

    public function getAppelOffresHeureLimiteRemise(): ?\DateTimeInterface { return $this->appelOffresHeureLimiteRemise; }
    public function setAppelOffresHeureLimiteRemise(?\DateTimeInterface $v): self { $this->appelOffresHeureLimiteRemise = $v; return $this; }

    public function getAppelOffresCCRetire(): ?int { return $this->appelOffresCCRetire; }
    public function setAppelOffresCCRetire(?int $v): self { $this->appelOffresCCRetire = $v; return $this; }

    public function getAppelOffresLienAnnonce(): ?string { return $this->appelOffresLienAnnonce; }
    public function setAppelOffresLienAnnonce(?string $v): self { $this->appelOffresLienAnnonce = $v; return $this; }

    public function getAppelOffresCautionBancaire(): ?int { return $this->appelOffresCautionBancaire; }
    public function setAppelOffresCautionBancaire(?int $v): self { $this->appelOffresCautionBancaire = $v; return $this; }

    public function getAppelOffresTypeParticipationId(): ?ParticipationType { return $this->appelOffresTypeParticipationId; }
    public function setAppelOffresTypeParticipationId(?ParticipationType $v): self { $this->appelOffresTypeParticipationId = $v; return $this; }

    public function getAppelOffresRemarque(): ?string { return $this->appelOffresRemarque; }
    public function setAppelOffresRemarque(?string $v): self { $this->appelOffresRemarque = $v; return $this; }

    public function getAppelOffresParticipation(): ?int { return $this->appelOffresParticipation; }
    public function setAppelOffresParticipation(?int $v): self { $this->appelOffresParticipation = $v; return $this; }

    public function getAppelOffresDateParticipation(): ?\DateTimeInterface { return $this->appelOffresDateParticipation; }
    public function setAppelOffresDateParticipation(?\DateTimeInterface $v): self { $this->appelOffresDateParticipation = $v; return $this; }

    public function getAppelOffresResultatEtat(): ?string { return $this->appelOffresResultatEtat; }
    public function setAppelOffresResultatEtat(?string $etat): self { $this->appelOffresResultatEtat = $etat; return $this; }

    public function getAppelOffresResultatRang(): ?int { return $this->appelOffresResultatRang; }
    public function setAppelOffresResultatRang(?int $v): self { $this->appelOffresResultatRang = $v; return $this; }

    public function getAppelOffresResultatRangTotal(): ?int { return $this->appelOffresResultatRangTotal; }
    public function setAppelOffresResultatRangTotal(?int $v): self { $this->appelOffresResultatRangTotal = $v; return $this; }

    public function getAppelOffresNumeroDevisParticipation(): ?string { return $this->appelOffresNumeroDevisParticipation; }
    public function setAppelOffresNumeroDevisParticipation(?string $v): self { $this->appelOffresNumeroDevisParticipation = $v; return $this; }

    public function getAppelOffresNumero(): ?string { return $this->appelOffresNumero; }
    public function setAppelOffresNumero(?string $v): self { $this->appelOffresNumero = $v; return $this; }

    public function getAppelOffresAnnee(): ?int { return $this->appelOffresAnnee; }
    public function setAppelOffresAnnee(?int $v): self { $this->appelOffresAnnee = $v; return $this; }

    public function getAppelOffresPartenaires(): Collection { return $this->appelOffresPartenaires; }

    public function addAppelOffresPartenaire(AppelOffresPartenaire $p): self
    {
        if (!$this->appelOffresPartenaires->contains($p)) {
            $this->appelOffresPartenaires->add($p);
            $p->setAppelOffres($this);
        }
        return $this;
    }

    public function removeAppelOffresPartenaire(AppelOffresPartenaire $p): self
    {
        if ($this->appelOffresPartenaires->removeElement($p)) {
            if ($p->getAppelOffres() === $this) {
                $p->setAppelOffres(null);
            }
        }
        return $this;
    }

    public function clearAppelOffresPartenaires(): self
    {
        $this->appelOffresPartenaires->clear();
        return $this;
    }

    public function getAppelOffresPersonnelCleAppelOffres(): Collection
    {
        return $this->appelOffresPersonnelCleAppelOffres;
    }

    public function addAppelOffresPersonnelCleAppelOffres(AppelOffresPersonnelCleAppelOffres $item): self
    {
        if (!$this->appelOffresPersonnelCleAppelOffres->contains($item)) {
            $this->appelOffresPersonnelCleAppelOffres->add($item);
            $item->setAppelOffres($this);
        }
        return $this;
    }

    public function removeAppelOffresPersonnelCleAppelOffres(AppelOffresPersonnelCleAppelOffres $item): self
    {
        if ($this->appelOffresPersonnelCleAppelOffres->removeElement($item)) {
            if ($item->getAppelOffres() === $this) {
                $item->setAppelOffres(null);
            }
        }
        return $this;
    }
}