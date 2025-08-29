<?php

namespace App\Entity;

use App\Repository\NatureClientRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NatureClientRepository::class)]
#[ORM\Table(name: 'natureclient')]
class NatureClient
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'natureClientId', type: 'integer')]
    private ?int $id;

    #[ORM\Column(name: 'natureClientLibelle', length: 254, unique: true)]
    #[Assert\NotBlank]
    private ?string  $natureClient = null;

    #[ORM\Column(name: 'natureClientDescription', length: 254, nullable: true)]
    #[Assert\NotBlank]
    private ?string  $natureClientDescription = null;

    #[ORM\OneToMany(targetEntity: Client::class, mappedBy: "natureClient")]
    private $clients;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getNatureClient(): ?string
    {
        return $this->natureClient;
    }

    public function setNatureClient(string $natureClient): self
    {
        $this->natureClient = $natureClient;

        return $this;
    }

    public function getNatureClientDescription(): ?string
    {
        return $this->natureClientDescription;
    }

    public function setNatureClientDescription(?string $natureClientDescription): static
    {
        $this->natureClientDescription = $natureClientDescription;

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
            $client->setNatureClient($this);
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getNatureClient() === $this) {
                $client->setNatureClient(null);
            }
        }

        return $this;
    }
}
