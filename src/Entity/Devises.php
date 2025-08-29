<?php

namespace App\Entity;

use App\Repository\DevisesRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: DevisesRepository::class)]
#[ORM\Table(name: "devises")]
class Devises
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "devisesId")]
    private $devisesId;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "devisesLibelle")]
    private $devisesLibelle;

    #[ORM\Column(type: "string", length: 10, nullable: true, name: "devisesAcronyme")]
    private $devisesAcronyme;

    #[ORM\OneToMany(targetEntity: Reference::class, mappedBy: "devises")]
    private $references;

       #[ORM\OneToMany(targetEntity: AppelOffre::class, mappedBy: 'devises')]
    private Collection $appelOffre; 


    public function __construct()
    {
        $this->references = new ArrayCollection();
    }

    public function getDevisesId(): ?int
    {
        return $this->devisesId;
    }

    public function getDevisesLibelle(): ?string
    {
        return $this->devisesLibelle;
    }

    public function setDevisesLibelle(?string $devisesLibelle): self
    {
        $this->devisesLibelle = $devisesLibelle;

        return $this;
    }

    public function getDevisesAcronyme(): ?string
    {
        return $this->devisesAcronyme;
    }


    
    public function setDevisesAcronyme(?string $devisesAcronyme): self
    {
        $this->devisesAcronyme = $devisesAcronyme;

        return $this;
    }
}
