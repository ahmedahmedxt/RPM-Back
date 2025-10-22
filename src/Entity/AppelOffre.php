<?php

namespace App\Entity;

use App\Repository\AppelOffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AppelOffreRepository::class)]
class AppelOffre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AppelOffreType::class)]
    #[ORM\JoinColumn(name: "appel_offre_type_id", referencedColumnName: "id", nullable: false)]
    private $appelOffreType;

    #[ORM\ManyToOne(targetEntity: MoyenLivraison::class)]
    #[ORM\JoinColumn(name: "moyen_livraison_id", referencedColumnName: "id", nullable: false)]
    private $moyenLivraison;

    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(name: "pays_id", referencedColumnName: "paysId", nullable: false)]
    private $pays;

    #[ORM\ManyToOne(targetEntity: OrganismeDemandeur::class)]
    #[ORM\JoinColumn(name: "organisme_demandeur_id", referencedColumnName: "id", nullable: false)]
    private $organismeDemandeur;

    #[ORM\ManyToOne(targetEntity: Devises::class)]
    #[ORM\JoinColumn(name: "devisesId", referencedColumnName: "devisesId", nullable: false)]
    private $devises;

    // ✅ NOUVELLE RELATION : Many-to-Many avec Partenaire
    #[ORM\ManyToMany(targetEntity: Partenaire::class, inversedBy: 'appelOffres')]
    #[ORM\JoinTable(name: 'appel_offre_partenaire')]
    #[ORM\JoinColumn(name: 'appel_offre_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'partenaire_id', referencedColumnName: 'partenaireId')]
    private Collection $partenaires;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private ?string $appelOffreObjet = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $appelOffreDateRemise = null;

    #[ORM\Column(type: "string", length: 50, nullable: true, name: "appelOffreDevis")]
    private ?string $appelOffreDevis = null;

    #[ORM\Column(type: "integer")]
    private ?int $appelOffreRetire = null;

    #[ORM\Column(type: "integer")]
    private ?int $appelOffreParticipation = null;

    #[ORM\Column(type: "string", length: 20)]
    private ?string $appelOffreEtat = null;

    public const ETATS = [
        'EN_ATTENTE' => 'En Attente du résultat',
        'ANNULE' => 'Annulé',
        'REPORTE' => 'Reporté',
        'GAGNE' => 'Gagné',
    ];

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $remarque = null;

    #[ORM\Column(type: "time", nullable: true)]
    private ?\DateTimeInterface $heureRemis = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $dateParticipation = null;

    #[ORM\Column(type: "string", length: 50, nullable: true, name: "numero_devis_participation")]
    private ?string $numeroDevisParticipation = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $typeParticipation = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $appelOffreAnnee = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $appelOffreCautionBancaire = null;
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $resultatRang = null;
    
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $resultatRangTotal = null;
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateLimiteRemise = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $lienAnnonce = null;

    // ✅ CONSTRUCTEUR avec initialisation de la collection partenaires
    public function __construct()
    {
        $this->partenaires = new ArrayCollection();
    }

    // ===================== GETTERS & SETTERS =====================
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppelOffreType(): ?AppelOffreType
    {
        return $this->appelOffreType;
    }

    public function setAppelOffreType(?AppelOffreType $appelOffreType): self
    {
        $this->appelOffreType = $appelOffreType;
        return $this;
    }

    public function getDevises(): ?Devises
    {
        return $this->devises;
    }

    public function setDevises(?Devises $devises): self
    {
        $this->devises = $devises;
        return $this;
    }

    public function getAppelOffreDevis(): ?string
    {
        return $this->appelOffreDevis;
    }

    public function setAppelOffreDevis(?string $appelOffreDevis): self
    {
        $this->appelOffreDevis = $appelOffreDevis;
        return $this;
    }

    public function getMoyenLivraison(): ?MoyenLivraison
    {
        return $this->moyenLivraison;
    }

    public function setMoyenLivraison(?MoyenLivraison $moyenLivraison): self
    {
        $this->moyenLivraison = $moyenLivraison;
        return $this;
    }

    public function getDateLimiteRemise(): ?\DateTimeInterface
    {
        return $this->dateLimiteRemise;
    }

    public function setDateLimiteRemise(?\DateTimeInterface $dateLimiteRemise): self
    {
        $this->dateLimiteRemise = $dateLimiteRemise;
        return $this;
    }

    public function getLienAnnonce(): ?string
    {
        return $this->lienAnnonce;
    }

    public function setLienAnnonce(?string $lienAnnonce): self
    {
        $this->lienAnnonce = $lienAnnonce;
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

    public function getOrganismeDemandeur(): ?OrganismeDemandeur
    {
        return $this->organismeDemandeur;
    }

    public function setOrganismeDemandeur(?OrganismeDemandeur $organismeDemandeur): self
    {
        $this->organismeDemandeur = $organismeDemandeur;
        return $this;
    }
    public function getResultatRang(): ?int
    {
        return $this->resultatRang;
    }
    
    public function setResultatRang(?int $resultatRang): self
    {
        $this->resultatRang = $resultatRang;
        return $this;
    }
    
    public function getResultatRangTotal(): ?int
    {
        return $this->resultatRangTotal;
    }
    
    public function setResultatRangTotal(?int $resultatRangTotal): self
    {
        $this->resultatRangTotal = $resultatRangTotal;
        return $this;
    }
    public function getAppelOffreObjet(): ?string
    {
        return $this->appelOffreObjet;
    }

    public function setAppelOffreObjet(string $appelOffreObjet): self
    {
        $this->appelOffreObjet = $appelOffreObjet;
        return $this;
    }

    public function getAppelOffreDateRemise(): ?\DateTimeInterface
    {
        return $this->appelOffreDateRemise;
    }

    public function setAppelOffreDateRemise(\DateTimeInterface $appelOffreDateRemise): self
    {
        $this->appelOffreDateRemise = $appelOffreDateRemise;
        return $this;
    }

    public function getAppelOffreRetire(): ?int
    {
        return $this->appelOffreRetire;
    }

    public function setAppelOffreRetire(int $appelOffreRetire): self
    {
        $this->appelOffreRetire = $appelOffreRetire;
        return $this;
    }

    public function getAppelOffreParticipation(): ?int
    {
        return $this->appelOffreParticipation;
    }

    public function setAppelOffreParticipation(int $appelOffreParticipation): self
    {
        $this->appelOffreParticipation = $appelOffreParticipation;
        return $this;
    }

    public function getAppelOffreEtat(): ?string
    {
        return $this->appelOffreEtat;
    }

    public function setAppelOffreEtat(string $etat): self
    {
        $key = array_search($etat, self::ETATS);
        if ($key === false) {
            if (!in_array($etat, array_keys(self::ETATS))) {
                throw new \InvalidArgumentException("État invalide: $etat");
            }
            $key = $etat;
        }
        $this->appelOffreEtat = $key;
        return $this;
    }

    public function getRemarque(): ?string
    {
        return $this->remarque;
    }

    public function setRemarque(?string $remarque): self
    {
        $this->remarque = $remarque;
        return $this;
    }

    public function getHeureRemis(): ?\DateTimeInterface
    {
        return $this->heureRemis;
    }

    public function setHeureRemis(?\DateTimeInterface $heureRemis): self
    {
        $this->heureRemis = $heureRemis;
        return $this;
    }

    public function getDateParticipation(): ?\DateTimeInterface
    {
        return $this->dateParticipation;
    }

    public function setDateParticipation(?\DateTimeInterface $dateParticipation): self
    {
        $this->dateParticipation = $dateParticipation;
        return $this;
    }

    public function getNumeroDevisParticipation(): ?string
    {
        return $this->numeroDevisParticipation;
    }

    public function setNumeroDevisParticipation(?string $numeroDevisParticipation): self
    {
        $this->numeroDevisParticipation = $numeroDevisParticipation;
        return $this;
    }

    public function getTypeParticipation(): ?string
    {
        return $this->typeParticipation;
    }

    public function setTypeParticipation(?string $typeParticipation): self
    {
        $this->typeParticipation = $typeParticipation;
        return $this;
    }

    public function getAppelOffreAnnee(): ?int
    {
        return $this->appelOffreAnnee;
    }

    public function setAppelOffreAnnee(?int $appelOffreAnnee): self
    {
        $this->appelOffreAnnee = $appelOffreAnnee;
        return $this;
    }

    public function getAppelOffreCautionBancaire(): ?int
    {
        return $this->appelOffreCautionBancaire;
    }

    public function setAppelOffreCautionBancaire(?int $appelOffreCautionBancaire): self
    {
        $this->appelOffreCautionBancaire = $appelOffreCautionBancaire;
        return $this;
    }

    // ✅ NOUVELLES MÉTHODES pour gérer les Partenaires
    /**
     * @return Collection<int, Partenaire>
     */
    public function getPartenaires(): Collection
    {
        return $this->partenaires;
    }

    public function addPartenaire(Partenaire $partenaire): self
    {
        if (!$this->partenaires->contains($partenaire)) {
            $this->partenaires->add($partenaire);
        }
        return $this;
    }

    public function removePartenaire(Partenaire $partenaire): self
    {
        $this->partenaires->removeElement($partenaire);
        return $this;
    }

    public function clearPartenaires(): self
    {
        $this->partenaires->clear();
        return $this;
    }
}