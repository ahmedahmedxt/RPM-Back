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

    #[ORM\Column(name: 'partenaireRaisonSociale', type: 'string', length: 255)]
    #[Groups(['partenaire:read', 'partenaire:write', 'appeloffres:read'])]
    private ?string $partenaireRaisonSociale = null;

    #[ORM\Column(name: 'partenaireRaisonSocialeShort', type: 'string', length: 50, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write', 'appeloffres:read'])]
    private ?string $partenaireRaisonSocialeShort = null;

    #[ORM\Column(name: 'partenairePremierResponsable', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenairePremierResponsable = null;

    #[ORM\Column(name: 'partenairePremierResponsableEmail', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenairePremierResponsableEmail = null;

    #[ORM\Column(name: 'partenairePremierResponsableTelephone', type: 'integer', nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?int $partenairePremierResponsableTelephone = null;

    #[ORM\Column(name: 'partenaireAdresse', type: 'text', nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenaireAdresse = null;

    #[ORM\Column(name: 'partenaireEmail', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenaireEmail = null;

    #[ORM\Column(name: 'partenaireTelephone1', type: 'integer', nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?int $partenaireTelephone1 = null;

    #[ORM\Column(name: 'partenaireTelephone2', type: 'integer', nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?int $partenaireTelephone2 = null;

    #[ORM\Column(name: 'partenaireSiteWeb', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenaireSiteWeb = null;

    #[ORM\Column(name: 'partenaireLinkedIn', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenaireLinkedIn = null;

    #[ORM\Column(name: 'partenaireFacebook', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $partenaireFacebook = null;

    #[ORM\OneToMany(mappedBy: 'partenaire', targetEntity: AppelOffresPartenaire::class, cascade: ['persist', 'remove'])]
    private Collection $appelOffresPartenaires;

    public function __construct()
    {
        $this->appelOffresPartenaires = new ArrayCollection();
    }

    public function getPartenaireId(): ?int
    {
        return $this->partenaireId;
    }

    public function getPartenaireRaisonSociale(): ?string
    {
        return $this->partenaireRaisonSociale;
    }

    public function setPartenaireRaisonSociale(string $partenaireRaisonSociale): self
    {
        $this->partenaireRaisonSociale = $partenaireRaisonSociale;
        return $this;
    }

    public function getPartenaireRaisonSocialeShort(): ?string
    {
        return $this->partenaireRaisonSocialeShort;
    }

    public function setPartenaireRaisonSocialeShort(?string $partenaireRaisonSocialeShort): self
    {
        $this->partenaireRaisonSocialeShort = $partenaireRaisonSocialeShort;
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

    public function getPartenairePremierResponsableTelephone(): ?int
    {
        return $this->partenairePremierResponsableTelephone;
    }

    public function setPartenairePremierResponsableTelephone(?int $partenairePremierResponsableTelephone): self
    {
        $this->partenairePremierResponsableTelephone = $partenairePremierResponsableTelephone;
        return $this;
    }

    public function getPartenaireAdresse(): ?string
    {
        return $this->partenaireAdresse;
    }

    public function setPartenaireAdresse(?string $partenaireAdresse): self
    {
        $this->partenaireAdresse = $partenaireAdresse;
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

    public function getPartenaireTelephone1(): ?int
    {
        return $this->partenaireTelephone1;
    }

    public function setPartenaireTelephone1(?int $partenaireTelephone1): self
    {
        $this->partenaireTelephone1 = $partenaireTelephone1;
        return $this;
    }

    public function getPartenaireTelephone2(): ?int
    {
        return $this->partenaireTelephone2;
    }

    public function setPartenaireTelephone2(?int $partenaireTelephone2): self
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

    public function getPartenaireFacebook(): ?string
    {
        return $this->partenaireFacebook;
    }

    public function setPartenaireFacebook(?string $partenaireFacebook): self
    {
        $this->partenaireFacebook = $partenaireFacebook;
        return $this;
    }

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