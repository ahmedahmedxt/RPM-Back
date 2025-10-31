<?php

namespace App\Entity;

use App\Repository\PartenaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\AppelOffresPartenaire;

#[ORM\Entity(repositoryClass: PartenaireRepository::class)]
#[ORM\Table(name: 'partenaire')]
class Partenaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'partenaireId', type: 'integer')]
    #[Groups(['partenaire:read', 'appeloffres:read'])]
    private ?int $partenaireId = null;

    #[ORM\Column(name: 'partenaireLibelle', type: 'string', length: 255)]
    #[Groups(['partenaire:read', 'partenaire:write', 'appeloffres:read'])]
    private ?string $partenaireLibelle = null;

    #[ORM\Column(name: 'partenaireAcronyme', type: 'string', length: 50, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write', 'appeloffres:read'])]
    private ?string $partenaireAcronyme = null;

    #[ORM\Column(name: 'partenairePremierResponsable', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenairePremierResponsable = null;

    #[ORM\Column(name: 'partenairePremierResponsableEmail', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenairePremierResponsableEmail = null;

    #[ORM\Column(name: 'partenairePremierResponsableTelephone', type: 'string', length: 50, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenairePremierResponsableTelephone = null;

    #[ORM\Column(name: 'partenairePremierResponsableAdresse', type: 'text', nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenairePremierResponsableAdresse = null;

    #[ORM\Column(name: 'partenairePays', type: 'string', length: 100, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenairePays = null;

    #[ORM\Column(name: 'partenaireEmail', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenaireEmail = null;

    #[ORM\Column(name: 'partenaireTelephone1', type: 'string', length: 50, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenaireTelephone1 = null;

    #[ORM\Column(name: 'partenaireTelephone2', type: 'string', length: 50, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenaireTelephone2 = null;

    #[ORM\Column(name: 'partenaireSiteWeb', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenaireSiteWeb = null;

    #[ORM\Column(name: 'partenaireLinkedIn', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenaireLinkedIn = null;

    #[ORM\OneToMany(mappedBy: 'partenaire', targetEntity: AppelOffresPartenaire::class, cascade: ['persist', 'remove'])]
    private Collection $appelOffresPartenaires;

    public function __construct()
    {
        $this->appelOffresPartenaires = new ArrayCollection();
    }

    // Getters / Setters

    public function getPartenaireId(): ?int
    {
        return $this->partenaireId;
    }

    public function getPartenaireLibelle(): ?string
    {
        return $this->partenaireLibelle;
    }

    public function setPartenaireLibelle(string $partenaireLibelle): self
    {
        $this->partenaireLibelle = $partenaireLibelle;
        return $this;
    }

    public function getPartenaireAcronyme(): ?string
    {
        return $this->partenaireAcronyme;
    }

    public function setPartenaireAcronyme(?string $partenaireAcronyme): self
    {
        $this->partenaireAcronyme = $partenaireAcronyme;
        return $this;
    }

    public function getPartenairePremierResponsable(): ?string
    {
        return $this->partenairePremierResponsable;
    }

    public function setPartenairePremierResponsable(?string $partenairePremierResponsable): self
    {
        $this->partenairePremierResponsable = $partenairePremierResponsable;
        return $this;
    }

    public function getPartenairePremierResponsableEmail(): ?string
    {
        return $this->partenairePremierResponsableEmail;
    }

    public function setPartenairePremierResponsableEmail(?string $partenairePremierResponsableEmail): self
    {
        $this->partenairePremierResponsableEmail = $partenairePremierResponsableEmail;
        return $this;
    }

    public function getPartenairePremierResponsableTelephone(): ?string
    {
        return $this->partenairePremierResponsableTelephone;
    }

    public function setPartenairePremierResponsableTelephone(?string $partenairePremierResponsableTelephone): self
    {
        $this->partenairePremierResponsableTelephone = $partenairePremierResponsableTelephone;
        return $this;
    }

    public function getPartenairePremierResponsableAdresse(): ?string
    {
        return $this->partenairePremierResponsableAdresse;
    }

    public function setPartenairePremierResponsableAdresse(?string $partenairePremierResponsableAdresse): self
    {
        $this->partenairePremierResponsableAdresse = $partenairePremierResponsableAdresse;
        return $this;
    }

    public function getPartenairePays(): ?string
    {
        return $this->partenairePays;
    }

    public function setPartenairePays(?string $partenairePays): self
    {
        $this->partenairePays = $partenairePays;
        return $this;
    }

    public function getPartenaireEmail(): ?string
    {
        return $this->partenaireEmail;
    }

    public function setPartenaireEmail(?string $partenaireEmail): self
    {
        $this->partenaireEmail = $partenaireEmail;
        return $this;
    }

    public function getPartenaireTelephone1(): ?string
    {
        return $this->partenaireTelephone1;
    }

    public function setPartenaireTelephone1(?string $partenaireTelephone1): self
    {
        $this->partenaireTelephone1 = $partenaireTelephone1;
        return $this;
    }

    public function getPartenaireTelephone2(): ?string
    {
        return $this->partenaireTelephone2;
    }

    public function setPartenaireTelephone2(?string $partenaireTelephone2): self
    {
        $this->partenaireTelephone2 = $partenaireTelephone2;
        return $this;
    }

    public function getPartenaireSiteWeb(): ?string
    {
        return $this->partenaireSiteWeb;
    }

    public function setPartenaireSiteWeb(?string $partenaireSiteWeb): self
    {
        $this->partenaireSiteWeb = $partenaireSiteWeb;
        return $this;
    }

    public function getPartenaireLinkedIn(): ?string
    {
        return $this->partenaireLinkedIn;
    }

    public function setPartenaireLinkedIn(?string $partenaireLinkedIn): self
    {
        $this->partenaireLinkedIn = $partenaireLinkedIn;
        return $this;
    }

    /**
     * @return Collection<int, AppelOffresPartenaire>
     */
    public function getAppelOffresPartenaires(): Collection
    {
        return $this->appelOffresPartenaires;
    }

    public function addAppelOffresPartenaire(AppelOffresPartenaire $p): self
    {
        if (!$this->appelOffresPartenaires->contains($p)) {
            $this->appelOffresPartenaires->add($p);
            $p->setPartenaire($this);
        }
        return $this;
    }

    public function removeAppelOffresPartenaire(AppelOffresPartenaire $p): self
    {
        if ($this->appelOffresPartenaires->removeElement($p)) {
            if ($p->getPartenaire() === $this) {
                $p->setPartenaire(null);
            }
        }
        return $this;
    }
}