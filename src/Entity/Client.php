<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: "clientId")]
    private ?int $clientId;

    #[ORM\ManyToOne(targetEntity: NatureClient::class)]
    #[ORM\JoinColumn(name: "natureClientId", referencedColumnName: "natureClientId")]
    #[Assert\NotBlank]
    private ?NatureClient $natureClient;
    
 #[ORM\OneToMany(targetEntity: Projet::class, mappedBy: "client")]
private Collection $projets;

    #[ORM\Column(name: "clientRaisonSocial", length: 254)]
    private ?string $clientRaisonSocial;

    #[ORM\Column(name: "clientRaisonSocialShort", length: 254)]
    private ?string $clientRaisonSocialShort;

    #[ORM\Column(name: "clientAdresse", length: 254)]
    private ?string $clientAdresse;

    #[ORM\Column(name: "clientTelephone1", length: 254)]
    private ?string $clientTelephone1;

    #[ORM\Column(name: "clientTelephone2", length: 254)]
    private ?string $clientTelephone2;

    #[ORM\Column(name: "clientTelephone3", length: 254)]
    private ?string $clientTelephone3;

    #[ORM\Column(name: "clientEmail", length: 254)]
    #[Assert\Email]
    private ?string $clientEmail;

    #[ORM\Column(name: "clientPersonneContact1", length: 254)]
    private ?string $clientPersonneContact1;

    #[ORM\Column(name: "clientPersonneContact2", length: 254)]
    private ?string $clientPersonneContact2;

    #[ORM\Column(name: "clientPersonneContact3", length: 254)]
    private ?string $clientPersonneContact3;

    #[ORM\OneToMany(targetEntity: Reference::class, mappedBy: "client")]
    private Collection $references;

 #[ORM\ManyToMany(targetEntity: Pays::class)]
#[ORM\JoinTable(name: "client_pays",
    joinColumns: [new ORM\JoinColumn(name: "client_id", referencedColumnName: "clientId")],
    inverseJoinColumns: [new ORM\JoinColumn(name: "pays_id", referencedColumnName: "paysId")]
)]
private Collection $pays;

    #[ORM\ManyToMany(targetEntity: SecteurActivite::class, inversedBy: 'clients')] 
    #[ORM\JoinTable(name: 'clientsecteuractivite',
        joinColumns: [new ORM\JoinColumn(name: 'clientId', referencedColumnName: 'clientId')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'secteur_activite_id', referencedColumnName: 'secteur_activite_id')]
    )]
    private $secteurActivites;


    public function __construct()
    {
        $this->references = new ArrayCollection();
        $this->secteurActivites = new ArrayCollection();
        $this->pays = new ArrayCollection();
        $this->projets = new ArrayCollection();

    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function getNatureClient(): ?NatureClient
    {
        return $this->natureClient;
    }

    public function setNatureClient(?NatureClient $natureClient): self
    {
        $this->natureClient = $natureClient;
        return $this;
    }

    

    public function getClientRaisonSocial(): ?string
    {
        return $this->clientRaisonSocial;
    }

    public function setClientRaisonSocial(?string $clientRaisonSocial): self
    {
        $this->clientRaisonSocial = $clientRaisonSocial;
        return $this;
    }

    public function getClientRaisonSocialShort(): ?string
    {
        return $this->clientRaisonSocialShort;
    }

    public function setClientRaisonSocialShort(?string $clientRaisonSocialShort): self
    {
        $this->clientRaisonSocialShort = $clientRaisonSocialShort;
        return $this;
    }

    public function getClientAdresse(): ?string
    {
        return $this->clientAdresse;
    }

    public function setClientAdresse(?string $clientAdresse): self
    {
        $this->clientAdresse = $clientAdresse;
        return $this;
    }

    public function getClientTelephone1(): ?string
    {
        return $this->clientTelephone1;
    }

    public function setClientTelephone1(?string $clientTelephone1): self
    {
        $this->clientTelephone1 = $clientTelephone1;
        return $this;
    }

    public function getClientTelephone2(): ?string
    {
        return $this->clientTelephone2;
    }

    public function setClientTelephone2(?string $clientTelephone2): self
    {
        $this->clientTelephone2 = $clientTelephone2;
        return $this;
    }

    public function getClientTelephone3(): ?string
    {
        return $this->clientTelephone3;
    }

    public function setClientTelephone3(?string $clientTelephone3): self
    {
        $this->clientTelephone3 = $clientTelephone3;
        return $this;
    }

    public function getClientEmail(): ?string
    {
        return $this->clientEmail;
    }

    public function setClientEmail(?string $clientEmail): self
    {
        $this->clientEmail = $clientEmail;
        return $this;
    }

    public function getClientPersonneContact1(): ?string
    {
        return $this->clientPersonneContact1;
    }

    public function setClientPersonneContact1(?string $clientPersonneContact1): self
    {
        $this->clientPersonneContact1 = $clientPersonneContact1;
        return $this;
    }

    public function getClientPersonneContact2(): ?string
    {
        return $this->clientPersonneContact2;
    }

    public function setClientPersonneContact2(?string $clientPersonneContact2): self
    {
        $this->clientPersonneContact2 = $clientPersonneContact2;
        return $this;
    }

    public function getClientPersonneContact3(): ?string
    {
        return $this->clientPersonneContact3;
    }

    public function setClientPersonneContact3(?string $clientPersonneContact3): self
    {
        $this->clientPersonneContact3 = $clientPersonneContact3;
        return $this;
    }

    /**
     * @return Collection|Reference[]
     */
    public function getReferences(): Collection
    {
        return $this->references;
    }

    public function addReference(Reference $reference): self
    {
        if (!$this->references->contains($reference)) {
            $this->references[] = $reference;
            $reference->setClient($this);
        }
        return $this;
    }

    public function removeReference(Reference $reference): self
    {
        if ($this->references->removeElement($reference)) {
            if ($reference->getClient() === $this) {
                $reference->setClient(null);
            }
        }
        return $this;
    }


    /**
     * @return Collection|SecteurActivite[]
     */
    public function getSecteurActivites(): Collection
    {
        return $this->secteurActivites;
    }

    public function addSecteurActivite(SecteurActivite $secteurActivite): self
    {
        if (!$this->secteurActivites->contains($secteurActivite)) {
            $this->secteurActivites[] = $secteurActivite;
        }

        return $this;
    }

    public function removeSecteurActivite(SecteurActivite $secteurActivite): self
    {
        $this->secteurActivites->removeElement($secteurActivite);

        return $this;
    }


    /**
     * @return Collection|Pays[]
     */

public function getPays(): Collection
{
    return $this->pays;
}

public function addPays(Pays $pays): self
{
    if (!$this->pays->contains($pays)) {
        $this->pays->add($pays);
    }
    return $this;
}

public function removePays(Pays $pays): self
{
    $this->pays->removeElement($pays);
    return $this;
}
/**
 * @return Collection|Projet[]
 */
public function getProjets(): Collection
{
    return $this->projets;
}

public function addProjet(Projet $projet): self
{
    if (!$this->projets->contains($projet)) {
        $this->projets[] = $projet;
        $projet->setClient($this);
    }
    return $this;
}

public function removeProjet(Projet $projet): self
{
    if ($this->projets->removeElement($projet)) {
        if ($projet->getClient() === $this) {
            $projet->setClient(null);
        }
    }
    return $this;
}

}
