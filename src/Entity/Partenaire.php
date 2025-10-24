<?php

namespace App\Entity;

use App\Repository\PartenaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PartenaireRepository::class)]
#[ORM\Table(name: 'partenaire')]
class Partenaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'partenaireId', type: 'integer')]
    #[Groups(['partenaire:read', 'appeloffre:read'])]
    private ?int $partenaireId = null;

    #[ORM\Column(name: 'partenaireLibelle', type: 'string', length: 255)]
    #[Groups(['partenaire:read', 'partenaire:write', 'appeloffre:read'])]
    private ?string $partenaireLibelle = null;

    #[ORM\Column(name: 'partenaireAcronyme', type: 'string', length: 50, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write', 'appeloffre:read'])]
    private ?string $partenaireAcronyme = null;

    #[ORM\Column(name: 'premierResponsable', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $premierResponsable = null;

    #[ORM\Column(name: 'prEmail', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $prEmail = null;

    #[ORM\Column(name: 'prTel', type: 'string', length: 50, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $prTel = null;

    #[ORM\Column(name: 'adresse', type: 'text', nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $adresse = null;

    #[ORM\Column(name: 'pays', type: 'string', length: 100, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $pays = null;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $email = null;

    #[ORM\Column(name: 'tel1', type: 'string', length: 50, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $tel1 = null;

    #[ORM\Column(name: 'tel2', type: 'string', length: 50, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $tel2 = null;

    #[ORM\Column(name: 'siteWeb', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $siteWeb = null;

    #[ORM\Column(name: 'linkedIn', type: 'string', length: 255, nullable: true)]
    #[Groups(['partenaire:read', 'partenaire:write'])]
    private ?string $linkedIn = null;

   
#[ORM\OneToMany(mappedBy: 'partenaire', targetEntity: AppelOffrePartenaire::class)]
private Collection $appelOffrePartenaires;

public function __construct()
{
    $this->appelOffrePartenaires = new ArrayCollection();  
}

    // Getters et Setters existants
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

    // Nouveaux Getters et Setters
    public function getPremierResponsable(): ?string
    {
        return $this->premierResponsable;
    }

    public function setPremierResponsable(?string $premierResponsable): self
    {
        $this->premierResponsable = $premierResponsable;
        return $this;
    }

    public function getPrEmail(): ?string
    {
        return $this->prEmail;
    }

    public function setPrEmail(?string $prEmail): self
    {
        $this->prEmail = $prEmail;
        return $this;
    }

    public function getPrTel(): ?string
    {
        return $this->prTel;
    }

    public function setPrTel(?string $prTel): self
    {
        $this->prTel = $prTel;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): self
    {
        $this->pays = $pays;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getTel1(): ?string
    {
        return $this->tel1;
    }

    public function setTel1(?string $tel1): self
    {
        $this->tel1 = $tel1;
        return $this;
    }

    public function getTel2(): ?string
    {
        return $this->tel2;
    }

    public function setTel2(?string $tel2): self
    {
        $this->tel2 = $tel2;
        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function setSiteWeb(?string $siteWeb): self
    {
        $this->siteWeb = $siteWeb;
        return $this;
    }

    public function getLinkedIn(): ?string
    {
        return $this->linkedIn;
    }

    public function setLinkedIn(?string $linkedIn): self
    {
        $this->linkedIn = $linkedIn;
        return $this;
    }

  
/**
 * @return Collection<int, AppelOffrePartenaire>
 */
public function getAppelOffrePartenaires(): Collection
{
    return $this->appelOffrePartenaires;
}
    
}