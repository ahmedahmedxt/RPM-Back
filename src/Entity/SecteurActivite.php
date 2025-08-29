<?php

namespace App\Entity;

use App\Repository\SecteurActiviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecteurActiviteRepository::class)]
#[ORM\Table(name: "secteuractivite")]
class SecteurActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "secteurActiviteId")]
    private ?int $id = null;

    #[ORM\Column(name: "secteurActiviteLibelle", length: 254, nullable: true)]
    private ?string $secteurActiviteLibelle = null;

    #[ORM\Column(name: "secteurActiviteDescription", length: 254, nullable: true)]
    private ?string $secteurActiviteDescription = null;

    #[ORM\ManyToMany(targetEntity: Client::class, mappedBy: 'secteurActivites')]
    private $clients;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSecteurActiviteLibelle(): ?string
    {
        return $this->secteurActiviteLibelle;
    }

    public function setSecteurActiviteLibelle(?string $secteurActiviteLibelle): static
    {
        $this->secteurActiviteLibelle = $secteurActiviteLibelle;

        return $this;
    }

    public function getSecteurActiviteDescription(): ?string
    {
        return $this->secteurActiviteDescription;
    }

    public function setSecteurActiviteDescription(?string $secteurActiviteDescription): static
    {
        $this->secteurActiviteDescription = $secteurActiviteDescription;

        return $this;
    }

    /**
     * @return Collection|Client[]
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->addSecteurActivite($this);
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->removeElement($client)) {
            $client->removeSecteurActivite($this);
        }

        return $this;
    }
}
