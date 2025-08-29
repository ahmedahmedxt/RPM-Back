<?php

namespace App\Entity;

use App\Repository\EnvironnementDeveloppementRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: EnvironnementDeveloppementRepository::class)]
#[ORM\Table(name: "environnementdeveloppement")]
class EnvironnementDeveloppement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "environnementDeveloppementId")]
    private ?int $environnementDeveloppementId= null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "environnementDeveloppementLibelle")]
    private $environnementDeveloppementLibelle;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "environnementDeveloppementDescription")]
    private $environnementDeveloppementDescription;

    #[ORM\ManyToMany(targetEntity: Reference::class, mappedBy: 'environnementdeveloppements')]
    private Collection $references;

    public function __construct()
    {
        $this->references = new ArrayCollection();
    }
    public function getEnvironnementDeveloppementId(): ?int
    {
        return $this->environnementDeveloppementId;
    }

    public function getEnvironnementDeveloppementLibelle(): ?string
    {
        return $this->environnementDeveloppementLibelle;
    }

    public function getEnvironnementDeveloppementDescription(): ?string
    {
        return $this->environnementDeveloppementDescription;
    }

    // Setters
    public function setEnvironnementDeveloppementId(?int $environnementDeveloppementId): void
    {
        $this->environnementDeveloppementId = $environnementDeveloppementId;
    }

    public function setEnvironnementDeveloppementLibelle(?string $environnementDeveloppementLibelle): void
    {
        $this->environnementDeveloppementLibelle = $environnementDeveloppementLibelle;
    }

    public function setEnvironnementDeveloppementDescription(?string $environnementDeveloppementDescription): void
    {
        $this->environnementDeveloppementDescription = $environnementDeveloppementDescription;
    }

    public function getReferences(): Collection
    {
        return $this->references;
    }

    public function addReference(Reference $reference): self
    {
        if (!$this->references->contains($reference)) {
            $this->references[] = $reference;
            $reference->addEnvironnementdeveloppement($this);
        }

        return $this;
    }

    public function removeReference(Reference $reference): self
    {
        if ($this->references->removeElement($reference)) {
            $reference->removeEnvironnementdeveloppement($this);
        }

        return $this;
    }
}
